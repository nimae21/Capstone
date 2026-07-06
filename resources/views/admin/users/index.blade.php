@extends('layouts.admin')

@section('title', 'Users Management')
@section('page-title', 'Users')
@section('page-subtitle', 'Manage all users and admin accounts.')

@section('styles')
    <!-- Tailwind + Google Fonts -->
    <script src="https://cdn.tailwindcss.com">
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: linear-gradient(145deg, #f0f4f8 0%, #e9eef3 100%);
        }

        .card-3d {
            transition: all 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            transform: translateZ(0);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03), 0 1px 2px rgba(0, 0, 0, 0.04);
            will-change: transform;
            backface-visibility: hidden;
        }
        .card-3d:hover {
            transform: translateY(-4px) translateZ(8px) scale(1.01);
            box-shadow: 0 20px 30px -12px rgba(0, 0, 0, 0.12), 0 4px 8px -4px rgba(0, 0, 0, 0.04);
        }

        .gradient-title {
            font-weight: 900 !important;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, #000000 0%, #dc2626 50%, #000000 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            font-size: 2.5rem;
            display: inline-block;
        }

        .section-header {
            font-weight: 800 !important;
            font-size: 1.15rem !important;
            letter-spacing: -0.01em;
            position: relative;
            display: inline-block;
        }
        .section-header::after {
            content: '';
            position: absolute;
            bottom: -6px;
            left: 0;
            width: 36px;
            height: 3px;
            background: linear-gradient(90deg, #dc2626, #ef4444);
            border-radius: 3px;
        }

        /* ===== STAT CARDS (dashboard style) ===== */
        .stat-card {
            position: relative;
            border-radius: 1rem;
            padding: 1.5rem 1.5rem 1.5rem 1.5rem;
            display: flex;
            flex-direction: column;
            min-height: 120px;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            border: 1px solid rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(2px);
        }

        .stat-card .stat-icon-bg {
            position: absolute;
            right: -10px;
            bottom: -10px;
            font-size: 6rem;
            opacity: 0.18;
            pointer-events: none;
            transform: rotate(8deg);
            line-height: 1;
            transition: opacity 0.3s ease;
        }
        .stat-card:hover .stat-icon-bg {
            opacity: 0.25;
        }

        .stat-card .stat-number {
            font-weight: 900 !important;
            font-size: 2.2rem !important;
            line-height: 1.2;
            letter-spacing: -0.02em;
            position: relative;
            z-index: 2;
        }

        .stat-card .stat-label {
            font-weight: 600 !important;
            font-size: 0.78rem !important;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            opacity: 0.75;
            position: relative;
            z-index: 2;
        }

        .stat-card .stat-sub {
            font-size: 0.7rem;
            opacity: 0.7;
            margin-top: 0.25rem;
            position: relative;
            z-index: 2;
        }

        .stat-card .stat-accent-line {
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            border-radius: 0 4px 4px 0;
            z-index: 3;
        }

        .stat-blue {
            background: linear-gradient(135deg, #ffffff 0%, #eff6ff 100%);
            border-left: 4px solid #3b82f6;
        }
        .stat-blue .stat-number {
            color: #1e40af;
        }
        .stat-blue .stat-label {
            color: #2563eb;
        }

        .stat-red {
            background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%);
            border-left: 4px solid #ef4444;
        }
        .stat-red .stat-number {
            color: #991b1b;
        }
        .stat-red .stat-label {
            color: #dc2626;
        }

        .stat-green {
            background: linear-gradient(135deg, #ffffff 0%, #ecfdf5 100%);
            border-left: 4px solid #10b981;
        }
        .stat-green .stat-number {
            color: #065f46;
        }
        .stat-green .stat-label {
            color: #059669;
        }

        /* ===== BUTTONS ===== */
        .btn-create-3d {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            font-weight: 700;
            padding: 0.65rem 1.5rem;
            border-radius: 0.75rem;
            border: none;
            cursor: pointer;
            transition: all 0.15s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            box-shadow: 0 4px 0 #991b1b, 0 2px 8px rgba(0, 0, 0, 0.06);
            transform: translateY(0);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-create-3d:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 0 #991b1b, 0 8px 16px -4px rgba(220, 38, 38, 0.2);
        }
        .btn-create-3d:active {
            transform: translateY(4px);
            box-shadow: 0 2px 0 #991b1b;
        }

        .btn-sm-3d {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.4rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            transition: all 0.15s;
            transform: translateY(0);
            box-shadow: 0 2px 0 rgba(0, 0, 0, 0.1);
        }
        .btn-sm-3d:active {
            transform: translateY(2px);
            box-shadow: 0 1px 0 rgba(0, 0, 0, 0.1);
        }
        .btn-sm-blue {
            background: #3b82f6;
            color: white;
            box-shadow: 0 2px 0 #1d4ed8;
        }
        .btn-sm-blue:hover {
            background: #2563eb;
            color: white;
        }
        .btn-sm-red {
            background: #ef4444;
            color: white;
            box-shadow: 0 2px 0 #b91c1c;
        }
        .btn-sm-red:hover {
            background: #dc2626;
            color: white;
        }

        /* Role badges */
        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
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

        /* Table row */
        .table-row-3d {
            transition: all 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            transform: translateZ(0);
            border-bottom: 1.5px solid #e5e7eb;
        }
        .table-row-3d:last-child {
            border-bottom: none;
        }
        .table-row-3d:hover {
            transform: translateX(4px) translateY(-2px) translateZ(8px);
            background: #ffffff;
            box-shadow: 0 8px 15px -6px rgba(0, 0, 0, 0.07);
            z-index: 2;
            position: relative;
        }

        /* Custom scroll */
        .custom-scroll::-webkit-scrollbar {
            height: 4px;
            width: 4px;
        }
        .custom-scroll::-webkit-scrollbar-track {
            background: #e2e8f0;
            border-radius: 10px;
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        /* Responsive */
        @media (max-width: 640px) {
            .gradient-title {
                font-size: 2rem;
            }
            .stat-card .stat-number {
                font-size: 1.8rem !important;
            }
            .stat-card .stat-icon-bg {
                font-size: 4rem;
                right: -5px;
                bottom: -5px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 relative z-10">

        <!-- ===== HEADER ===== -->
        <div class="mb-6">
            <h1 class="gradient-title">Users</h1>
            <p class="text-gray-500 text-sm mt-1 flex items-center gap-2">
                <i class="fas fa-users text-gray-400"></i>
                Manage all users and admin accounts.
            </p>
        </div>

        <!-- ===== SUCCESS / ERROR ALERTS ===== -->
        @if(session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 rounded-xl mb-6 flex items-center gap-3">
                <i class="fas fa-check-circle text-emerald-500 text-xl"></i>
                <span>{{ session('success') }}</span>
                <button onclick="this.parentElement.style.display='none'" class="ml-auto text-emerald-600 hover:text-emerald-800 transition">✕</button>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 text-red-800 p-4 rounded-xl mb-6 flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                <span>{{ session('error') }}</span>
                <button onclick="this.parentElement.style.display='none'" class="ml-auto text-red-600 hover:text-red-800 transition">✕</button>
            </div>
        @endif

        <!-- ===== STAT CARDS ===== -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
            <!-- Total Users -->
            <div class="stat-card stat-blue card-3d">
                <div class="stat-accent-line" style="background:#3b82f6;"></div>
                <span class="stat-icon-bg">👥</span>
                <div class="flex-1">
                    <p class="stat-label">Total Users</p>
                    <p class="stat-number">{{ $users->total() }}</p>
                    <p class="stat-sub">All registered accounts</p>
                </div>
            </div>

            <!-- Admins -->
            <div class="stat-card stat-red card-3d">
                <div class="stat-accent-line" style="background:#ef4444;"></div>
                <span class="stat-icon-bg">👑</span>
                <div class="flex-1">
                    <p class="stat-label">Admins</p>
                    <p class="stat-number">{{ $users->pluck('role')->filter(fn($r) => $r === 'admin')->count() }}</p>
                    <p class="stat-sub">Administrators</p>
                </div>
            </div>

            <!-- Regular Users -->
            <div class="stat-card stat-green card-3d">
                <div class="stat-accent-line" style="background:#10b981;"></div>
                <span class="stat-icon-bg">👤</span>
                <div class="flex-1">
                    <p class="stat-label">Regular Users</p>
                    <p class="stat-number">{{ $users->pluck('role')->filter(fn($r) => $r === 'user')->count() }}</p>
                    <p class="stat-sub">Standard accounts</p>
                </div>
            </div>
        </div>

        <!-- ===== USER TABLE ===== -->
        <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center flex-wrap gap-3">
                <h3 class="section-header text-gray-800">All Users</h3>
                <a href="{{ route('admin.users.create-admin') }}" class="btn-create-3d text-sm">
                    <i class="fas fa-user-plus"></i> Create Admin
                </a>
            </div>

            <div class="overflow-x-auto custom-scroll">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50/60 text-gray-600 font-semibold uppercase tracking-wider">
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
                                            <i class="fas fa-crown"></i> Admin
                                        </span>
                                    @else
                                        <span class="role-badge role-badge-user">
                                            <i class="fas fa-user"></i> User
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-gray-600 text-xs">
                                    {{ $user->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <div class="flex gap-2 justify-end flex-wrap">
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
                <div class="px-6 py-4 border-t border-gray-100 flex justify-center">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection