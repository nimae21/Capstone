<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Helvetica, sans-serif; font-size: 11px; color: #1e293b; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        .subtitle { color: #64748b; margin-bottom: 20px; }
        .stats-grid { width: 100%; margin-bottom: 20px; }
        .stats-grid td { padding: 8px; border: 1px solid #e2e8f0; width: 25%; }
        .stat-label { font-size: 9px; color: #64748b; text-transform: uppercase; }
        .stat-value { font-size: 16px; font-weight: bold; margin-top: 2px; }
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        table.data-table th { background: #f8fafc; text-align: left; padding: 6px 10px; border-bottom: 2px solid #e2e8f0; font-size: 9px; text-transform: uppercase; color: #64748b; }
        table.data-table td { padding: 6px 10px; border-bottom: 1px solid #f1f5f9; }
        .section-title { font-size: 14px; font-weight: bold; margin: 20px 0 10px; border-bottom: 2px solid #dc2626; padding-bottom: 4px; }
        .text-right { text-align: right; }
    </style>
</head>
<body>

    <h1>Achilles — Sales Report</h1>
    <p class="subtitle">Generated {{ now()->format('F d, Y H:i A') }} · Year: {{ $selectedYear }}</p>

    <table class="stats-grid">
        <tr>
            <td>
                <div class="stat-label">Total Sales</div>
                <div class="stat-value">₱{{ number_format($totalSales, 2) }}</div>
            </td>
            <td>
                <div class="stat-label">Total Orders</div>
                <div class="stat-value">{{ $totalOrders }}</div>
            </td>
            <td>
                <div class="stat-label">Average Order</div>
                <div class="stat-value">₱{{ number_format($averageOrderValue, 2) }}</div>
            </td>
            <td>
                <div class="stat-label">Total Customers</div>
                <div class="stat-value">{{ $totalCustomers }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Monthly Sales — {{ $selectedYear }}</div>
    <table class="data-table">
        <thead>
            <tr><th>Month</th><th class="text-right">Sales</th></tr>
        </thead>
        <tbody>
            @foreach($monthlyLabels as $index => $label)
                <tr>
                    <td>{{ $label }}</td>
                    <td class="text-right">₱{{ number_format($monthlySalesData[$index], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Order Status Summary</div>
    <table class="data-table">
        <thead>
            <tr><th>Status</th><th class="text-right">Count</th></tr>
        </thead>
        <tbody>
            @foreach($ordersByStatus as $row)
                <tr>
                    <td>{{ $row->status->label() }}</td>
                    <td class="text-right">{{ $row->total }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Top Customers</div>
    <table class="data-table">
        <thead>
            <tr><th>Customer</th><th class="text-right">Total Spent</th></tr>
        </thead>
        <tbody>
            @foreach($topCustomers as $customer)
                <tr>
                    <td>{{ $customer->user->full_name ?? 'Guest User' }}</td>
                    <td class="text-right">₱{{ number_format($customer->total_spent, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Sales by Province</div>
    <table class="data-table">
        <thead>
            <tr><th>Province</th><th class="text-right">Orders</th><th class="text-right">Sales</th></tr>
        </thead>
        <tbody>
            @foreach($salesByProvince as $row)
                <tr>
                    <td>{{ $row->province }}</td>
                    <td class="text-right">{{ $row->order_count }}</td>
                    <td class="text-right">₱{{ number_format($row->total_sales, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>