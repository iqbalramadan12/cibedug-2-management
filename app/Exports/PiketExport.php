<?php

namespace App\Exports;

use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PiketExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected Enumerable $piketList;
    protected int $rowNumber = 0;

    public function __construct(Enumerable $piketList)
    {
        $this->piketList = $piketList;
    }

    public function collection(): Enumerable
    {
        return $this->piketList;
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'NIS',
            'Nama Siswa',
            'Kelas',
            'Jurusan',
            'Tugas Piket',
            'Status',
        ];
    }

    public function map(mixed $row): array
    {
        $this->rowNumber++;
        $piket = $row;
        $tanggal = $piket->tanggal ? (is_string($piket->tanggal) ? date('d/m/Y', strtotime($piket->tanggal)) : $piket->tanggal->format('d/m/Y')) : '-';

        return [
            $this->rowNumber,
            $tanggal,
            $piket->anggota->nis ?? '-',
            $piket->anggota->user->nama ?? '-',
            $piket->anggota->kelas ?? '-',
            $piket->anggota->jurusan ?? '-',
            $piket->tugas,
            $piket->status,
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
