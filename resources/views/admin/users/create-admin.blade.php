@extends('layouts.admin')

@section('title', 'Create Admin')
@section('page-title', 'Create New Admin')
@section('page-subtitle', 'Add a new admin account to manage the store.')

@section('styles')
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }

        body {
            background: linear-gradient(145deg, #f0f4f8 0%, #e9eef3 100%);
        }

        .card-cinematic {
            background: rgba(255,255,255,0.96);
            backdrop-filter: blur(2px);
            border: 1px solid rgba(255,255,255,0.5);
            box-shadow: 0 8px 20px -6px rgba(0,0,0,0.05);
        }

        .gradient-title {
            font-weight: 800 !important;
            background: linear-gradient(135deg, #000000, #dc2626);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .input-compact {
            transition: all 0.2s;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 0.75rem;
            font-size: 0.875rem;
            width: 100%;
        }
        .input-compact:focus {
            transform: translateY(-1px);
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220,38,38,0.1);
            outline: none;
        }

        .btn-create-3d {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            border: none;
            box-shadow: 0 8px 0 #991b1b, 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
            transition: all 0.08s linear;
            font-weight: 600;
            border-radius: 0.75rem;
        }
        .btn-create-3d:active {
            transform: translateY(6px);
            box-shadow: 0 2px 0 #991b1b;
        }
        .btn-create-3d:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #475569;
            border: 1px solid #cbd5e1;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-secondary:hover {
            background: #cbd5e1;
        }

        .error-text {
            color: #dc2626;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
    </style>
@endsection

@section('content')
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        
        <!-- Error Alert -->
        @if(session('error'))
            <div class="bg-red-50/90 backdrop-blur-sm border-l-4 border-red-400 text-red-700 p-4 rounded-xl shadow-md mb-6">
                <div class="font-medium">{{ session('error') }}</div>
            </div>
        @endif

        <!-- Form Card -->
        <div class="card-cinematic rounded-2xl p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-1 h-6 bg-gradient-to-b from-red-600 to-black rounded-full"></div>
                <h2 class="text-2xl font-bold gradient-title">Create New Admin Account</h2>
            </div>
            
            <p class="text-gray-600 text-sm mb-8">
                Fill in the form below to create a new admin account. Admin accounts have full access to manage the store.
            </p>

            <form action="{{ route('admin.users.store-admin') }}" method="POST" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- First Name -->
                    <div>
                        <label for="first_name" class="block text-xs font-semibold text-gray-700 mb-2">
                            First Name <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="first_name" 
                            id="first_name" 
                            placeholder="e.g., John"
                            value="{{ old('first_name') }}"
                            required
                            class="input-compact"
                        >
                        @error('first_name')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Last Name -->
                    <div>
                        <label for="last_name" class="block text-xs font-semibold text-gray-700 mb-2">
                            Last Name <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="last_name" 
                            id="last_name" 
                            placeholder="e.g., Doe"
                            value="{{ old('last_name') }}"
                            required
                            class="input-compact"
                        >
                        @error('last_name')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Middle Name -->
                    <div>
                        <label for="middle_name" class="block text-xs font-semibold text-gray-700 mb-2">
                            Middle Name
                        </label>
                        <input 
                            type="text" 
                            name="middle_name" 
                            id="middle_name" 
                            placeholder="e.g., James"
                            value="{{ old('middle_name') }}"
                            class="input-compact"
                        >
                        @error('middle_name')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Suffix -->
                    <div>
                        <label for="suffix" class="block text-xs font-semibold text-gray-700 mb-2">
                            Suffix
                        </label>
                        <input 
                            type="text" 
                            name="suffix" 
                            id="suffix" 
                            placeholder="e.g., Jr., Sr."
                            value="{{ old('suffix') }}"
                            class="input-compact"
                        >
                        @error('suffix')
                            <span class="error-text">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-semibold text-gray-700 mb-2">
                        Email Address <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        placeholder="admin@achilles.com"
                        value="{{ old('email') }}"
                        required
                        class="input-compact"
                    >
                    @error('email')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-xs font-semibold text-gray-700 mb-2">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        placeholder="Min 8 chars: letters, numbers, symbols"
                        required
                        class="input-compact"
                    >
                    <p class="text-gray-500 text-xs mt-2">
                        Password must contain at least 8 characters, including letters, numbers, and symbols.
                    </p>
                    @error('password')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Form Actions -->
                <div class="flex gap-4 pt-6 border-t border-gray-100">
                    <button type="submit" class="btn-create-3d px-6 py-2 flex-1 text-center">
                        <i class="fas fa-user-plus mr-2"></i> Create Admin
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn-secondary px-6 py-2 flex-1 text-center">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </a>
                </div>
            </form>
        </div>

        <!-- Info Box -->
        <div class="mt-8 bg-blue-50/80 border border-blue-200 rounded-xl p-6">
            <h3 class="font-semibold text-blue-900 mb-3 flex items-center gap-2">
                <i class="fas fa-info-circle"></i> Important Information
            </h3>
            <ul class="text-sm text-blue-800 space-y-2">
                <li>✓ Admin accounts will be automatically email verified</li>
                <li>✓ Email must be unique and not already in use</li>
                <li>✓ Password must be strong (8+ characters with letters, numbers, symbols)</li>
                <li>✓ Only existing admins can create new admin accounts</li>
            </ul>
        </div>
    </div>
@endsection
