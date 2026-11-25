@extends('layouts.dashboard')

@section('title', 'Laporan & Rekapitulasi Terpadu - Cibedug 2 Management')

@section('content')
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
    <div>
        <h1>Laporan & Rekapitulasi Terpadu</h1>
        <p>Pusat analisa data presensi, penugasan piket kebersihan, dan keuangan kas Rayon Cibedug 2 SMK Wikrama Bogor.</p>
    </div>

    <!-- Shortcut Export Cepat -->
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="{{ route('absensi.export.pdf') }}" class="btn btn-outline btn-sm" style="border-color: #ef4444; color: #ef4444;">
            PDF Absensi
        </a>
        <a href="{{ route('piket.export.pdf') }}" class="btn btn-outline btn-sm" style="border-color: #3b82f6; color: #3b82f6;">
            PDF Piket
        </a>
        <a href="{{ route('kas.export.pdf') }}" class="btn btn-outline btn-sm" style="border-color: #10b981; color: #10b981;">
            PDF Kas
        </a>
    </div>
</div>

<!-- Filter Global Periode Laporan -->
<div class="card" style="margin-bottom: 24px;">
    <div class="card-body" style="padding: 16px;">
        <form method="GET" action="{{ route('laporan.index') }}" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
            <input type="hidden" name="tab" value="{{ $tab }}">

            <div style="display: flex; align-items: center; gap: 6px;">
                <label style="font-size: 12px; color: #64748b; font-weight: 500;">Rentang:</label>
                <input 
                    type="date" 
                    name="tanggal_awal" 
                    value="{{ $filters['tanggalAwal'] }}" 
                    class="form-control" 
                    style="width: 135px; height: 34px; font-size: 12.5px;"
                    title="Tanggal Awal"
                >
                <span style="font-size: 12px; color: #64748b;">s/d</span>
                <input 
                    type="date" 
                    name="tanggal_akhir" 
                    value="{{ $filters['tanggalAkhir'] }}" 
                    class="form-control" 
                    style="width: 135px; height: 34px; font-size: 12.5px;"
                    title="Tanggal Akhir"
                >
            </div>

            <div style="display: flex; align-items: center; gap: 6px;">
                <label style="font-size: 12px; color: #64748b; font-weight: 500;">Bulan:</label>
                <select name="bulan" class="form-control" style="width: 120px; height: 34px; font-size: 12.5px;">
                    <option value="">Semua Bulan</option>
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ sprintf('%02d', $m) }}" {{ $filters['bulan'] == sprintf('%02d', $m) ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                        </option>
                    @endfor
                </select>
            </div>

            <div style="display: flex; align-items: center; gap: 6px;">
                <label style="font-size: 12px; color: #64748b; font-weight: 500;">Tahun:</label>
                <select name="tahun" class="form-control" style="width: 95px; height: 34px; font-size: 12.5px;">
                    @for ($y = date('Y'); $y >= date('Y') - 2; $y--)
                        <option value="{{ $y }}" {{ $filters['tahun'] == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <button type="submit" class="btn btn-outline btn-sm">Terapkan Filter</button>
            <a href="{{ route('laporan.index', ['tab' => $tab]) }}" class="btn btn-outline btn-sm" style="color: #64748b;">Reset</a>
        </form>
    </div>
</div>

<!-- Tab Navigasi -->
<div style="display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">
    <a href="{{ route('laporan.index', array_merge(request()->except('tab'), ['tab' => 'overview'])) }}" 
       style="padding: 8px 16px; border-radius: 6px; font-weight: 600; font-size: 13.5px; text-decoration: none; {{ $tab === 'overview' ? 'background: #002b49; color: #ffffff;' : 'color: #64748b; background: #f8fafc;' }}">
        Ringkasan Umum
    </a>
    <a href="{{ route('laporan.index', array_merge(request()->except('tab'), ['tab' => 'absensi'])) }}" 
       style="padding: 8px 16px; border-radius: 6px; font-weight: 600; font-size: 13.5px; text-decoration: none; {{ $tab === 'absensi' ? 'background: #002b49; color: #ffffff;' : 'color: #64748b; background: #f8fafc;' }}">
        Rekap Absensi
    </a>
    <a href="{{ route('laporan.index', array_merge(request()->except('tab'), ['tab' => 'piket'])) }}" 
       style="padding: 8px 16px; border-radius: 6px; font-weight: 600; font-size: 13.5px; text-decoration: none; {{ $tab === 'piket' ? 'background: #002b49; color: #ffffff;' : 'color: #64748b; background: #f8fafc;' }}">
        Rekap Piket
    </a>
    <a href="{{ route('laporan.index', array_merge(request()->except('tab'), ['tab' => 'kas'])) }}" 
       style="padding: 8px 16px; border-radius: 6px; font-weight: 600; font-size: 13.5px; text-decoration: none; {{ $tab === 'kas' ? 'background: #002b49; color: #ffffff;' : 'color: #64748b; background: #f8fafc;' }}">
        Rekap Keuangan Kas
    </a>
</div>

@if ($tab === 'overview')
    <!-- Tampilan Ringkasan Umum -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
        <!-- Card Absensi -->
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h2>Statistik Kehadiran</h2>
                <span class="badge-status" style="background: #dcfce7; color: #166534; font-weight: 700;">
                    {{ $persentaseHadir }}% Hadir
                </span>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                    <div style="background: #f8fafc; padding: 10px; border-radius: 8px;">
                        <span style="font-size: 11px; color: #64748b;">Hadir</span>
                        <div style="font-size: 18px; font-weight: 700; color: #10b981;">{{ $totalHadir }}</div>
                    </div>
                    <div style="background: #f8fafc; padding: 10px; border-radius: 8px;">
                        <span style="font-size: 11px; color: #64748b;">Izin</span>
                        <div style="font-size: 18px; font-weight: 700; color: #3b82f6;">{{ $totalIzin }}</div>
                    </div>
                    <div style="background: #f8fafc; padding: 10px; border-radius: 8px;">
                        <span style="font-size: 11px; color: #64748b;">Sakit</span>
                        <div style="font-size: 18px; font-weight: 700; color: #f59e0b;">{{ $totalSakit }}</div>
                    </div>
                    <div style="background: #f8fafc; padding: 10px; border-radius: 8px;">
                        <span style="font-size: 11px; color: #64748b;">Alpa</span>
                        <div style="font-size: 18px; font-weight: 700; color: #ef4444;">{{ $totalAlpa }}</div>
                    </div>
                </div>
                <div style="font-size: 12px; color: #64748b; text-align: center;">
                    Total Rekapitulasi: <strong>{{ $totalAbsensi }}</strong> catatan
                </div>
            </div>
        </div>

        <!-- Card Piket -->
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h2>Statistik Piket Kelas</h2>
                <span class="badge-status" style="background: #e0e7ff; color: #3730a3; font-weight: 700;">
                    {{ $persentasePiket }}% Selesai
                </span>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                    <div style="background: #f8fafc; padding: 10px; border-radius: 8px;">
                        <span style="font-size: 11px; color: #64748b;">Tugas Selesai</span>
                        <div style="font-size: 18px; font-weight: 700; color: #10b981;">{{ $piketSelesai }}</div>
                    </div>
                    <div style="background: #f8fafc; padding: 10px; border-radius: 8px;">
                        <span style="font-size: 11px; color: #64748b;">Belum Dikerjakan</span>
                        <div style="font-size: 18px; font-weight: 700; color: #f59e0b;">{{ $piketBelum }}</div>
                    </div>
                </div>
                <div style="font-size: 12px; color: #64748b; text-align: center;">
                    Total Penugasan Piket: <strong>{{ $totalPiket }}</strong> jadwal
                </div>
            </div>
        </div>

        <!-- Card Kas -->
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h2>Statistik Kas Keuangan</h2>
                <span class="badge-status" style="background: #f1f5f9; color: #002b49; font-weight: 700;">
                    Saldo Akhir
                </span>
            </div>
            <div class="card-body">
                <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 16px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="font-size: 13px; color: #64748b;">Pemasukan:</span>
                        <strong style="color: #10b981;">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="font-size: 13px; color: #64748b;">Pengeluaran:</span>
                        <strong style="color: #ef4444;">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-top: 1px solid #e2e8f0; padding-top: 8px;">
                        <span style="font-size: 14px; font-weight: 600;">Saldo Bersih:</span>
                        <strong style="font-size: 15px; color: {{ $saldoAkhir >= 0 ? '#002b49' : '#ef4444' }};">
                            Rp {{ number_format($saldoAkhir, 0, ',', '.') }}
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
@elseif ($tab === 'absensi')
    <!-- Tampilan Rekap Absensi per Siswa -->
    <div class="card">
        <div class="card-header">
            <h2>Rekapitulasi Kehadiran per Siswa</h2>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th style="text-align: center;">Hadir</th>
                        <th style="text-align: center;">Izin</th>
                        <th style="text-align: center;">Sakit</th>
                        <th style="text-align: center;">Alpa</th>
                        <th style="text-align: center;">Total Pertemuan</th>
                        <th style="text-align: center;">Persentase</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rekapSiswa as $r)
                        <tr>
                            <td>{{ $r['nis'] ?: '-' }}</td>
                            <td><strong>{{ $r['nama'] }}</strong></td>
                            <td style="text-align: center; color: #10b981; font-weight: 600;">{{ $r['hadir'] }}</td>
                            <td style="text-align: center; color: #3b82f6;">{{ $r['izin'] }}</td>
                            <td style="text-align: center; color: #f59e0b;">{{ $r['sakit'] }}</td>
                            <td style="text-align: center; color: #ef4444; font-weight: 600;">{{ $r['alpa'] }}</td>
                            <td style="text-align: center;">{{ $r['total'] }}</td>
                            <td style="text-align: center;">
                                <span class="badge-status" style="background: {{ $r['persen'] >= 80 ? '#dcfce7' : '#fee2e2' }}; color: {{ $r['persen'] >= 80 ? '#166534' : '#991b1b' }};">
                                    {{ $r['persen'] }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: #94a3b8; padding: 24px;">Tidak ada data siswa.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@elseif ($tab === 'piket')
    <!-- Tampilan Rekap Piket per Siswa -->
    <div class="card">
        <div class="card-header">
            <h2>Rekapitulasi Kepatuhan Piket per Siswa</h2>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th style="text-align: center;">Total Jadwal</th>
                        <th style="text-align: center;">Selesai</th>
                        <th style="text-align: center;">Belum</th>
                        <th style="text-align: center;">Tingkat Kepatuhan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rekapSiswa as $r)
                        <tr>
                            <td>{{ $r['nis'] ?: '-' }}</td>
                            <td><strong>{{ $r['nama'] }}</strong></td>
                            <td style="text-align: center;">{{ $r['total'] }}</td>
                            <td style="text-align: center; color: #10b981; font-weight: 600;">{{ $r['selesai'] }}</td>
                            <td style="text-align: center; color: #f59e0b;">{{ $r['belum'] }}</td>
                            <td style="text-align: center;">
                                <span class="badge-status" style="background: {{ $r['persen'] >= 75 ? '#e0e7ff' : '#fef3c7' }}; color: {{ $r['persen'] >= 75 ? '#3730a3' : '#92400e' }};">
                                    {{ $r['persen'] }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: #94a3b8; padding: 24px;">Tidak ada data jadwal piket.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@elseif ($tab === 'kas')
    <!-- Tampilan Rekap Transaksi Kas -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2>Rincian Transaksi Keuangan Kas Periode Terpilih</h2>
            <div style="font-size: 13px;">
                Saldo: <strong style="color: {{ $saldoAkhir >= 0 ? '#10b981' : '#ef4444' }};">Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</strong>
            </div>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Pencatat</th>
                        <th>Jenis</th>
                        <th style="text-align: right;">Nominal</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($detailList as $k)
                        <tr>
                            <td>{{ $k->tanggal ? $k->tanggal->format('d/m/Y') : '-' }}</td>
                            <td>{{ $k->user->nama ?? '-' }}</td>
                            <td>
                                <span class="badge-status" style="background: {{ $k->jenis === 'Pemasukan' ? '#dcfce7' : '#fee2e2' }}; color: {{ $k->jenis === 'Pemasukan' ? '#166534' : '#991b1b' }};">
                                    {{ $k->jenis }}
                                </span>
                            </td>
                            <td style="text-align: right; font-weight: 600; color: {{ $k->jenis === 'Pemasukan' ? '#166534' : '#991b1b' }};">
                                {{ $k->jenis === 'Pemasukan' ? '+' : '-' }} Rp {{ number_format($k->nominal, 0, ',', '.') }}
                            </td>
                            <td>{{ $k->keterangan }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: #94a3b8; padding: 24px;">Tidak ada transaksi kas pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($detailList->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end;">
                {{ $detailList->links() }}
            </div>
        @endif
    </div>
@endif

@endsection
