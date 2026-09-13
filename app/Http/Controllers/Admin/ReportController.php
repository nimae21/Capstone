<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function index(Request $request, AnalyticsService $analytics): View
    {
        return view('admin.reports.index', $analytics->report($this->selectedYear($request)));
    }

    public function exportPdf(Request $request, AnalyticsService $analytics): Response
    {
        $data = $analytics->report($this->selectedYear($request));
        $pdf = Pdf::loadView('admin.reports.pdf', $data)->setPaper('a4', 'portrait');

        return $pdf->download('sales-report-'.$data['selectedYear'].'.pdf');
    }

    private function selectedYear(Request $request): int
    {
        $filters = $request->validate([
            'year' => ['nullable', 'integer', 'min:2000', 'max:'.(now()->year + 1)],
        ]);

        return (int) ($filters['year'] ?? now()->year);
    }
}
