<?php

namespace App\Exports;

use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KasExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected Enumerable $kasList;
    protected int $rowNumber = 0;

    public function __construct(Enumerable $kasList)
    {
        $this->kasList = $kasList;
    }

    public function collection(): Enumerable
    {
        return $this->kasList;
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Pencatat',
            'Jenis Transaksi',
            'Nominal (Rp)',
            'Keterangan',
        ];
    }

    public function map(mixed $row): array
    {
        $this->rowNumber++;
        $kas = $row;
        $tanggal = $kas->tanggal ? (is_string($kas->tanggal) ? date('d/m/Y', strtotime($kas->tanggal)) : $kas->tanggal->format('d/m/Y')) : '-';

        return [
            $this->rowNumber,
            $tanggal,
            $kas->user->nama ?? '-',
            $kas->jenis,
            (float) $kas->nominal,
            $kas->keterangan,
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
