@extends('layouts.admin')

@section('title', 'Users Management')
@section('page-title', 'User Management')
@section('page-subtitle', 'Manage all users and admin accounts.')

@section('styles')
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }

        body {
            background: linear-gradient(145deg, #f0f4f8 0%, #e9eef3 100%);
            position: relative;
        }

        .card-cinematic {
            background: rgba(255,255,255,0.96);
            backdrop-filter: blur(2px);
            border: 1px solid rgba(255,255,255,0.5);
            transition: all 0.4s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            transform: translateZ(0);
            box-shadow: 0 8px 20px -6px rgba(0,0,0,0.05), 0 1px 1px rgba(0,0,0,0.02);
        }
        .card-cinematic:hover {
            transform: translateY(-6px) translateZ(12px);
            box-shadow: 0 25px 35px -12px rgba(0, 0, 0, 0.15);
            border-color: rgba(220,38,38,0.2);
        }

        .gradient-title {
            font-weight: 800 !important;
            background: linear-gradient(135deg, #000000, #dc2626);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            letter-spacing: -0.02em;
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

        .btn-sm-3d {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            font-weight: 500;
            font-size: 0.75rem;
            padding: 0.4rem 1rem;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.05s linear;
            transform: translateY(-1px);
            text-decoration: none;
        }
        .btn-sm-3d:active { transform: translateY(3px); }

        .btn-sm-blue {
            background: #3b82f6;
            color: white;
            border: 1px solid #2563eb;
            box-shadow: 0 3px 0 #1e40af;
        }
        .btn-sm-blue:active { box-shadow: 0 0px 0 #1e40af; }
        .btn-sm-blue:hover { background: #2563eb; }

        .btn-sm-red {
            background: #ef4444;
            color: white;
            border: 1px solid #dc2626;
            box-shadow: 0 3px 0 #991b1b;
        }
        .btn-sm-red:active { box-shadow: 0 0px 0 #991b1b; }
        .btn-sm-red:hover { background: #dc2626; }

        .btn-sm-green {
            background: #10b981;
            color: white;
            border: 1px solid #059669;
            box-shadow: 0 3px 0 #047857;
        }
        .btn-sm-green:active { box-shadow: 0 0px 0 #047857; }
        .btn-sm-green:hover { background: #059669; }

        .role-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
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

        .stat-card {
            background: linear-gradient(115deg, #ffffff, #fefefe);
            border-radius: 1rem;
            transition: all 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -12px rgba(0,0,0,0.1);
        }

        .table-row-3d {
            transition: all 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            transform: translateZ(0);
        }
        .table-row-3d:hover {
            transform: translateX(4px) translateY(-2px) translateZ(6px);
            background: linear-gradient(90deg, #ffffff, #fafcff);
            box-shadow: 0 8px 15px -6px rgba(0, 0, 0, 0.08);
            position: relative;
            z-index: 2;
        }

        .custom-scroll::-webkit-scrollbar {
            height: 5px;
            width: 5px;
        }
        .custom-scroll::-webkit-scrollbar-track {
            background: #e2e8f0;
            border-radius: 10px;
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #94a3b8, #64748b);
            border-radius: 10px;
        }
    </style>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8 relative z-10">
        
        <!-- Success Alert -->
        @if(session('success'))
            <div class="bg-white/90 backdrop-blur-sm border-l-4 border-emerald-400 text-gray-700 p-4 rounded-xl shadow-md flex items-center justify-between transition-all hover:shadow-lg">
                <div class="font-medium">{{ session('success') }}</div>
                <button onclick="this.parentElement.style.display='none'" class="text-gray-400 hover:text-gray-600 transition">✕</button>
            </div>
        @endif

        <!-- Error Alert -->
        @if(session('error'))
            <div class="bg-red-50/90 backdrop-blur-sm border-l-4 border-red-400 text-red-700 p-4 rounded-xl shadow-md flex items-center justify-between transition-all hover:shadow-lg">
                <div class="font-medium">{{ session('error') }}</div>
                <button onclick="this.parentElement.style.display='none'" class="text-red-400 hover:text-red-600 transition">✕</button>
            </div>
        @endif

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="stat-card p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Users</p>
                        <p class="text-4xl font-black text-gray-800 mt-2">{{ $users->total() }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center text-blue-600">
                        <i class="fas fa-users fa-lg"></i>
                    </div>
                </div>
                <div class="mt-3 h-0.5 w-12 bg-gradient-to-r from-blue-300 to-transparent rounded-full"></div>
            </div>

            <div class="stat-card p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Admins</p>
                        <p class="text-4xl font-black text-gray-800 mt-2">{{ $users->pluck('role')->filter(fn($r) => $r === 'admin')->count() }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-red-50 to-red-100 flex items-center justify-center text-red-600">
                        <i class="fas fa-crown fa-lg"></i>
                    </div>
                </div>
                <div class="mt-3 h-0.5 w-12 bg-gradient-to-r from-red-300 to-transparent rounded-full"></div>
            </div>

            <div class="stat-card p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Regular Users</p>
                        <p class="text-4xl font-black text-gray-800 mt-2">{{ $users->pluck('role')->filter(fn($r) => $r === 'user')->count() }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-green-50 to-green-100 flex items-center justify-center text-green-600">
                        <i class="fas fa-user fa-lg"></i>
                    </div>
                </div>
                <div class="mt-3 h-0.5 w-12 bg-gradient-to-r from-green-300 to-transparent rounded-full"></div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card-cinematic rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-bold gradient-title">All Users</h3>
                <a href="{{ route('admin.users.create-admin') }}" class="btn-create-3d px-4 py-2 text-sm">
                    <i class="fas fa-user-plus"></i> Create Admin
                </a>
            </div>
            
            <div class="overflow-x-auto custom-scroll">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600 font-semibold uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3 text-left">Name</th>
                            <th class="px-6 py-3 text-left">Email</th>
                            <th class="px-6 py-3 text-left">Role</th>
                            <th class="px-6 py-3 text-left">Joined</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($users as $user)
                            <tr class="table-row-3d">
                                <td class="px-6 py-3 font-medium text-gray-800">
                                    {{ $user->first_name }} {{ $user->last_name }}
                                </td>
                                <td class="px-6 py-3 text-gray-600">
                                    {{ $user->email }}
                                </td>
                                <td class="px-6 py-3">
                                    @if($user->role === 'admin')
                                        <span class="role-badge role-badge-admin">
                                            <i class="fas fa-crown mr-1"></i> Admin
                                        </span>
                                    @else
                                        <span class="role-badge role-badge-user">
                                            <i class="fas fa-user mr-1"></i> User
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-gray-600 text-xs">
                                    {{ $user->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <div class="flex gap-2 justify-end">
                                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn-sm-3d btn-sm-blue">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        @if($user->id !== auth()->id())
                                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Delete user {{ addslashes($user->first_name . ' ' . $user->last_name) }}?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-sm-3d btn-sm-red">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-500 text-sm">
                                    No users found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($users->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
