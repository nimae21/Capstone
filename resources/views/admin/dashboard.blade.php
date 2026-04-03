    @extends('layouts.admin')

    @section('title', 'Admin Dashboard')
    @section('page-title', 'Admin Dashboard')
    @section('page-subtitle', 'Manage your online shoe store efficiently.')

    @section('styles')
    <style>
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            border-left: 6px solid #e60023;
            padding: 25px;
            border-radius: 18px;
            box-shadow: 0 5px 18px rgba(0,0,0,0.08);
            transition: 0.3s ease;
        }

        .card:hover {
            transform: translateY(-4px);
        }

        .card h2 {
            font-size: 32px;
            color: #111;
            margin-bottom: 8px;
        }

        .card p {
            font-size: 15px;
            color: #666;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 35px;
        }

        .action-btn {
            background: #111;
            color: white;
            text-decoration: none;
            padding: 18px;
            text-align: center;
            border-radius: 15px;
            font-weight: 600;
            transition: 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .action-btn:hover {
            background: #e60023;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table thead {
            background: #111;
            color: white;
        }

        table th,
        table td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }

        .status.pending {
            background: #fff3cd;
            color: #856404;
        }

        .status.completed {
            background: #d4edda;
            color: #155724;
        }

        .alert-item {
            background: #fff5f5;
            border-left: 5px solid #e60023;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 15px;
        }

        .alert-item strong {
            color: #111;
            display: block;
            margin-bottom: 5px;
        }

        .alert-item span {
            color: #666;
            font-size: 14px;
        }
    </style>
    @endsection

    @section('content')

        <!-- CARDS -->
        <div class="cards">
            <div class="card">
                <h2>124</h2>
                <p>Total Products</p>
            </div>
            <div class="card">
                <h2>58</h2>
                <p>Total Orders</p>
            </div>
            <div class="card">
                <h2>39</h2>
                <p>Registered Users</p>
            </div>
            <div class="card">
                <h2>7</h2>
                <p>Low Stock Items</p>
            </div>
        </div>

        <!-- QUICK ACTIONS -->
        <h2 class="section-title">Quick Actions</h2>
        <div class="quick-actions">
            <a href="#" class="action-btn">+ Add Product</a>
            <a href="#" class="action-btn">+ Add Category</a>
            <a href="#" class="action-btn">Manage Orders</a>
            <a href="#" class="action-btn">Manage Users</a>
        </div>

        <!-- LOWER GRID -->
        <div class="dashboard-grid">

            <!-- RECENT ORDERS -->
            <div class="panel">
                <h3>Recent Orders</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Product</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#1001</td>
                            <td>Juan Dela Cruz</td>
                            <td>Nike Air Max</td>
                            <td><span class="status pending">Pending</span></td>
                        </tr>
                        <tr>
                            <td>#1002</td>
                            <td>Maria Santos</td>
                            <td>Adidas Samba</td>
                            <td><span class="status completed">Completed</span></td>
                        </tr>
                        <tr>
                            <td>#1003</td>
                            <td>Kevin Reyes</td>
                            <td>Puma RS-X</td>
                            <td><span class="status pending">Pending</span></td>
                        </tr>
                        <tr>
                            <td>#1004</td>
                            <td>Ana Lopez</td>
                            <td>New Balance 550</td>
                            <td><span class="status completed">Completed</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- LOW STOCK -->
            <div class="panel">
                <h3>Low Stock Alerts</h3>

                <div class="alert-item">
                    <strong>Nike Air Force 1</strong>
                    <span>Only 3 items left in stock</span>
                </div>

                <div class="alert-item">
                    <strong>Adidas Ultraboost</strong>
                    <span>Only 2 items left in stock</span>
                </div>

                <div class="alert-item">
                    <strong>Converse Chuck Taylor</strong>
                    <span>Only 5 items left in stock</span>
                </div>

                <div class="alert-item">
                    <strong>Vans Old Skool</strong>
                    <span>Only 4 items left in stock</span>
                </div>
            </div>

        </div>

    @endsection