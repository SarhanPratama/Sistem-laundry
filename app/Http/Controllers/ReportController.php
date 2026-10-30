<?php

namespace App\Http\Controllers;

use App\Exports\ReportExport;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::today()->format('Y-m-d'));
        $period = $request->input('period', 'daily');

        $orderValueData = $this->reportService->getOrderValueReport($period, $startDate, $endDate);
        $cashFlowData = $this->reportService->getCashFlowReport($period, $startDate, $endDate);
        $receivables = $this->reportService->getReceivablesReport($startDate, $endDate);
        $orderSummary = $this->reportService->getOrderSummary($startDate, $endDate);
        $popularServices = $this->reportService->getPopularServices($startDate, $endDate);
        $periodKeys = $this->periodKeys($orderValueData, $cashFlowData);

        return view('reports.index', compact(
            'orderValueData',
            'cashFlowData',
            'receivables',
            'orderSummary',
            'popularServices',
            'periodKeys',
            'startDate',
            'endDate',
            'period'
        ));
    }

    public function export(Request $request)
    {
        $request->validate([
            'format' => 'required|in:pdf,excel',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'period' => 'nullable|in:daily,weekly,monthly',
        ]);

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::today()->format('Y-m-d'));
        $period = $request->input('period', 'daily');

        $orderValueData = $this->reportService->getOrderValueReport($period, $startDate, $endDate);
        $cashFlowData = $this->reportService->getCashFlowReport($period, $startDate, $endDate);
        $receivables = $this->reportService->getReceivablesReport($startDate, $endDate);
        $orderSummary = $this->reportService->getOrderSummary($startDate, $endDate);
        $popularServices = $this->reportService->getPopularServices($startDate, $endDate);
        $periodKeys = $this->periodKeys($orderValueData, $cashFlowData);

        if ($request->input('format') === 'pdf') {
            $pdf = Pdf::loadView('reports.pdf', compact(
                'orderValueData',
                'cashFlowData',
                'receivables',
                'orderSummary',
                'popularServices',
                'periodKeys',
                'startDate',
                'endDate',
                'period'
            ));

            return $pdf->download('laporan-'.$startDate.'-sd-'.$endDate.'.pdf');
        }

        return Excel::download(
            new ReportExport($orderValueData, $cashFlowData, $receivables),
            'laporan-'.$startDate.'-sd-'.$endDate.'.xlsx'
        );
    }

    /**
     * Union of period keys from order value and cash flow reports.
     *
     * @return Collection<int, string>
     */
    private function periodKeys(array $orderValueData, array $cashFlowData)
    {
        return collect(array_keys($orderValueData['data']))
            ->merge(array_keys($cashFlowData['data']))
            ->unique()
            ->sort()
            ->values();
    }
}
