<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Jadwal Piket - Cibedug 2 Management</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #315C49;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 16px;
            margin: 0;
            color: #244C3D;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header h2 {
            font-size: 13px;
            margin: 4px 0 0 0;
            color: #555;
            font-weight: normal;
        }
        .meta-info {
            margin-bottom: 15px;
            font-size: 10px;
        }
        .meta-info table {
            width: 100%;
        }
        .meta-info td {
            padding: 2px 0;
        }
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table-data th, .table-data td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            font-size: 10px;
        }
        .table-data th {
            background-color: #002b49;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
        }
        .table-data tr:nth-child(even) {
            background-color: #f9fbfd;
        }
        .badge-selesai { color: #10b981; font-weight: bold; }
        .badge-belum { color: #f59e0b; font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .summary-box {
            width: 50%;
            float: right;
            margin-top: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 5px 10px;
            font-size: 10px;
            border-bottom: 1px solid #f1f5f9;
        }
        .summary-table tr:last-child td {
            border-bottom: none;
            font-weight: bold;
            background-color: #f8fafc;
        }
        .clearfix { clear: both; }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
        }
        .signature-box {
            display: inline-block;
            text-align: center;
            width: 180px;
        }
        .signature-space { height: 50px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CIBEDUG 2 MANAGEMENT</h1>
        <h2>Rayon Cibedug 2 &bull; SMK Wikrama Bogor</h2>
        <div style="font-size: 12px; color: #315C49; font-weight: bold; margin-top: 4px;">Laporan Jadwal & Status Piket Kebersihan</div>
    </div>

    <div class="meta-info">
        <table>
            <tr>
                <td style="width: 15%;"><strong>Tanggal Cetak</strong></td>
                <td style="width: 35%;">: {{ date('d F Y, H:i') }} WIB</td>
                <td style="width: 15%;"><strong>Total Data</strong></td>
                <td style="width: 35%;">: {{ count($items) }} Jadwal</td>
            </tr>
            @if(isset($statusFilter) && $statusFilter && $statusFilter !== 'all')
            <tr>
                <td><strong>Filter Status</strong></td>
                <td>: {{ $statusFilter }}</td>
                <td></td>
                <td></td>
            </tr>
            @endif
        </table>
    </div>

    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 15%;">Tanggal</th>
                <th style="width: 12%;">NIS</th>
                <th style="width: 25%;">Nama Siswa</th>
                <th style="width: 12%;">Kelas</th>
                <th style="width: 18%;">Tugas Piket</th>
                <th style="width: 13%;" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                $selesai = 0; $belum = 0;
            @endphp
            @forelse($items as $index => $piket)
                @php
                    if ($piket->status === 'Selesai') $selesai++;
                    else $belum++;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $piket->tanggal ? (is_string($piket->tanggal) ? date('d/m/Y', strtotime($piket->tanggal)) : $piket->tanggal->format('d/m/Y')) : '-' }}</td>
                    <td>{{ $piket->anggota->nis ?? '-' }}</td>
                    <td>{{ $piket->anggota->user->nama ?? '-' }}</td>
                    <td>{{ $piket->anggota->kelas ?? '-' }}</td>
                    <td>{{ $piket->tugas }}</td>
                    <td class="text-center">
                        <span class="badge-{{ strtolower($piket->status) }}">{{ $piket->status }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 15px; color: #666;">Tidak ada jadwal piket yang sesuai.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary-box">
        <table class="summary-table">
            <tr>
                <td>Tugas Selesai</td>
                <td class="text-right badge-selesai">{{ $selesai }}</td>
            </tr>
            <tr>
                <td>Tugas Belum Selesai</td>
                <td class="text-right badge-belum">{{ $belum }}</td>
            </tr>
            <tr>
                <td>Total Jadwal</td>
                <td class="text-right">{{ count($items) }}</td>
            </tr>
        </table>
    </div>
    <div class="clearfix"></div>

    <div class="footer">
        <div class="signature-box">
            <div>Dicetak oleh,</div>
            <div class="signature-space"></div>
            <div><strong>{{ auth()->user()->nama ?? 'Administrator' }}</strong></div>
            <div style="font-size: 9px; color: #666;">{{ ucfirst(auth()->user()->role ?? 'Admin') }}</div>
        </div>
    </div>
</body>
</html>
