@extends('layouts.dashboard')

@section('title', 'Transparansi Keuangan Kas - Cibedug 2 Management')

@section('content')
<div class="page-header">
    <div>
        <h1>Transparansi Keuangan Kas Kelas</h1>
        <p>Laporan pembukuan kas kelas XI RPL 2 terbuka bagi seluruh siswa.</p>
    </div>
</div>

<!-- Ringkasan Keuangan Siswa -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="stat-card" style="background: #ffffff; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 16px;">
        <div style="width: 48px; height: 48px; border-radius: 10px; background: rgba(16, 185, 129, 0.12); color: #10b981; display: flex; align-items: center; justify-content: center;">
            <svg style="width: 26px; height: 26px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
        </div>
        <div>
            <div style="font-size: 13px; color: #64748b; font-weight: 500;">Total Pemasukan</div>
            <div style="font-size: 20px; font-weight: 700; color: #10b981;">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="stat-card" style="background: #ffffff; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 16px;">
        <div style="width: 48px; height: 48px; border-radius: 10px; background: rgba(239, 68, 68, 0.12); color: #ef4444; display: flex; align-items: center; justify-content: center;">
            <svg style="width: 26px; height: 26px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
        </div>
        <div>
            <div style="font-size: 13px; color: #64748b; font-weight: 500;">Total Pengeluaran</div>
            <div style="font-size: 20px; font-weight: 700; color: #ef4444;">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="stat-card" style="background: #ffffff; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 16px;">
        <div style="width: 48px; height: 48px; border-radius: 10px; background: rgba(0, 43, 73, 0.1); color: #002b49; display: flex; align-items: center; justify-content: center;">
            <svg style="width: 26px; height: 26px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div style="font-size: 13px; color: #64748b; font-weight: 500;">Saldo Kas Saat Ini</div>
            <div style="font-size: 20px; font-weight: 700; color: {{ $saldoAkhir >= 0 ? '#002b49' : '#ef4444' }};">
                Rp {{ number_format($saldoAkhir, 0, ',', '.') }}
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <h2>Buku Kas Kelas (Read-Only)</h2>

        <form method="GET" action="{{ route('kas.index') }}" style="display: flex; gap: 8px;">
            <select name="jenis" class="form-control" style="width: 140px; height: 34px; font-size: 12.5px;" onchange="this.form.submit()">
                <option value="all">Semua Jenis</option>
                <option value="Pemasukan" {{ request('jenis') === 'Pemasukan' ? 'selected' : '' }}>Pemasukan</option>
                <option value="Pengeluaran" {{ request('jenis') === 'Pengeluaran' ? 'selected' : '' }}>Pengeluaran</option>
            </select>
        </form>
    </div>

    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Tanggal</th>
                    <th>Pencatat</th>
                    <th>Jenis</th>
                    <th style="text-align: right;">Nominal</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($kasList as $index => $kas)
                    <tr>
                        <td style="color: #64748b;">{{ $kasList->firstItem() + $index }}</td>
                        <td>{{ $kas->tanggal ? (is_string($kas->tanggal) ? date('d M Y', strtotime($kas->tanggal)) : $kas->tanggal->format('d M Y')) : '-' }}</td>
                        <td>{{ $kas->user->nama ?? 'Bendahara / Pengurus' }}</td>
                        <td>
                            <span class="badge-status" style="background: {{ $kas->jenis === 'Pemasukan' ? '#dcfce7' : '#fee2e2' }}; color: {{ $kas->jenis === 'Pemasukan' ? '#166534' : '#991b1b' }};">
                                {{ $kas->jenis }}
                            </span>
                        </td>
                        <td style="text-align: right; font-weight: 600; color: {{ $kas->jenis === 'Pemasukan' ? '#166534' : '#991b1b' }};">
                            {{ $kas->jenis === 'Pemasukan' ? '+' : '-' }} Rp {{ number_format($kas->nominal, 0, ',', '.') }}
                        </td>
                        <td style="color: #334155;">{{ $kas->keterangan }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #94a3b8; padding: 32px;">
                            Belum ada transaksi kas kelas yang dicatat.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($kasList->hasPages())
        <div style="padding: 16px 20px; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end;">
            {{ $kasList->links() }}
        </div>
    @endif
</div>
@endsection
