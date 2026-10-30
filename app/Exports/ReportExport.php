<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReportExport implements FromCollection, WithHeadings, WithTitle
{
    /**
     * @param  array{total: float, data: array<string, float>}  $orderValueData
     * @param  array{total: float, data: array<string, float>}  $cashFlowData
     * @param  array{total_billed: float, total_paid: float, outstanding: float, orders_with_balance: int}  $receivables
     */
    public function __construct(
        private array $orderValueData,
        private array $cashFlowData,
        private array $receivables = [],
    ) {}

    public function collection(): Collection
    {
        $periodKeys = collect(array_keys($this->orderValueData['data']))
            ->merge(array_keys($this->cashFlowData['data']))
            ->unique()
            ->sort()
            ->values();

        $rows = [];

        foreach ($periodKeys as $period) {
            $rows[] = [
                'periode' => $period,
                'nilai_order' => $this->orderValueData['data'][$period] ?? 0,
                'kas_diterima' => $this->cashFlowData['data'][$period] ?? 0,
            ];
        }

        $rows[] = [
            'periode' => 'TOTAL',
            'nilai_order' => $this->orderValueData['total'],
            'kas_diterima' => $this->cashFlowData['total'],
        ];

        if (! empty($this->receivables)) {
            $rows[] = [
                'periode' => 'PIUTANG (sisa tagihan)',
                'nilai_order' => $this->receivables['outstanding'],
                'kas_diterima' => null,
            ];
        }

        return collect($rows);
    }

    public function headings(): array
    {
        return [
            'Periode',
            'Nilai Order (Rp)',
            'Kas Diterima (Rp)',
        ];
    }

    public function title(): string
    {
        return 'Laporan Nilai Order & Kas';
    }
}
