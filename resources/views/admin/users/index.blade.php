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
        .btn-sm-green {
    background: #10b981;
    color: white;
    box-shadow: 0 2px 0 #047857;
}

.btn-sm-green:hover {
    background: #059669;
    color: white;
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

        @if(request('search'))
    <div class="px-6 pt-4 text-sm text-gray-500">
        Showing
        <span class="font-semibold text-gray-700">
            {{ $users->total() }}
        </span>
        result(s) for
        <span class="font-semibold text-red-600">
            "{{ request('search') }}"
        </span>

        <a href="{{ route('admin.users.index') }}"
           class="ml-2 text-red-600 hover:underline">
            Clear
        </a>
    </div>
@endif

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
                    <p class="stat-number">{{ $totalUsers }}</p>
                    <p class="stat-sub">All registered accounts</p>
                </div>
            </div>

            <!-- Admins -->
            <div class="stat-card stat-red card-3d">
                <div class="stat-accent-line" style="background:#ef4444;"></div>
                <span class="stat-icon-bg">👑</span>
                <div class="flex-1">
                    <p class="stat-label">Admins</p>
                    <p class="stat-number">{{ $totalAdmins }}</p>
                    <p class="stat-sub">Administrators</p>
                </div>
            </div>

            <!-- Regular Users -->
            <div class="stat-card stat-green card-3d">
                <div class="stat-accent-line" style="background:#10b981;"></div>
                <span class="stat-icon-bg">👤</span>
                <div class="flex-1">
                    <p class="stat-label">Regular Users</p>
                    <p class="stat-number">{{ $totalRegularUsers }}</p>
                    <p class="stat-sub">Standard accounts</p>
                </div>
            </div>
        </div>

        <!-- ===== USER TABLE ===== -->
        <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

    <h3 class="section-header text-gray-800">
        All Users
    </h3>

    <div class="flex items-center gap-3">

        <form method="GET"
              action="{{ route('admin.users.index') }}"
              class="relative">

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search users..."
                class="w-72 pl-10 pr-4 py-2.5 rounded-xl border border-gray-200
                       focus:ring-2 focus:ring-red-500 focus:border-red-500
                       outline-none transition bg-white shadow-sm">

            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>

        </form>

        <a href="{{ route('admin.users.create-admin') }}"
           class="btn-create-3d text-sm">
            <i class="fas fa-user-plus"></i>
            Create Admin
        </a>

    </div>

</div>

            <div class="overflow-x-auto custom-scroll">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50/60 text-gray-600 font-semibold uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3 text-left">Name</th>
                            <th class="px-6 py-3 text-left">Email</th>
                            <th class="px-6 py-3 text-left">Role</th>
                            <th class="px-6 py-3 text-left">Joined</th>
                            <th class="px-6 py-3 text-left">
    Status
</th>   
                            <th class="px-6 py-3 text-right">Actions</th>

                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($users as $user)
                            <tr class="table-row-3d">
                                <td class="px-6 py-3 font-medium text-gray-800">
                                    <div class="flex items-center gap-2">
    <span>{{ $user->first_name }} {{ $user->last_name }}</span>

    @if($user->id === auth()->id())
        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700 font-semibold">
            You
        </span>
    @endif
</div>
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
                                <td class="px-6 py-3">
    @if($user->is_active)
        <span class="role-badge bg-green-100 text-green-700">
            <i class="fas fa-check-circle"></i>
            Active
        </span>
    @else
        <span class="role-badge bg-red-100 text-red-700">
            <i class="fas fa-ban"></i>
            Suspended
        </span>
    @endif
</td>
                                <td class="px-6 py-3 text-right">
                                    <div class="flex gap-2 justify-end flex-wrap">
                                        @if($user->id !== auth()->id())
    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn-sm-3d btn-sm-blue">
        <i class="fas fa-edit"></i> Edit
    </a>
@endif
                                        @if($user->id !== auth()->id())
                                                <form
    action="{{ route('admin.users.toggle-status', $user) }}"
    method="POST"
    class="inline"
>
    @csrf
    @method('PATCH')

    @if($user->is_active)
        <button
            type="submit"
            class="btn-sm-3d btn-sm-red"
            onclick="return confirm('Suspend this user?')"
        >
            <i class="fas fa-ban"></i>
            Suspend
        </button>
    @else
        <button
            type="submit"
            class="btn-sm-3d btn-sm-green"
            onclick="return confirm('Activate this user?')"
        >
            <i class="fas fa-check"></i>
            Activate
        </button>
    @endif
</form>
                                            
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
    <td colspan="6" class="px-6 py-12 text-center">

        <div class="flex flex-col items-center">

            <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                <i class="fas fa-users text-2xl text-gray-400"></i>
            </div>

            <h4 class="text-lg font-semibold text-gray-700">
                No users found
            </h4>

            <p class="text-sm text-gray-500 mt-1">
                Try another search or clear the current filter.
            </p>

        </div>

    </td>
</tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($users->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 flex justify-center">
                    {{ $users->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection