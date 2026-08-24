@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-100 px-4 py-10 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="mb-2 text-sm font-bold uppercase tracking-[0.2em] text-red-600">Account center</p>
                <h1 class="text-4xl font-black tracking-tight text-gray-950 sm:text-5xl">Your profile</h1>
                <p class="mt-2 max-w-xl text-gray-500">Keep your details current for smoother checkout and order updates.</p>
            </div>
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 self-start rounded-full border border-gray-200 bg-white px-5 py-3 text-sm font-bold text-gray-700 shadow-sm transition hover:-translate-x-1 hover:border-red-200 hover:text-red-600 sm:self-auto">
                <i class="fas fa-arrow-left"></i>
                Back to shop
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">
                <i class="fas fa-circle-check"></i>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <p class="font-bold">Please check the highlighted information.</p>
                <ul class="mt-1 list-inside list-disc">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[280px_1fr]">
            <aside class="h-fit rounded-2xl border border-gray-200 bg-gray-950 p-6 text-white shadow-xl">
                <div class="flex items-center gap-4 lg:block">
                    <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-red-500 to-red-800 text-3xl font-black shadow-lg">
                        {{ strtoupper(substr(auth()->user()->first_name, 0, 1) . substr(auth()->user()->last_name, 0, 1)) }}
                    </div>
                    <div class="mt-0 lg:mt-5">
                        <h2 class="text-xl font-black">{{ auth()->user()->full_name }}</h2>
                        <p class="mt-1 break-all text-sm text-gray-400">{{ auth()->user()->email }}</p>
                    </div>
                </div>

                <div class="mt-6 flex items-center gap-2 text-sm font-semibold {{ auth()->user()->email_verified_at ? 'text-emerald-300' : 'text-amber-300' }}">
                    <i class="fas {{ auth()->user()->email_verified_at ? 'fa-circle-check' : 'fa-circle-exclamation' }}"></i>
                    {{ auth()->user()->email_verified_at ? 'Verified account' : 'Email verification pending' }}
                </div>

                <div class="mt-6 border-t border-white/10 pt-5 text-sm text-gray-400">
                    <p class="uppercase tracking-widest">Member since</p>
                    <p class="mt-1 font-bold text-white">{{ auth()->user()->created_at->format('F Y') }}</p>
                </div>

                <div class="mt-6 grid gap-2">
                    <a href="{{ route('addresses.index') }}" class="flex items-center justify-between rounded-xl bg-white/10 px-4 py-3 text-sm font-bold transition hover:bg-red-600">
                        <span><i class="fas fa-location-dot mr-2"></i>Saved addresses</span>
                        <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                    <a href="{{ route('orders.index') }}" class="flex items-center justify-between rounded-xl bg-white/10 px-4 py-3 text-sm font-bold transition hover:bg-red-600">
                        <span><i class="fas fa-box mr-2"></i>Order history</span>
                        <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>
            </aside>

            <div class="grid gap-6 xl:grid-cols-2">
                <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                    <div class="mb-6 flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-red-600">Identity</p>
                            <h2 class="mt-1 text-2xl font-black text-gray-950">Personal information</h2>
                        </div>
                        <i class="fas fa-user-pen text-xl text-gray-300"></i>
                    </div>

                    <form method="POST" action="{{ route('profile.update') }}" class="space-y-5">
                        @csrf
                        @method('PUT')
                        <div class="grid gap-5 sm:grid-cols-2">
                            @foreach([
                                ['first_name', 'First name', auth()->user()->first_name],
                                ['middle_name', 'Middle name', auth()->user()->middle_name],
                                ['last_name', 'Last name', auth()->user()->last_name],
                                ['suffix', 'Suffix', auth()->user()->suffix],
                            ] as [$name, $label, $value])
                                <div>
                                    <label for="{{ $name }}" class="mb-2 block text-sm font-bold text-gray-700">{{ $label }}</label>
                                    <input id="{{ $name }}" name="{{ $name }}" value="{{ old($name, $value) }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 outline-none transition focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10" {{ in_array($name, ['first_name', 'last_name']) ? 'required' : '' }}>
                                </div>
                            @endforeach
                        </div>
                        <div>
                            <label for="email" class="mb-2 block text-sm font-bold text-gray-700">Email address</label>
                            <input id="email" type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 outline-none transition focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10">
                        </div>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gray-950 px-5 py-3 font-bold text-white transition hover:-translate-y-0.5 hover:bg-red-600">
                            <i class="fas fa-floppy-disk"></i>
                            Save changes
                        </button>
                    </form>
                </section>

                <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                    <div class="mb-6 flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-red-600">Security</p>
                            <h2 class="mt-1 text-2xl font-black text-gray-950">Change password</h2>
                        </div>
                        <i class="fas fa-shield-halved text-xl text-gray-300"></i>
                    </div>

                    <form method="POST" action="{{ route('profile.password') }}" class="space-y-5">
                        @csrf
                        @method('PUT')
                        @foreach([
                            ['current_password', 'Current password'],
                            ['password', 'New password'],
                            ['password_confirmation', 'Confirm new password'],
                        ] as [$name, $label])
                            <div>
                                <label for="{{ $name }}" class="mb-2 block text-sm font-bold text-gray-700">{{ $label }}</label>
                                <input id="{{ $name }}" type="password" name="{{ $name }}" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 outline-none transition focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10" required>
                            </div>
                        @endforeach
                        <p class="text-xs leading-relaxed text-gray-500">Use at least 8 characters with letters, numbers, and a symbol.</p>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-red-600 px-5 py-3 font-bold text-red-600 transition hover:bg-red-600 hover:text-white">
                            <i class="fas fa-key"></i>
                            Update password
                        </button>
                    </form>
                </section>
            </div>
        </div>
    </div>
</div>

@endsection