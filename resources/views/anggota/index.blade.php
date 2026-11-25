@extends('layouts.dashboard')

@section('title', 'Data Anggota Rayon')

@section('content')
<div class="card-header-clean">
    <div>
        <h2 class="card-title">Direktori Anggota Rayon</h2>
        <p class="card-subtitle">Daftar siswa anggota resmi Rayon Cibedug 2 di SMK Wikrama Bogor.</p>
    </div>

    @if (Auth::user()->isAdmin())
        <a href="{{ route('anggota.create') }}" class="btn btn-primary">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Tambah Anggota Baru</span>
        </a>
    @endif
</div>

<!-- Controls: View Toggle (Grid vs Table) & Search -->
<div class="directory-controls">
    <div class="view-toggle-group">
        <button type="button" id="btnToggleGrid" class="view-toggle-btn active" onclick="switchView('grid')">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
            <span>Grid Card</span>
        </button>
        <button type="button" id="btnToggleTable" class="view-toggle-btn" onclick="switchView('table')">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            <span>Tabel DataTables</span>
        </button>
    </div>

    <form method="GET" action="{{ route('anggota.index') }}" style="display: flex; gap: 8px;">
        <input 
            type="text" 
            name="q" 
            value="{{ request('q') }}" 
            class="form-input" 
            style="width: 260px; padding: 7px 12px; font-size: 13px;" 
            placeholder="Cari nama, NIS, kelas..."
        >
        <button type="submit" class="btn btn-secondary btn-sm">Cari</button>
        @if (request('q'))
            <a href="{{ route('anggota.index') }}" class="btn btn-outline btn-sm">Reset</a>
        @endif
    </form>
</div>

<!-- 1. GRID CARD VIEW (DEFAULT) -->
<div id="viewGridContainer">
    <div class="member-grid">
        @forelse ($anggotaList as $anggota)
            <div class="member-card">
                <div class="member-card-photo-wrapper">
                    @if ($anggota->foto_url)
                        <img src="{{ $anggota->foto_url }}" alt="{{ $anggota->user->nama ?? 'Siswa' }}" class="member-card-photo">
                    @else
                        <div class="member-card-initial">
                            {{ strtoupper(substr($anggota->user->nama ?? 'S', 0, 1)) }}
                        </div>
                    @endif
                </div>

                <div class="member-card-body">
                    <div>
                        <div class="member-card-name">{{ $anggota->user->nama ?? '-' }}</div>
                        <div class="member-card-nis">NIS: {{ $anggota->nis ?: '-' }}</div>

                        <div class="member-meta-row">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span>{{ $anggota->kelas ?: '-' }} &bull; {{ $anggota->jurusan ?: '-' }}</span>
                        </div>
                        <div class="member-meta-row">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span>{{ $anggota->user->email ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="member-card-footer">
                        <a href="{{ route('anggota.show', $anggota->id_anggota) }}" class="btn btn-secondary-sm" style="flex: 1; text-align: center;">
                            Lihat Profil
                        </a>

                        @if (Auth::user()->isAdmin())
                            <a href="{{ route('anggota.edit', $anggota->id_anggota) }}" class="btn btn-warning-sm" title="Ubah data">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </a>
                            <form action="{{ route('anggota.destroy', $anggota->id_anggota) }}" method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data anggota {{ $anggota->user->nama ?? '' }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger-sm" title="Hapus">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 40px 20px; color: var(--text-muted);">
                <p>Tidak ada data anggota siswa yang ditemukan.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- 2. TABLE VIEW (DATATABLES SERVER-SIDE / CLIENT-SIDE HYBRID) -->
<div id="viewTableContainer" style="display: none;">
    <div class="card">
        <div class="table-responsive">
            <table class="custom-table" id="tabelAnggota">
                <thead>
                    <tr>
                        <th style="width: 50px;">Foto</th>
                        <th>NIS/NISN</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Kompetensi Keahlian</th>
                        <th>No. HP</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($anggotaList as $anggota)
                        <tr>
                            <td>
                                @if ($anggota->foto_url)
                                    <img src="{{ $anggota->foto_url }}" alt="{{ $anggota->user->nama ?? '' }}" class="table-avatar">
                                @else
                                    <div class="table-avatar-placeholder">
                                        {{ strtoupper(substr($anggota->user->nama ?? 'S', 0, 1)) }}
                                    </div>
                                @endif
                            </td>
                            <td style="font-weight: 600; color: var(--primary);">{{ $anggota->nis ?: '-' }}</td>
                            <td style="font-weight: 600;">{{ $anggota->user->nama ?? '-' }}</td>
                            <td>{{ $anggota->kelas ?: '-' }}</td>
                            <td>{{ $anggota->jurusan ?: '-' }}</td>
                            <td>{{ $anggota->no_hp ?: '-' }}</td>
                            <td style="text-align: center; white-space: nowrap;">
                                <div class="table-action-group">
                                    <a href="{{ route('anggota.show', $anggota->id_anggota) }}" class="btn btn-secondary-sm btn-sm">Profil</a>
                                    @if (Auth::user()->isAdmin())
                                        <a href="{{ route('anggota.edit', $anggota->id_anggota) }}" class="btn btn-warning-sm btn-sm">Ubah</a>
                                        <form action="{{ route('anggota.destroy', $anggota->id_anggota) }}" method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus siswa {{ $anggota->user->nama ?? '' }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger-sm btn-sm">Hapus</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 24px;">
                                Tidak ada data anggota siswa yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function switchView(mode) {
    const gridContainer = document.getElementById('viewGridContainer');
    const tableContainer = document.getElementById('viewTableContainer');
    const btnGrid = document.getElementById('btnToggleGrid');
    const btnTable = document.getElementById('btnToggleTable');

    if (mode === 'grid') {
        gridContainer.style.display = 'block';
        tableContainer.style.display = 'none';
        btnGrid.classList.add('active');
        btnTable.classList.remove('active');
    } else {
        gridContainer.style.display = 'none';
        tableContainer.style.display = 'block';
        btnGrid.classList.remove('active');
        btnTable.classList.add('active');

        // Init DataTable if not already
        if (!$.fn.DataTable.isDataTable('#tabelAnggota')) {
            $('#tabelAnggota').DataTable({
                language: {
                    search: "Cari di tabel:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
                    paginate: { previous: "Sebelumnya", next: "Berikutnya" },
                    emptyTable: "Tidak ada data anggota"
                },
                pageLength: 10
            });
        }
    }
}
</script>
@endsection
