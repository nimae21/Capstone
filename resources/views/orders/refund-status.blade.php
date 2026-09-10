@if($order->payment?->refund_status)
    <div class="mt-3 rounded-lg border border-gray-200 p-3" role="status">
        <p class="font-semibold">{{ $order->payment->refund_label }}</p>
        @if($order->payment->refund_status === 'pending')
            <p class="text-sm">The refund has not been confirmed yet.</p>
        @elseif($order->payment->refund_status === 'refunded')
            <p class="text-sm">PayMongo confirmed the refund. Your bank or wallet may need time to post it.</p>
        @else
            <p class="text-sm">The refund could not be completed. Please contact support for review.</p>
        @endif
        @if($order->payment->refund_error)
            <p class="text-sm">{{ $order->payment->refund_error }}</p>
        @endif
        @if($order->status === \App\Enums\OrderStatus::Cancelled && $order->payment->can_retry_refund)
            <form method="POST" action="{{ $refundRetryUrl }}" class="mt-2">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="cancelled">
                <button type="submit" class="font-semibold text-red-600">Retry refund confirmation</button>
            </form>
        @endif
    </div>
@endif
@if($order->status === \App\Enums\OrderStatus::Cancelled && $order->payment?->status === 'pending')
    <p class="text-sm">Checkout closure needs confirmation.</p>
    <form method="POST" action="{{ $refundRetryUrl }}" class="mt-2">
        @csrf
        @method('PUT')
        <input type="hidden" name="status" value="cancelled">
        <button type="submit" class="font-semibold text-red-600">Retry cancellation</button>
    </form>
@endif