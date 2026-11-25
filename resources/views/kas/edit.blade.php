@extends('layouts.dashboard')

@section('title', 'Ubah Transaksi Kas - Cibedug 2 Management')

@section('content')
<div class="page-header">
    <div>
        <h1>Ubah Transaksi Kas</h1>
        <p>Perbarui catatan pembukuan pemasukan atau pengeluaran kas.</p>
    </div>
</div>

<div class="card" style="max-width: 600px;">
    <div class="card-header">
        <h2>Edit Catatan Kas #{{ $kas->id_kas }}</h2>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('kas.update', $kas->id_kas) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="tanggal" class="form-label">Tanggal Transaksi *</label>
                <input 
                    type="date" 
                    id="tanggal" 
                    name="tanggal" 
                    value="{{ old('tanggal', is_string($kas->tanggal) ? $kas->tanggal : $kas->tanggal->format('Y-m-d')) }}" 
                    class="form-control" 
                    required
                >
                @error('tanggal') <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="jenis" class="form-label">Jenis Transaksi *</label>
                <select id="jenis" name="jenis" class="form-control" required>
                    <option value="Pemasukan" {{ old('jenis', $kas->jenis) === 'Pemasukan' ? 'selected' : '' }}>Pemasukan</option>
                    <option value="Pengeluaran" {{ old('jenis', $kas->jenis) === 'Pengeluaran' ? 'selected' : '' }}>Pengeluaran</option>
                </select>
                @error('jenis') <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="nominal" class="form-label">Nominal (Rp) *</label>
                <input 
                    type="number" 
                    id="nominal" 
                    name="nominal" 
                    value="{{ old('nominal', (float) $kas->nominal) }}" 
                    class="form-control" 
                    min="1" 
                    step="any" 
                    required
                >
                @error('nominal') <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="keterangan" class="form-label">Keterangan / Deskripsi Transaksi *</label>
                <textarea id="keterangan" name="keterangan" rows="3" class="form-control" required>{{ old('keterangan', $kas->keterangan) }}</textarea>
                @error('keterangan') <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
            </div>

            <div style="display: flex; gap: 12px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Simpan Perubahan</button>
                <a href="{{ route('kas.index') }}" class="btn btn-outline" style="text-align: center;">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
