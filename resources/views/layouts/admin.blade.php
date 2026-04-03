    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>@yield('title', 'Admin Dashboard') | Shoe Store</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: 'Segoe UI', sans-serif;
            }

            body {
                background: #f5f5f5;
                color: #222;
                display: flex;
            }

            /* SIDEBAR */
            .sidebar {
                width: 260px;
                height: 100vh;
                background: #111;
                color: white;
                padding: 25px 20px;
                position: fixed;
                top: 0;
                left: 0;
                overflow-y: auto;
            }

            .sidebar .logo {
                font-size: 28px;
                font-weight: bold;
                color: #e60023;
                margin-bottom: 35px;
                text-align: center;
            }

            .sidebar h3 {
                font-size: 14px;
                color: #aaa;
                margin-bottom: 10px;
                margin-top: 20px;
                text-transform: uppercase;
                letter-spacing: 1px;
            }

            .sidebar a {
                display: block;
                color: white;
                text-decoration: none;
                padding: 12px 15px;
                border-radius: 10px;
                margin-bottom: 10px;
                transition: 0.3s ease;
                font-size: 15px;
            }

            .sidebar a:hover,
            .sidebar a.active {
                background: #e60023;
            }

            /* MAIN CONTENT */
            .main {
                margin-left: 260px;
                width: calc(100% - 260px);
                padding: 30px;
            }

            /* TOPBAR */
            .topbar {
                background: white;
                padding: 20px 25px;
                border-radius: 18px;
                box-shadow: 0 5px 20px rgba(0,0,0,0.08);
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 25px;
            }

            .topbar h1 {
                font-size: 28px;
                color: #111;
            }

            .topbar .admin-info {
                font-size: 15px;
                color: #555;
            }

            /* GENERAL */
            .section-title {
                font-size: 22px;
                margin-bottom: 18px;
                color: #111;
            }

            .panel {
                background: white;
                border-radius: 18px;
                padding: 25px;
                box-shadow: 0 5px 18px rgba(0,0,0,0.08);
            }

            /* RESPONSIVE */
            @media (max-width: 992px) {
                .dashboard-grid {
                    grid-template-columns: 1fr;
                }
            }

            @media (max-width: 768px) {
                .sidebar {
                    display: none;
                }

                .main {
                    margin-left: 0;
                    width: 100%;
                    padding: 20px;
                }

                .topbar {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 10px;
                }
            }
        </style>

        @yield('styles')
    </head>
    <body>

        <!-- SIDEBAR -->
        <div class="sidebar">
            <div class="logo">Achilles</div>

            <h3>Main</h3>
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{route('admin.products.index')}}">Products</a>
            <a href="{{route('admin.categories.index')}}">Categories</a>
            <a href="{{route('admin.brands.index')}}">Brands</a>
            <a href="#">Orders</a>
            <a href="#">Users</a>

            <h3>Analytics</h3>
            <a href="#">Reports</a>
            <a href="#">Sales</a>
            <a href="#">Inventory</a>

            <h3>Account</h3>
            <a href="#">Settings</a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" style="
                    width: 100%;
                    background: none;
                    border: none;
                    color: white;
                    text-align: left;
                    padding: 12px 15px;
                    border-radius: 10px;
                    font-size: 15px;
                    cursor: pointer;
                    transition: 0.3s ease;
                " onmouseover="this.style.background='#e60023'" onmouseout="this.style.background='none'">
                    Logout
                </button>
            </form>
        </div>

        <!-- MAIN -->
        <div class="main">

            <!-- TOPBAR -->
            <div class="topbar">
                <div>
                    <h1>@yield('page-title', 'Admin Dashboard')</h1>
                    <p style="color:#666; margin-top: 5px;">@yield('page-subtitle', 'Manage your online shoe store efficiently.')</p>
                </div>
                <div class="admin-info">
                    Logged in as <strong>{{ auth()->user()->first_name ?? auth()->user()->name ?? 'Admin' }}</strong>
                </div>
            </div>

            @yield('content')

        </div>

    </body>
    </html>