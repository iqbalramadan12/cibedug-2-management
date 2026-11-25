<?php

namespace App\Exports;

use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AbsensiExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected Enumerable $absensiList;
    protected int $rowNumber = 0;

    public function __construct(Enumerable $absensiList)
    {
        $this->absensiList = $absensiList;
    }

    public function collection(): Enumerable
    {
        return $this->absensiList;
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
            'Status Kehadiran',
            'Keterangan',
        ];
    }

    public function map(mixed $row): array
    {
        $this->rowNumber++;
        $absensi = $row;
        $tanggal = $absensi->tanggal ? (is_string($absensi->tanggal) ? date('d/m/Y', strtotime($absensi->tanggal)) : $absensi->tanggal->format('d/m/Y')) : '-';

        return [
            $this->rowNumber,
            $tanggal,
            $absensi->anggota->nis ?? '-',
            $absensi->anggota->user->nama ?? '-',
            $absensi->anggota->kelas ?? '-',
            $absensi->anggota->jurusan ?? '-',
            $absensi->status,
            $absensi->keterangan ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
