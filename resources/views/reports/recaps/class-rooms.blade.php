@extends('layouts.app')

@section('title', 'Rekap Pembayaran per Rombel')
@section('header_title', 'Rekapitulasi Pembayaran per Rombel')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
        <h5 class="fw-bold mb-1">Rekap Pembayaran per Rombel</h5>
        <p class="text-muted small mb-0">Laporan capaian penerimaan kas dan persentase kelunasan per kelas / rombongan belajar.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.recaps.class-rooms', array_merge(request()->all(), ['export' => 1])) }}" class="btn btn-light">
            <i class="bi bi-file-earmark-arrow-down me-1"></i> Export CSV
        </a>
        <a href="{{ route('reports.recaps.class-rooms', array_merge(request()->all(), ['print' => 1])) }}" target="_blank" class="btn btn-light">
            <i class="bi bi-printer me-1"></i> Cetak Rekap Rombel
        </a>
    </div>
</div>

<!-- Financial Summary Widgets -->
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--pastel-primary-light); color: var(--pastel-primary);">
                <i class="bi bi-journal-text"></i>
            </div>
            <div>
                <small class="text-muted d-block" style="font-size: 0.75rem;">Total Tagihan Terbit</small>
                <h5 class="fw-bold mb-0 text-dark">Rp {{ number_format($totalBilledAll, 0, ',', '.') }}</h5>
                <small class="text-muted" style="font-size: 0.7rem;">Seluruh rombel</small>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--pastel-success-light); color: var(--pastel-success);">
                <i class="bi bi-check-circle"></i>
            </div>
            <div>
                <small class="text-muted d-block" style="font-size: 0.75rem;">Total Telah Dibayar</small>
                <h5 class="fw-bold mb-0 text-success">Rp {{ number_format($totalPaidAll, 0, ',', '.') }}</h5>
                <small class="text-muted" style="font-size: 0.7rem;">Capaian {{ $overallPercentage }}%</small>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--pastel-danger-light); color: var(--pastel-danger);">
                <i class="bi bi-hourglass-bottom"></i>
            </div>
            <div>
                <small class="text-muted d-block" style="font-size: 0.75rem;">Total Sisa Tunggakan</small>
                <h5 class="fw-bold mb-0 text-danger">Rp {{ number_format($totalRemainingAll, 0, ',', '.') }}</h5>
                <small class="text-muted" style="font-size: 0.7rem;">Belum tertagih</small>
            </div>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card p-3 mb-3">
    <form method="GET" action="{{ route('reports.recaps.class-rooms') }}" class="row g-2 align-items-center">
        <div class="col-md-4">
            <label class="form-label small mb-1 fw-semibold">Pilih Tahun Ajaran:</label>
            <select name="academic_year_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Tahun Ajaran</option>
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}" {{ $selectedYearId == $year->id ? 'selected' : '' }}>
                        {{ $year->name }} {{ $year->is_active ? '★ Aktif' : '' }}
                    </option>
                @endforeach
            </select>
        </div>
    </form>
</div>

<!-- Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th style="width: 70px;">Tingkat</th>
                    <th>Nama Rombel</th>
                    <th>Jurusan</th>
                    <th>Jumlah Murid</th>
                    <th class="text-end">Total Tagihan</th>
                    <th class="text-end">Total Terbayar</th>
                    <th class="text-end">Sisa Tunggakan</th>
                    <th style="width: 160px;" class="text-center">Progress Kelunasan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recapData as $row)
                    <tr>
                        <td><span class="badge badge-pastel-primary">{{ $row['class_room']->level }}</span></td>
                        <td class="fw-bold">{{ $row['class_room']->name }}</td>
                        <td>{{ $row['class_room']->major?->name ?: '-' }}</td>
                        <td>
                            <span class="badge bg-light text-dark">{{ $row['students_count'] }} Murid</span>
                        </td>
                        <td class="text-end fw-semibold">
                            Rp {{ number_format($row['total_billed'], 0, ',', '.') }}
                        </td>
                        <td class="text-end fw-semibold text-success">
                            Rp {{ number_format($row['total_paid'], 0, ',', '.') }}
                        </td>
                        <td class="text-end fw-bold {{ $row['total_remaining'] > 0 ? 'text-danger' : 'text-muted' }}">
                            Rp {{ number_format($row['total_remaining'], 0, ',', '.') }}
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 8px;">
                                    <div class="progress-bar"
                                         style="width: {{ $row['percentage'] }}%; background-color: {{ $row['percentage'] >= 80 ? 'var(--pastel-success)' : ($row['percentage'] >= 50 ? 'var(--pastel-accent)' : 'var(--pastel-warning)') }};">
                                    </div>
                                </div>
                                <span class="small fw-semibold" style="width: 45px; text-align: right;">{{ $row['percentage'] }}%</span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Belum ada data rombel.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="fw-bold" style="background-color: #f1f5f9;">
                    <td colspan="4" class="text-end">TOTAL KESELURUHAN :</td>
                    <td class="text-end">Rp {{ number_format($totalBilledAll, 0, ',', '.') }}</td>
                    <td class="text-end text-success">Rp {{ number_format($totalPaidAll, 0, ',', '.') }}</td>
                    <td class="text-end text-danger">Rp {{ number_format($totalRemainingAll, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $overallPercentage }}% Lunas</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
