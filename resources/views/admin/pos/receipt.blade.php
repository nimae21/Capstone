<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt #{{ $order->order_id }}</title>
    <style>
        body { font-family: 'Courier New', monospace; max-width: 380px; margin: 2rem auto; color: #1e293b; }
        .center { text-align: center; }
        hr { border: none; border-top: 1px dashed #94a3b8; margin: 1rem 0; }
        table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        td { padding: 0.3rem 0; }
        .right { text-align: right; }
        .total-row td { font-weight: bold; font-size: 1rem; border-top: 1px solid #1e293b; padding-top: 0.5rem; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="center">
        <h2>ACHILLES</h2>
        <p>Official Receipt</p>
    </div>
    <hr>
    <p>Order #{{ $order->order_id }}<br>
       {{ $order->created_at->format('M d, Y H:i A') }}<br>
       Cashier: {{ $order->user->full_name ?? 'Admin' }}</p>
    <hr>
    <table>
        @foreach($order->items as $item)
            <tr>
                <td>{{ $item->variant->product->product_name }}<br>
                    <small>{{ $item->variant->size }}/{{ $item->variant->color }} × {{ $item->quantity }}</small>
                </td>
                <td class="right">₱{{ number_format($item->price * $item->quantity, 2) }}</td>
            </tr>
        @endforeach
        <tr class="total-row">
            <td>TOTAL</td>
            <td class="right">₱{{ number_format($order->total_amount, 2) }}</td>
        </tr>
    </table>
    <hr>
    <p class="center">Payment: Cash<br>Thank you for shopping with us!</p>

    <div class="no-print center" style="margin-top:2rem;">
        <button onclick="window.print()">Print Receipt</button>
    </div>
</body>
</html>