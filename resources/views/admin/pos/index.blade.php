@extends('layouts.admin')

@section('title', 'Point of Sale')
@section('page-title', 'Point of Sale')
@section('page-subtitle', 'Process in-store, walk-in customer sales.')

@section('styles')
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
    * { font-family: 'Inter', sans-serif; }
    body { background: linear-gradient(145deg, #f0f4f8 0%, #e9eef3 100%); }

    .gradient-title {
        font-weight: 900 !important;
        letter-spacing: -0.02em;
        background: linear-gradient(135deg, #000000 0%, #dc2626 50%, #000000 100%);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    .pos-layout {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 1.5rem;
        height: calc(100vh - 180px);
        min-height: 600px;
    }
    @media (max-width: 1024px) {
        .pos-layout { grid-template-columns: 1fr; height: auto; }
    }

    .product-panel, .cart-panel {
        background: white;
        border-radius: 1.25rem;
        border: 1px solid #eef2f6;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .search-bar {
        padding: 1.25rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .search-bar input {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 2.75rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        font-size: 0.95rem;
    }
    .search-bar input:focus {
        outline: none;
        border-color: #dc2626;
        box-shadow: 0 0 0 4px rgba(220,38,38,0.1);
    }
    .search-wrap { position: relative; }
    .search-wrap i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    .product-scroll {
        flex: 1;
        overflow-y: auto;
        padding: 1.25rem;
    }

    .pos-product-card {
        background: #fafcfd;
        border: 1px solid #eef2f6;
        border-radius: 1rem;
        padding: 1rem;
        margin-bottom: 1rem;
        transition: all 0.2s ease;
    }
    .pos-product-card:hover {
        border-color: #dc2626;
        box-shadow: 0 4px 12px -4px rgba(220,38,38,0.1);
    }
    .pos-product-name {
        font-weight: 700;
        color: #1e293b;
        font-size: 0.95rem;
        margin-bottom: 0.25rem;
    }
    .pos-product-meta {
        font-size: 0.75rem;
        color: #94a3b8;
        margin-bottom: 0.75rem;
    }

    .variant-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: white;
        border: 1.5px solid #e2e8f0;
        border-radius: 0.6rem;
        padding: 0.4rem 0.75rem;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s ease;
        margin: 0 0.4rem 0.4rem 0;
    }
    .variant-chip:hover {
        border-color: #dc2626;
        background: #fef2f2;
        color: #dc2626;
    }
    .variant-chip.out-of-stock {
        opacity: 0.4;
        cursor: not-allowed;
        text-decoration: line-through;
    }
    .variant-chip .stock-dot {
        width: 6px; height: 6px; border-radius: 50%;
        background: #10b981;
    }
    .variant-chip.low-stock .stock-dot { background: #f59e0b; }

    .cart-header {
        padding: 1.25rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .cart-items {
        flex: 1;
        overflow-y: auto;
        padding: 1rem 1.25rem;
    }
    .cart-item-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f8fafc;
    }
    .cart-item-name {
        font-weight: 600;
        font-size: 0.85rem;
        color: #1e293b;
    }
    .cart-item-meta {
        font-size: 0.72rem;
        color: #94a3b8;
    }
    .qty-control {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: #f8fafc;
        border-radius: 0.5rem;
        padding: 0.2rem;
    }
    .qty-btn {
        width: 22px; height: 22px;
        border-radius: 0.35rem;
        background: white;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.75rem;
        font-weight: 700;
        color: #475569;
    }
    .qty-btn:hover { border-color: #dc2626; color: #dc2626; }

    .cart-footer {
        padding: 1.25rem;
        border-top: 1px solid #f1f5f9;
        background: #fafcfd;
    }
    .total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
    .total-row .amount {
        font-size: 1.75rem;
        font-weight: 900;
        color: #dc2626;
    }

    .btn-checkout {
        width: 100%;
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
        font-weight: 700;
        padding: 0.9rem;
        border-radius: 0.85rem;
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 0 #991b1b;
        transition: all 0.1s ease;
        font-size: 1rem;
    }
    .btn-checkout:active { transform: translateY(2px); box-shadow: 0 2px 0 #991b1b; }
    .btn-checkout:disabled {
        background: #cbd5e1;
        box-shadow: none;
        cursor: not-allowed;
    }

    .empty-cart {
        text-align: center;
        color: #94a3b8;
        padding: 3rem 1rem;
    }
</style>
@endsection

@section('content')
<div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6 relative z-10">

    <div class="mb-5">
        <h1 class="gradient-title text-3xl">Point of Sale</h1>
        <p class="text-gray-500 text-sm mt-1">Search a product, tap a size/color to add it, then complete the sale.</p>
    </div>

    <div class="pos-layout">

        <!-- LEFT: Product Search + Grid -->
        <div class="product-panel">
            <div class="search-bar">
                <div class="search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" id="posSearch" placeholder="Search products by name...">
                </div>
            </div>

            <div class="product-scroll" id="productScroll">
                @forelse($products as $product)
                    <div class="pos-product-card" data-name="{{ strtolower($product->product_name) }}">
                        <div class="pos-product-name">{{ $product->product_name }}</div>
                        <div class="pos-product-meta">
                            {{ $product->brand->brand_name ?? 'No Brand' }} · {{ $product->category->category_name ?? '' }}
                        </div>
                        <div>
                            @forelse($product->variants as $variant)
                                @php
                                    $stock = $variant->available_stock;
                                    $chipClass = $stock <= 0 ? 'out-of-stock' : ($stock <= 5 ? 'low-stock' : '');
                                @endphp
                                <span
                                    class="variant-chip {{ $chipClass }}"
                                    @if($stock > 0)
                                        onclick="addToCart({
                                            variantId: {{ $variant->product_variant_id }},
                                            name: '{{ addslashes($product->product_name) }}',
                                            size: '{{ $variant->size }}',
                                            color: '{{ $variant->color }}',
                                            price: {{ $variant->current_price }},
                                            maxStock: {{ $stock }}
                                        })"
                                    @endif
                                >
                                    <span class="stock-dot"></span>
                                    {{ $variant->size }} / {{ $variant->color }}
                                    <span class="text-gray-400">·</span>
                                    ₱{{ number_format($variant->current_price, 0) }}
                                </span>
                            @empty
                                <span class="text-xs text-gray-400">No variants available</span>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <div class="empty-cart">
                        <i class="fas fa-box-open text-4xl mb-3 block opacity-30"></i>
                        No products found.
                    </div>
                @endforelse
            </div>

            @if(method_exists($products, 'links'))
                <div class="p-4 border-t border-gray-100">
                    {{ $products->links() }}
                </div>
            @endif
        </div>

        <!-- RIGHT: Cart -->
        <div class="cart-panel">
            <div class="cart-header">
                <h3 class="font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-shopping-basket text-red-500"></i> Current Sale
                </h3>
                <button onclick="clearCart()" class="text-xs text-gray-400 hover:text-red-600 font-semibold">
                    Clear
                </button>
            </div>
<div class="cart-items" id="cartItems"></div>
           

            <div class="cart-footer">
                <div class="total-row">
                    <span class="font-semibold text-gray-600">Total</span>
                    <span class="amount" id="cartTotal">₱0.00</span>
                </div>
                <button class="btn-checkout" id="checkoutBtn" onclick="completeSale()" disabled>
                    <i class="fas fa-check-circle mr-2"></i> Complete Sale (Cash)
                </button>
            </div>
        </div>

    </div>
</div>

<!-- Success Modal -->
<div id="saleSuccessModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white w-full max-w-sm p-6 rounded-2xl text-center shadow-2xl">
        <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-check text-green-600 text-2xl"></i>
        </div>
        <h2 class="text-xl font-bold text-gray-800">Sale Complete</h2>
        <p class="text-gray-600 mt-2">Order #<span id="successOrderId"></span> has been recorded.</p>
        <div class="flex gap-3 mt-6">
            <a id="viewReceiptBtn" href="#" target="_blank"
               class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2.5 rounded-lg">
                View Receipt
            </a>
            <button onclick="startNewSale()" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 rounded-lg">
                New Sale
            </button>
        </div>
    </div>
</div>

<!-- Error Toast -->
<div id="errorToast" class="fixed top-6 right-6 bg-red-50 border border-red-200 text-red-700 px-5 py-3 rounded-xl shadow-lg hidden z-50 max-w-sm"></div>

<script>
let cart = {}; // keyed by variantId

function addToCart(item) {
    if (cart[item.variantId]) {
        if (cart[item.variantId].quantity >= item.maxStock) {
            showError(`Only ${item.maxStock} in stock for ${item.name} (${item.size}/${item.color}).`);
            return;
        }
        cart[item.variantId].quantity++;
    } else {
        cart[item.variantId] = { ...item, quantity: 1 };
    }
    renderCart();
}

function increaseQty(variantId) {
    const item = cart[variantId];
    if (item.quantity >= item.maxStock) {
        showError(`Only ${item.maxStock} in stock for ${item.name}.`);
        return;
    }
    item.quantity++;
    renderCart();
}

function decreaseQty(variantId) {
    const item = cart[variantId];
    item.quantity--;
    if (item.quantity <= 0) {
        delete cart[variantId];
    }
    renderCart();
}

function clearCart() {
    cart = {};
    renderCart();
}

function renderCart() {
    const container = document.getElementById('cartItems');
    const emptyMsg = document.getElementById('emptyCartMsg');
    const entries = Object.values(cart);

    if (entries.length === 0) {
    container.innerHTML = `
        <div class="empty-cart">
            <i class="fas fa-cash-register text-4xl mb-3 block opacity-30"></i>
            No items yet. Tap a product to add it.
        </div>
    `;
    document.getElementById('checkoutBtn').disabled = true;
    document.getElementById('cartTotal').textContent = '₱0.00';
    return;
}

    let html = '';
    let total = 0;

    entries.forEach(item => {
        const subtotal = item.price * item.quantity;
        total += subtotal;

        html += `
            <div class="cart-item-row">
                <div class="flex-1">
                    <div class="cart-item-name">${item.name}</div>
                    <div class="cart-item-meta">${item.size} / ${item.color} · ₱${item.price.toLocaleString()} each</div>
                </div>
                <div class="qty-control">
                    <button class="qty-btn" onclick="decreaseQty(${item.variantId})">−</button>
                    <span class="text-sm font-bold w-5 text-center">${item.quantity}</span>
                    <button class="qty-btn" onclick="increaseQty(${item.variantId})">+</button>
                </div>
                <div class="text-sm font-bold text-gray-800 w-16 text-right">₱${subtotal.toLocaleString()}</div>
            </div>
        `;
    });

    container.innerHTML = html;
    document.getElementById('cartTotal').textContent = '₱' + total.toLocaleString(undefined, {minimumFractionDigits: 2});
    document.getElementById('checkoutBtn').disabled = false;
}

function showError(message) {
    const toast = document.getElementById('errorToast');
    toast.textContent = message;
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 4000);
}

async function completeSale() {
    const items = Object.values(cart).map(item => ({
        product_variant_id: item.variantId,
        quantity: item.quantity,
        price: item.price
    }));

    const btn = document.getElementById('checkoutBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';

    try {
        const response = await fetch('{{ route("admin.pos.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ items })
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            showError(data.error || 'Something went wrong. Please try again.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle mr-2"></i> Complete Sale (Cash)';
            return;
        }

        document.getElementById('successOrderId').textContent = data.order_id;
        document.getElementById('viewReceiptBtn').href = data.receipt_url;
        document.getElementById('saleSuccessModal').classList.remove('hidden');

    } catch (e) {
        showError('Network error. Please check your connection and try again.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle mr-2"></i> Complete Sale (Cash)';
    }
}

function startNewSale() {
    clearCart();
    document.getElementById('saleSuccessModal').classList.add('hidden');
    document.getElementById('checkoutBtn').innerHTML = '<i class="fas fa-check-circle mr-2"></i> Complete Sale (Cash)';
    location.reload(); // refresh stock counts for accuracy
}

// Client-side search filter (matches product name, no server round-trip needed for typing)
document.getElementById('posSearch').addEventListener('input', function() {
    const term = this.value.toLowerCase();
    document.querySelectorAll('.pos-product-card').forEach(card => {
        card.style.display = card.dataset.name.includes(term) ? '' : 'none';
    });
});
renderCart(); // show empty state on initial page load
</script>
@endsection