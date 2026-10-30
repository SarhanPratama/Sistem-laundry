<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReportExport implements FromCollection, WithHeadings, WithTitle
{
    /**
     * @param  array{total: float, data: array<string, float>}  $reportData
     */
    public function __construct(private array $reportData) {}

    public function collection(): Collection
    {
        $rows = [];

        foreach ($this->reportData['data'] as $period => $amount) {
            $rows[] = [
                'periode'    => $period,
                'pendapatan' => $amount,
            ];
        }

        $rows[] = [
            'periode'    => 'TOTAL',
            'pendapatan' => $this->reportData['total'],
        ];

        return collect($rows);
    }

    public function headings(): array
    {
        return [
            'Periode',
            'Pendapatan (Rp)',
        ];
    }

    public function title(): string
    {
        return 'Laporan Pendapatan';
    }
}
