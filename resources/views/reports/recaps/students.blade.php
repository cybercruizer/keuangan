@extends('layouts.app')

@section('title', 'Rekap Pembayaran per Siswa')
@section('header_title', 'Rekapitulasi Pembayaran Siswa')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
        <h5 class="fw-bold mb-1">Rekap Pembayaran per Siswa</h5>
        <p class="text-muted small mb-0">Laporan tunggakan dan rekapitulasi kelunasan per peserta didik.</p>
    </div>
    <div>
        <a href="{{ route('reports.recaps.students', array_merge(request()->all(), ['print' => 1])) }}" target="_blank" class="btn btn-light">
            <i class="bi bi-printer me-1"></i> Cetak Rekap Lengkap
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card p-3 mb-3">
    <form method="GET" action="{{ route('reports.recaps.students') }}" class="row g-2 align-items-center">
        <div class="col-md-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Cari Nama / NIS Siswa..." value="{{ request('search') }}">
            </div>
        </div>

        <div class="col-md-3">
            <select name="class_room_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Rombel</option>
                @foreach($classRooms as $cr)
                    <option value="{{ $cr->id }}" {{ request('class_room_id') == $cr->id ? 'selected' : '' }}>
                        {{ $cr->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Kategori</option>
                <option value="reguler" {{ request('category') === 'reguler' ? 'selected' : '' }}>Reguler</option>
                <option value="beasiswa_50" {{ request('category') === 'beasiswa_50' ? 'selected' : '' }}>Beasiswa 50%</option>
                <option value="beasiswa_100" {{ request('category') === 'beasiswa_100' ? 'selected' : '' }}>Beasiswa 100%</option>
            </select>
        </div>

        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-pastel-primary w-50">Filter</button>
            <a href="{{ route('reports.recaps.students') }}" class="btn btn-sm btn-light w-50">Reset</a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>NIS</th>
                    <th>Nama Murid</th>
                    <th>Rombel & Jurusan</th>
                    <th>Kategori</th>
                    <th class="text-end">Wajib Bayar</th>
                    <th class="text-end">Sudah Dibayar</th>
                    <th class="text-end">Sisa Tunggakan</th>
                    <th class="text-center">Status Lunas</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $st)
                    <tr>
                        <td class="fw-bold font-monospace">{{ $st->nis }}</td>
                        <td>
                            <a href="{{ route('management.students.show', $st) }}" class="fw-semibold text-dark text-decoration-none">
                                {{ $st->name }}
                            </a>
                        </td>
                        <td>
                            <span>{{ $st->classRoom?->name ?: '-' }}</span>
                            <small class="d-block text-muted">{{ $st->classRoom?->major?->code }}</small>
                        </td>
                        <td>
                            <span class="badge {{ $st->category === 'beasiswa_100' ? 'badge-pastel-accent' : ($st->category === 'beasiswa_50' ? 'badge-pastel-warning' : 'badge-pastel-primary') }}">
                                {{ $st->category_label }}
                            </span>
                        </td>
                        <td class="text-end fw-semibold">
                            Rp {{ number_format($st->recap['total_billed'], 0, ',', '.') }}
                        </td>
                        <td class="text-end fw-semibold text-success">
                            Rp {{ number_format($st->recap['total_paid'], 0, ',', '.') }}
                        </td>
                        <td class="text-end fw-bold {{ $st->recap['remaining'] > 0 ? 'text-danger' : 'text-muted' }}">
                            Rp {{ number_format($st->recap['remaining'], 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            @if($st->recap['remaining'] <= 0 && $st->recap['total_billed'] > 0)
                                <span class="badge badge-pastel-success">100% Lunas</span>
                            @elseif($st->recap['total_paid'] > 0)
                                <span class="badge badge-pastel-warning">{{ $st->recap['percentage'] }}%</span>
                            @elseif($st->recap['total_billed'] > 0)
                                <span class="badge badge-pastel-danger">0%</span>
                            @else
                                <span class="badge bg-light text-muted">Belum Ada Tagihan</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('management.students.show', $st) }}" class="btn btn-sm btn-light py-1 px-2">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">Tidak ada data murid yang cocok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($students->hasPages())
        <div class="card-footer d-flex justify-content-end py-2">
            {{ $students->links() }}
        </div>
    @endif
</div>
@endsection
