@extends('layouts.app')

@section('title', 'My Addresses')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">My Addresses</h1>
                    <p class="text-gray-600 mt-2">Manage your delivery addresses</p>
                </div>
                <a href="{{ route('addresses.create') }}" class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition">
                    <i class="fas fa-plus mr-2"></i> Add New Address
                </a>
            </div>
        </div>

        <!-- Success Alert -->
        @if(session('success'))
            <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-4 rounded-lg flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="fas fa-check-circle text-xl"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-4 rounded-lg flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-xl"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif

        <!-- Addresses Grid -->
        @if($addresses->count())
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($addresses as $address)
                    <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition border-2 {{ $address->is_default ? 'border-red-400' : 'border-gray-200' }} overflow-hidden">
                        <div class="p-6">
                            <!-- Header with Default Badge -->
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">{{ $address->full_name }}</h3>
                                    <p class="text-gray-600 text-sm">{{ $address->phone_number }}</p>
                                </div>
                                @if($address->is_default)
                                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-semibold">
                                        <i class="fas fa-star mr-1"></i> Default
                                    </span>
                                @endif
                            </div>

                            <!-- Address Details -->
                            <div class="mb-4 text-sm text-gray-700 space-y-1">
                                <p>{{ $address->street }}, {{ $address->barangay }}</p>
                                <p>{{ $address->city }}, {{ $address->province }} {{ $address->postal_code }}</p>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-3 pt-4 border-t border-gray-200">
                                <a href="{{ route('addresses.edit', $address->address_id) }}" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded transition text-center">
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </a>
                                @if(!$address->is_default)
                                    <form action="{{ route('addresses.set-default', $address->address_id) }}" method="POST" class="flex-1">
                                        @csrf
                                        <button type="submit" class="w-full bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded transition">
                                            <i class="fas fa-flag mr-1"></i> Set Default
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('addresses.destroy', $address->address_id) }}" method="POST" onsubmit="return confirm('Delete this address?');" class="flex-1">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded transition">
                                        <i class="fas fa-trash mr-1"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($addresses->hasPages())
                <div class="mt-8">
                    {{ $addresses->links() }}
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <i class="fas fa-map-marker-alt text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No Addresses Yet</h3>
                <p class="text-gray-600 mb-6">Add your first delivery address to get started</p>
                <a href="{{ route('addresses.create') }}" class="inline-block bg-red-500 hover:bg-red-600 text-white font-semibold py-3 px-6 rounded-lg transition">
                    <i class="fas fa-plus mr-2"></i> Add Address
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
