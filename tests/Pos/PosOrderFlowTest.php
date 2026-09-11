<?php

namespace Tests\Pos;

use App\Enums\OrderStatus;
use App\Enums\SaleType;
use App\Exceptions\InvalidOrderTransitionException;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShoeType;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class PosOrderFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertEmpty(config('database.connections.sqlite.url'));

        foreach (glob(database_path('migrations/*.php')) as $file) {
            (require $file)->up();
        }

        Http::fake();
        $this->withoutVite();
    }

    private function fixture(): array
    {
        $cashier = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $category = Category::create(['category_name' => 'POS category']);
        $brand = Brand::create(['brand_name' => 'POS brand']);
        $type = ShoeType::create(['shoe_type_name' => 'POS type', 'display_order' => 0]);
        $product = Product::create([
            'product_name' => 'POS shoe',
            'category_id' => $category->getKey(),
            'brand_id' => $brand->getKey(),
            'shoe_type_id' => $type->getKey(),
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->getKey(),
            'size' => '8',
            'color' => 'Red',
        ]);

        // Insert the newest batch first to prove delivery date, not ID, determines FIFO.
        $newest = Stock::create([
            'product_variant_id' => $variant->getKey(),
            'price' => 100,
            'received_quantity' => 5,
            'remaining_quantity' => 5,
            'deliver_date' => '2026-02-01',
        ]);
        $oldest = Stock::create([
            'product_variant_id' => $variant->getKey(),
            'price' => 100,
            'received_quantity' => 2,
            'remaining_quantity' => 2,
            'deliver_date' => '2026-01-01',
        ]);

        return [$cashier, $variant, $oldest, $newest];
    }

    public function test_pos_completes_paid_sale_with_server_price_fifo_history_and_revenue(): void
    {
        [$cashier, $variant, $oldest, $newest] = $this->fixture();
        $requestId = (string) Str::uuid();

        $response = $this->actingAs($cashier)->postJson('/admin/pos/sale', [
            'request_id' => $requestId,
            'items' => [
                ['product_variant_id' => $variant->getKey(), 'quantity' => 4],
            ],
        ])->assertOk()->assertJsonPath('success', true);

        $order = Order::findOrFail($response->json('order_id'));
        $this->assertSame(OrderStatus::Completed, $order->status);
        $this->assertSame(SaleType::Pos, $order->sale_type);
        $this->assertSame($requestId, $order->pos_request_id);
        $this->assertSame('cash_pos', $order->payment_method);
        $this->assertSame('completed', $order->payment->status);
        $this->assertSame('cash_pos', $order->payment->method);
        $this->assertNotNull($order->payment->payment_date);
        $this->assertEquals(100, $order->items->first()->price);
        $this->assertEquals(400, $order->total_amount);
        $this->assertEquals(0, $oldest->fresh()->remaining_quantity);
        $this->assertEquals(3, $newest->fresh()->remaining_quantity);

        $moves = StockMovement::where('type', 'out')->orderBy('stock_movement_id')->get();
        $this->assertCount(2, $moves);
        $this->assertEquals([$oldest->getKey(), $newest->getKey()], $moves->pluck('stock_id')->all());
        $this->assertEquals([2, 2], $moves->pluck('quantity')->all());
        $this->assertEquals(400, Order::where('status', 'completed')->sum('total_amount'));

        $history = $this->get('/admin/orders?sale_type=pos')->assertOk();
        $this->assertEquals($order->getKey(), $history->viewData('orders')->first()->getKey());
        $this->assertEquals(400, $history->viewData('stats')['total_revenue']);
        $this->get($response->json('receipt_url'))->assertOk()->assertSee('POS shoe');
        $this->assertSame([], $order->status->allowedTransitions());
        $this->put('/admin/orders/'.$order->getKey().'/status', ['status' => 'shipped'])
            ->assertSessionHas('error');
        $this->assertSame(OrderStatus::Completed, $order->fresh()->status);
    }

    public function test_duplicate_request_returns_same_sale_without_double_deduction(): void
    {
        [$cashier, $variant, $oldest, $newest] = $this->fixture();
        $payload = [
            'request_id' => (string) Str::uuid(),
            'items' => [['product_variant_id' => $variant->getKey(), 'quantity' => 1]],
        ];

        $first = $this->actingAs($cashier)->postJson('/admin/pos/sale', $payload)->assertOk();
        $second = $this->postJson('/admin/pos/sale', $payload)->assertOk();

        $this->postJson('/admin/pos/sale', [
            'request_id' => $payload['request_id'],
            'items' => [['product_variant_id' => $variant->getKey(), 'quantity' => 2]],
        ])->assertConflict();

        $this->assertSame($first->json('order_id'), $second->json('order_id'));
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('stock_movements', 1);
        $this->assertEquals(1, $oldest->fresh()->remaining_quantity);
        $this->assertEquals(5, $newest->fresh()->remaining_quantity);
    }

    public function test_client_price_is_rejected_and_failed_sale_writes_nothing(): void
    {
        [$cashier, $variant, $oldest, $newest] = $this->fixture();

        $this->actingAs($cashier)->postJson('/admin/pos/sale', [
            'request_id' => (string) Str::uuid(),
            'items' => [[
                'product_variant_id' => $variant->getKey(),
                'quantity' => 1,
                'price' => 0.01,
            ]],
        ])->assertUnprocessable()->assertJsonValidationErrors('items.0.price');

        $this->postJson('/admin/pos/sale', [
            'request_id' => (string) Str::uuid(),
            'items' => [['product_variant_id' => $variant->getKey(), 'quantity' => 8]],
        ])->assertUnprocessable()->assertJsonPath('success', false);

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseCount('stock_movements', 0);
        $this->assertEquals(2, $oldest->fresh()->remaining_quantity);
        $this->assertEquals(5, $newest->fresh()->remaining_quantity);
    }

    public function test_customer_and_super_admin_cannot_create_pos_sales(): void
    {
        [, $variant] = $this->fixture();
        $payload = [
            'request_id' => (string) Str::uuid(),
            'items' => [['product_variant_id' => $variant->getKey(), 'quantity' => 1]],
        ];

        $customer = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $this->actingAs($customer)->postJson('/admin/pos/sale', $payload)->assertForbidden();

        $superAdmin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
        $this->actingAs($superAdmin)->postJson('/admin/pos/sale', $payload)->assertForbidden();

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_online_paid_order_still_requires_shipping_before_completion(): void
    {
        [$cashier] = $this->fixture();
        $order = Order::create([
            'user_id' => $cashier->id,
            'sale_type' => SaleType::Online,
            'status' => OrderStatus::Paid,
            'total_amount' => 100,
            'full_name' => 'Customer',
            'phone_number' => '123',
            'street' => 'Street',
            'barangay' => 'Barangay',
            'city' => 'City',
            'province' => 'Province',
            'postal_code' => '1000',
        ]);
        Payment::create([
            'order_id' => $order->getKey(),
            'method' => 'gcash',
            'status' => 'completed',
            'payment_date' => now(),
        ]);

        $service = app(OrderService::class);
        try {
            $service->updateStatus($order, OrderStatus::Completed);
            $this->fail('Online paid orders must not skip shipping.');
        } catch (InvalidOrderTransitionException $e) {
            $this->assertSame(OrderStatus::Paid, $order->fresh()->status);
        }

        $this->assertSame(OrderStatus::Shipped, $service->updateStatus($order, OrderStatus::Shipped)->status);
        $this->assertSame(
            OrderStatus::Completed,
            $service->updateStatus($order->fresh(), OrderStatus::Completed)->status
        );
    }
}
