@extends('layouts.admin')

@section('title', 'Edit User')
@section('page-title', 'Edit User')
@section('page-subtitle', "Managing " . $user->first_name . " " . $user->last_name)

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

        .role-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        .role-badge-admin {
            background: #fecaca;
            color: #991b1b;
        }
        .role-badge-user {
            background: #bfdbfe;
            color: #1e40af;
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
            <div class="flex items-center justify-between mb-6 pb-6 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-1 h-6 bg-gradient-to-b from-red-600 to-black rounded-full"></div>
                    <div>
                        <h2 class="text-2xl font-bold gradient-title">Edit User</h2>
                        <p class="text-gray-500 text-sm">{{ $user->email }}</p>
                    </div>
                </div>
                @if($user->role === 'admin')
                    <span class="role-badge role-badge-admin">
                        <i class="fas fa-crown mr-1"></i> Admin
                    </span>
                @else
                    <span class="role-badge role-badge-user">
                        <i class="fas fa-user mr-1"></i> User
                    </span>
                @endif
            </div>

            <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

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
                            value="{{ old('first_name', $user->first_name) }}"
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
                            value="{{ old('last_name', $user->last_name) }}"
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
                            value="{{ old('middle_name', $user->middle_name) }}"
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
                            value="{{ old('suffix', $user->suffix) }}"
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
                        value="{{ old('email', $user->email) }}"
                        required
                        class="input-compact"
                    >
                    @error('email')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Role -->
                <div>
                    <label for="role" class="block text-xs font-semibold text-gray-700 mb-2">
                        User Role <span class="text-red-500">*</span>
                    </label>
                    <select name="role" id="role" required class="input-compact">
                        <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>
                            👤 Regular User - Can browse and purchase products
                        </option>
                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>
                            👑 Admin - Full access to manage the store
                        </option>
                    </select>
                    @error('role')
                        <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Form Actions -->
                <div class="flex gap-4 pt-6 border-t border-gray-100">
                    <button type="submit" class="btn-create-3d px-6 py-2 flex-1 text-center">
                        <i class="fas fa-save mr-2"></i> Save Changes
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn-secondary px-6 py-2 flex-1 text-center">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </a>
                </div>
            </form>
        </div>

        <!-- User Info Card -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-gray-50/80 border border-gray-200 rounded-xl p-6">
                <h3 class="font-semibold text-gray-900 mb-4">Account Information</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Joined:</span>
                        <span class="font-medium">{{ $user->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Last Updated:</span>
                        <span class="font-medium">{{ $user->updated_at->format('M d, Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Email Status:</span>
                        @if($user->email_verified_at)
                            <span class="text-green-600 font-medium">✓ Verified</span>
                        @else
                            <span class="text-yellow-600 font-medium">⚠ Unverified</span>
                        @endif
                    </div>
                </div>
            </div>

            @if($user->id !== auth()->id())
                <div class="bg-red-50/80 border border-red-200 rounded-xl p-6">
                    <h3 class="font-semibold text-red-900 mb-4">Danger Zone</h3>
                    <p class="text-sm text-red-800 mb-4">
                        Deleting this user will permanently remove their account and all associated data.
                    </p>
                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition">
                            <i class="fas fa-trash mr-2"></i> Delete User
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection
