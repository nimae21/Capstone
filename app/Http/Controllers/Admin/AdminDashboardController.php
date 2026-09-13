<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(AnalyticsService $analytics): View
    {
        return view('admin.dashboard', $analytics->dashboard());
    }
}
