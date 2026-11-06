@extends('layouts.app')

@section('title', 'Rekap Pembayaran per Tagihan')
@section('header_title', 'Rekapitulasi Pembayaran per Jenis Tagihan')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
        <h5 class="fw-bold mb-1">Rekap Pembayaran per Jenis Tagihan</h5>
        <p class="text-muted small mb-0">Laporan capaian penerimaan kas, diskon beasiswa, dan kelunasan berdasarkan jenis tagihan.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.recaps.bill-types', array_merge(request()->all(), ['export' => 1])) }}" class="btn btn-light">
            <i class="bi bi-file-earmark-arrow-down me-1"></i> Export CSV
        </a>
        <a href="{{ route('reports.recaps.bill-types', array_merge(request()->all(), ['print' => 1])) }}" target="_blank" class="btn btn-light">
            <i class="bi bi-printer me-1"></i> Cetak Rekap Tagihan
        </a>
    </div>
</div>

<!-- Financial Summary Widgets -->
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--pastel-primary-light); color: var(--pastel-primary);">
                <i class="bi bi-tags"></i>
            </div>
            <div>
                <small class="text-muted d-block" style="font-size: 0.75rem;">Total Tagihan Diterbitkan</small>
                <h5 class="fw-bold mb-0 text-dark">Rp {{ number_format($totalBilledAll, 0, ',', '.') }}</h5>
                <small class="text-muted" style="font-size: 0.7rem;">Semua jenis biaya</small>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--pastel-success-light); color: var(--pastel-success);">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div>
                <small class="text-muted d-block" style="font-size: 0.75rem;">Total Kas Terbayar</small>
                <h5 class="fw-bold mb-0 text-success">Rp {{ number_format($totalPaidAll, 0, ',', '.') }}</h5>
                <small class="text-muted" style="font-size: 0.7rem;">Capaian {{ $overallPercentage }}%</small>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--pastel-danger-light); color: var(--pastel-danger);">
                <i class="bi bi-clock-history"></i>
            </div>
            <div>
                <small class="text-muted d-block" style="font-size: 0.75rem;">Sisa Belum Tertagih</small>
                <h5 class="fw-bold mb-0 text-danger">Rp {{ number_format($totalRemainingAll, 0, ',', '.') }}</h5>
                <small class="text-muted" style="font-size: 0.7rem;">Tertunggak</small>
            </div>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card p-3 mb-3">
    <form method="GET" action="{{ route('reports.recaps.bill-types') }}" class="row g-2 align-items-center">
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
                    <th>Nama Jenis Tagihan</th>
                    <th>Tipe Pembayaran</th>
                    <th>Tarif Satuan (Rp)</th>
                    <th class="text-center">Tagihan Terbit</th>
                    <th class="text-center">Lunas</th>
                    <th class="text-end">Total Nominal</th>
                    <th class="text-end">Total Terbayar</th>
                    <th class="text-end">Sisa Tunggakan</th>
                    <th style="width: 160px;" class="text-center">Progress Kelunasan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recapData as $row)
                    @php
                        $bt = $row['bill_type'];
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-bold text-dark">{{ $bt->name }}</div>
                            <small class="text-muted">{{ $bt->description ?: '-' }}</small>
                        </td>
                        <td>
                            @if($bt->type === 'spp')
                                <span class="badge badge-pastel-primary"><i class="bi bi-calendar-month me-1"></i> SPP</span>
                            @elseif($bt->type === 'custom')
                                <span class="badge badge-pastel-accent"><i class="bi bi-wallet2 me-1"></i> Custom</span>
                            @else
                                <span class="badge badge-pastel-warning"><i class="bi bi-piggy-bank me-1"></i> Tabungan</span>
                            @endif
                        </td>
                        <td class="fw-semibold">
                            Rp {{ number_format($bt->amount, 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark">{{ $row['count_bills'] }} invoice</span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-pastel-success">{{ $row['count_paid'] }} lunas</span>
                        </td>
                        <td class="text-end fw-semibold">
                            Rp {{ number_format($row['total_billed'], 0, ',', '.') }}
                            @if($row['total_discount'] > 0)
                                <small class="text-muted d-block" style="font-size: 0.7rem;">
                                    Diskon: Rp {{ number_format($row['total_discount'], 0, ',', '.') }}
                                </small>
                            @endif
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
                        <td colspan="9" class="text-center py-4 text-muted">Belum ada data jenis tagihan.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="fw-bold" style="background-color: #f1f5f9;">
                    <td colspan="5" class="text-end">TOTAL KESELURUHAN :</td>
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
