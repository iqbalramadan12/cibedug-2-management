@extends('layouts.dashboard')

@section('title', 'Manajemen Keuangan Kas - Cibedug 2 Management')

@section('content')
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
    <div>
        <h1>Manajemen Keuangan Kas</h1>
        <p>Pengelolaan pembukuan pemasukan dan pengeluaran kas Rayon Cibedug 2 SMK Wikrama Bogor secara transparan dan akuntabel.</p>
    </div>

    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="{{ route('kas.export.excel', request()->all()) }}" class="btn btn-outline btn-sm" style="background: #10b981; color: white; border-color: #10b981;">
            <svg style="width: 14px; height: 14px; display: inline-block; vertical-align: middle; margin-right: 4px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export Excel
        </a>
        <a href="{{ route('kas.export.pdf', request()->all()) }}" class="btn btn-outline btn-sm" style="background: #ef4444; color: white; border-color: #ef4444;">
            <svg style="width: 14px; height: 14px; display: inline-block; vertical-align: middle; margin-right: 4px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            Export PDF
        </a>
        <a href="{{ route('kas.create') }}" class="btn btn-primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Catat Transaksi Kas</span>
        </a>
    </div>
</div>

<!-- Ringkasan Keuangan (Rubrik Penilaian: Ringkasan Keuangan) -->
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
            <div style="font-size: 13px; color: #64748b; font-weight: 500;">Saldo Akhir Kas</div>
            <div style="font-size: 20px; font-weight: 700; color: {{ $saldoAkhir >= 0 ? '#002b49' : '#ef4444' }};">
                Rp {{ number_format($saldoAkhir, 0, ',', '.') }}
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header" style="display: flex; flex-direction: column; gap: 14px; align-items: stretch;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <h2>Buku Kas & Riwayat Transaksi</h2>
            <span style="font-size: 13px; color: #64748b;">Menampilkan <strong>{{ $kasList->total() }}</strong> catatan transaksi</span>
        </div>

        <!-- Filter, Pencarian, & Pengurutan (Rubrik Search, Filter, Sort) -->
        <form method="GET" action="{{ route('kas.index') }}" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center; background-color: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
            <!-- Search -->
            <input 
                type="text" 
                name="q" 
                value="{{ request('q') }}" 
                class="form-control" 
                style="width: 170px; height: 34px; font-size: 12.5px;" 
                placeholder="Cari transaksi..."
            >

            <!-- Filter Jenis -->
            <select name="jenis" class="form-control" style="width: 140px; height: 34px; font-size: 12.5px;">
                <option value="all">Semua Jenis</option>
                <option value="Pemasukan" {{ request('jenis') === 'Pemasukan' ? 'selected' : '' }}>Pemasukan</option>
                <option value="Pengeluaran" {{ request('jenis') === 'Pengeluaran' ? 'selected' : '' }}>Pengeluaran</option>
            </select>

            <!-- Rentang Tanggal -->
            <div style="display: flex; align-items: center; gap: 6px;">
                <input 
                    type="date" 
                    name="dari_tanggal" 
                    value="{{ request('dari_tanggal') }}" 
                    class="form-control" 
                    style="width: 140px; height: 34px; font-size: 12.5px;"
                    title="Dari Tanggal"
                >
                <span style="font-size: 12px; color: #64748b;">s/d</span>
                <input 
                    type="date" 
                    name="sampai_tanggal" 
                    value="{{ request('sampai_tanggal') }}" 
                    class="form-control" 
                    style="width: 140px; height: 34px; font-size: 12.5px;"
                    title="Sampai Tanggal"
                >
            </div>

            <!-- Sorting -->
            <select name="sort_by" class="form-control" style="width: 130px; height: 34px; font-size: 12.5px;">
                <option value="tanggal" {{ request('sort_by', 'tanggal') === 'tanggal' ? 'selected' : '' }}>Urut Tanggal</option>
                <option value="nominal" {{ request('sort_by') === 'nominal' ? 'selected' : '' }}>Urut Nominal</option>
                <option value="user" {{ request('sort_by') === 'user' ? 'selected' : '' }}>Urut Pencatat</option>
            </select>

            <select name="sort_order" class="form-control" style="width: 110px; height: 34px; font-size: 12.5px;">
                <option value="desc" {{ request('sort_order', 'desc') === 'desc' ? 'selected' : '' }}>Terbaru</option>
                <option value="asc" {{ request('sort_order') === 'asc' ? 'selected' : '' }}>Terlama</option>
            </select>

            <button type="submit" class="btn btn-outline btn-sm">Filter</button>
            <a href="{{ route('kas.index') }}" class="btn btn-outline btn-sm" style="color: #64748b;">Reset</a>
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
                    <th style="text-align: center; width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($kasList as $index => $kas)
                    <tr>
                        <td style="color: #64748b;">{{ $kasList->firstItem() + $index }}</td>
                        <td>{{ $kas->tanggal ? (is_string($kas->tanggal) ? date('d M Y', strtotime($kas->tanggal)) : $kas->tanggal->format('d M Y')) : '-' }}</td>
                        <td>
                            <strong style="color: #0f172a;">{{ $kas->user->nama ?? 'Sistem' }}</strong>
                            <div style="font-size: 11px; color: #64748b;">{{ ucfirst($kas->user->role ?? '-') }}</div>
                        </td>
                        <td>
                            <span class="badge-status" style="background: {{ $kas->jenis === 'Pemasukan' ? '#dcfce7' : '#fee2e2' }}; color: {{ $kas->jenis === 'Pemasukan' ? '#166534' : '#991b1b' }};">
                                {{ $kas->jenis }}
                            </span>
                        </td>
                        <td style="text-align: right; font-weight: 600; color: {{ $kas->jenis === 'Pemasukan' ? '#166534' : '#991b1b' }};">
                            {{ $kas->jenis === 'Pemasukan' ? '+' : '-' }} Rp {{ number_format($kas->nominal, 0, ',', '.') }}
                        </td>
                        <td style="color: #334155;">{{ $kas->keterangan }}</td>
                        <td style="text-align: center; white-space: nowrap;">
                            <a href="{{ route('kas.edit', $kas->id_kas) }}" class="btn btn-warning-sm btn-sm">Ubah</a>
                            <form action="{{ route('kas.destroy', $kas->id_kas) }}" method="POST" style="display: inline;" onsubmit="return confirm('Hapus catatan transaksi ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger-sm btn-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: #94a3b8; padding: 32px;">
                            Belum ada transaksi kas yang dicatat atau sesuai kriteria pencarian.
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
