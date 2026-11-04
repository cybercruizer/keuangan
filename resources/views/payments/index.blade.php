@extends('layouts.app')

@section('title', 'Riwayat Pembayaran')
@section('header_title', 'Riwayat Transaksi Pembayaran')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h5 class="fw-bold mb-1">Riwayat Transaksi Kasir</h5>
        <p class="text-muted small mb-0">Catatan pembayaran siswa, pencetakan ulang kuitansi, dan monitoring kas masuk.</p>
    </div>
    @if(auth()->user()->hasRole(['admin', 'teller']))
        <a href="{{ route('payments.create') }}" class="btn btn-pastel-primary">
            <i class="bi bi-cash-stack me-1"></i> Transaksi Pembayaran Baru
        </a>
    @endif
</div>

<!-- Financial Summary Widgets -->
<div class="row g-3 mb-3">
    <div class="col-sm-6">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--pastel-success-light); color: var(--pastel-success);">
                <i class="bi bi-cash-coin"></i>
            </div>
            <div>
                <small class="text-muted d-block" style="font-size: 0.75rem;">Total Pembayaran Terkumpul</small>
                <h5 class="fw-bold mb-0 text-success">Rp {{ number_format($totalCollected, 0, ',', '.') }}</h5>
                <small class="text-muted" style="font-size: 0.72rem;">Sesuai filter pencarian</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--pastel-accent-light); color: var(--pastel-accent);">
                <i class="bi bi-receipt"></i>
            </div>
            <div>
                <small class="text-muted d-block" style="font-size: 0.75rem;">Jumlah Transaksi Kuitansi</small>
                <h5 class="fw-bold mb-0 text-dark">{{ number_format($totalTransactions) }} Transaksi</h5>
                <small class="text-muted" style="font-size: 0.72rem;">Total bukti setor tercetak</small>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card p-3 mb-3">
    <form method="GET" action="{{ route('payments.index') }}" class="row g-2 align-items-center">
        <div class="col-md-3">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="No. Kuitansi / Nama / NIS..." value="{{ request('search') }}">
            </div>
        </div>

        <div class="col-md-2">
            <input type="date" name="date_from" class="form-control form-control-sm" placeholder="Dari Tanggal" value="{{ request('date_from') }}" title="Dari Tanggal">
        </div>

        <div class="col-md-2">
            <input type="date" name="date_to" class="form-control form-control-sm" placeholder="Sampai Tanggal" value="{{ request('date_to') }}" title="Sampai Tanggal">
        </div>

        <div class="col-md-3">
            <select name="user_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Teller / Kasir</option>
                @foreach($tellers as $teller)
                    <option value="{{ $teller->id }}" {{ request('user_id') == $teller->id ? 'selected' : '' }}>
                        {{ $teller->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-pastel-primary w-50">Filter</button>
            <a href="{{ route('payments.index') }}" class="btn btn-sm btn-light w-50" title="Reset">Reset</a>
        </div>
    </form>
</div>

<!-- Payments Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>No. Kuitansi</th>
                    <th>Tanggal</th>
                    <th>Nama Siswa & Rombel</th>
                    <th>Item yang Dibayar</th>
                    <th>Total Bayar</th>
                    <th>Metode</th>
                    <th>Kasir / Teller</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $p)
                    <tr>
                        <td>
                            <a href="{{ route('payments.receipt', $p) }}" class="fw-bold text-dark font-monospace text-decoration-none">
                                {{ $p->invoice_number }}
                            </a>
                        </td>
                        <td>
                            <span>{{ $p->payment_date->format('d/m/Y') }}</span>
                            <small class="d-block text-muted" style="font-size: 0.7rem;">{{ $p->created_at->format('H:i') }} WIB</small>
                        </td>
                        <td>
                            <a href="{{ route('management.students.show', $p->student_id) }}" class="fw-semibold text-dark text-decoration-none">
                                {{ $p->student?->name }}
                            </a>
                            <small class="d-block text-muted">
                                NIS: {{ $p->student?->nis }} &bull; {{ $p->student?->classRoom?->name ?: '-' }}
                            </small>
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($p->items as $item)
                                    <span class="badge bg-light text-dark border" style="font-size: 0.72rem;">
                                        {{ $item->notes }} (Rp {{ number_format($item->amount, 0, ',', '.') }})
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="fw-bold text-success">
                            Rp {{ number_format($p->total_amount, 0, ',', '.') }}
                        </td>
                        <td>
                            <span class="badge badge-pastel-primary text-uppercase">{{ $p->payment_method }}</span>
                        </td>
                        <td class="small text-muted">
                            {{ $p->user?->name ?: 'Kasir' }}
                        </td>
                        <td class="text-end">
                            <a href="{{ route('payments.receipt', $p) }}" class="btn btn-sm btn-light py-1 px-2" title="Cetak Kuitansi A5 Landscape">
                                <i class="bi bi-printer me-1"></i> Cetak A5
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Belum ada riwayat transaksi pembayaran.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($payments->hasPages())
        <div class="card-footer d-flex justify-content-end py-2">
            {{ $payments->links() }}
        </div>
    @endif
</div>
@endsection
