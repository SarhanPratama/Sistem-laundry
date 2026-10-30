<?php

namespace App\Http\Controllers;

use App\Exports\ReportExport;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->input('end_date', Carbon::today()->format('Y-m-d'));
        $period    = $request->input('period', 'daily');

        $revenueData     = $this->reportService->getRevenueReport($period, $startDate, $endDate);
        $orderSummary    = $this->reportService->getOrderSummary($startDate, $endDate);
        $popularServices = $this->reportService->getPopularServices($startDate, $endDate);

        return view('reports.index', compact(
            'revenueData',
            'orderSummary',
            'popularServices',
            'startDate',
            'endDate',
            'period'
        ));
    }

    public function export(Request $request)
    {
        $request->validate([
            'format'     => 'required|in:pdf,excel',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date',
            'period'     => 'nullable|in:daily,weekly,monthly',
        ]);

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->input('end_date', Carbon::today()->format('Y-m-d'));
        $period    = $request->input('period', 'daily');

        $revenueData     = $this->reportService->getRevenueReport($period, $startDate, $endDate);
        $orderSummary    = $this->reportService->getOrderSummary($startDate, $endDate);
        $popularServices = $this->reportService->getPopularServices($startDate, $endDate);

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('reports.pdf', compact(
                'revenueData',
                'orderSummary',
                'popularServices',
                'startDate',
                'endDate',
                'period'
            ));

            return $pdf->download('laporan-' . $startDate . '-sd-' . $endDate . '.pdf');
        }

        return Excel::download(
            new ReportExport($revenueData),
            'laporan-' . $startDate . '-sd-' . $endDate . '.xlsx'
        );
    }
}
