@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center py-16">
    <div class="bg-white rounded-2xl p-10 shadow-sm border border-gray-100 text-center max-w-md">
        <i class="fas fa-clock text-5xl text-amber-500 mb-4"></i>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Confirming Your Payment</h1>
        <p class="text-gray-600 mb-6">
            We're verifying your payment with PayMongo. This usually takes a few seconds.
            Your order status will update automatically.
        </p>
        <a href="{{ route('orders.show', $order->order_id) }}"
           class="inline-block bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-8 rounded-lg">
            View Order Status
        </a>
    </div>
</div>
@endsection