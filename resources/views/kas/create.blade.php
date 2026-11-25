@extends('layouts.dashboard')

@section('title', 'Catat Transaksi Kas - Cibedug 2 Management')

@section('content')
<div class="page-header">
    <div>
        <h1>Catat Transaksi Kas</h1>
        <p>Form pencatatan penerimaan atau pengeluaran kas kelas.</p>
    </div>
</div>

<div class="card" style="max-width: 600px;">
    <div class="card-header">
        <h2>Informasi Transaksi</h2>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('kas.store') }}">
            @csrf

            <div class="form-group">
                <label for="tanggal" class="form-label">Tanggal Transaksi *</label>
                <input type="date" id="tanggal" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" class="form-control" required>
                @error('tanggal') <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="jenis" class="form-label">Jenis Transaksi *</label>
                <select id="jenis" name="jenis" class="form-control" required>
                    <option value="">-- Pilih Jenis Transaksi --</option>
                    <option value="Pemasukan" {{ old('jenis') === 'Pemasukan' ? 'selected' : '' }}>Pemasukan (Uang Masuk / Kas Siswa)</option>
                    <option value="Pengeluaran" {{ old('jenis') === 'Pengeluaran' ? 'selected' : '' }}>Pengeluaran (Biaya / Belanja Kelas)</option>
                </select>
                @error('jenis') <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="nominal" class="form-label">Nominal (Rp) *</label>
                <input type="number" id="nominal" name="nominal" value="{{ old('nominal') }}" class="form-control" placeholder="Contoh: 50000" min="1" step="any" required>
                @error('nominal') <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="keterangan" class="form-label">Keterangan / Deskripsi Transaksi *</label>
                <textarea id="keterangan" name="keterangan" rows="3" class="form-control" placeholder="Jelaskan sumber pemasukan atau tujuan pengeluaran..." required>{{ old('keterangan') }}</textarea>
                @error('keterangan') <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
            </div>

            <div style="display: flex; gap: 12px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Simpan Transaksi</button>
                <a href="{{ route('kas.index') }}" class="btn btn-outline" style="text-align: center;">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
