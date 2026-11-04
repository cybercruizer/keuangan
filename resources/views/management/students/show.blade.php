@extends('layouts.app')

@section('title', 'Detail Murid - ' . $student->name)
@section('header_title', 'Profil Murid')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('management.students.index') }}" class="btn btn-sm btn-light">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h5 class="fw-bold mb-0">{{ $student->name }}</h5>
        <span class="badge {{ $student->gender == 'L' ? 'badge-pastel-accent' : 'badge-pastel-lavender' }}">
            {{ $student->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}
        </span>
    </div>
    <div class="d-flex gap-2">
        @if(auth()->user()->hasRole(['admin', 'teller']))
            <a href="{{ route('management.students.edit', $student) }}" class="btn btn-sm btn-pastel-primary">
                <i class="bi bi-pencil me-1"></i> Edit Data Murid
            </a>
        @endif
    </div>
</div>

<div class="row g-3">
    <!-- Student Profile Card -->
    <div class="col-lg-4">
        <div class="card p-4 text-center mb-3">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mx-auto mb-3" style="width: 80px; height: 80px; background-color: var(--pastel-primary-light); color: var(--pastel-primary);">
                <i class="bi bi-person-fill" style="font-size: 2.5rem;"></i>
            </div>
            <h5 class="fw-bold mb-1">{{ $student->name }}</h5>
            <p class="text-muted small mb-2">NIS: {{ $student->nis }} &bull; NISN: {{ $student->nisn ?: '-' }}</p>

            <div class="mb-3">
                @if($student->category === 'beasiswa_100')
                    <span class="badge badge-pastel-accent py-2 px-3">
                        <i class="bi bi-award-fill me-1"></i> Beasiswa 100% (Bebas Biaya)
                    </span>
                @elseif($student->category === 'beasiswa_50')
                    <span class="badge badge-pastel-warning py-2 px-3">
                        <i class="bi bi-award-fill me-1"></i> Beasiswa 50% (Potongan 50%)
                    </span>
                @else
                    <span class="badge badge-pastel-primary py-2 px-3">
                        Kategori Reguler
                    </span>
                @endif
            </div>

            <div class="border-top pt-3 text-start small">
                <div class="d-flex justify-content-between py-1">
                    <span class="text-muted">Kelas / Rombel:</span>
                    <span class="fw-semibold">{{ $student->classRoom ? $student->classRoom->name : '-' }}</span>
                </div>
                <div class="d-flex justify-content-between py-1">
                    <span class="text-muted">Jurusan:</span>
                    <span class="fw-semibold">{{ $student->classRoom?->major?->name ?: '-' }}</span>
                </div>
                <div class="d-flex justify-content-between py-1">
                    <span class="text-muted">Tahun Ajaran:</span>
                    <span class="fw-semibold">{{ $student->classRoom?->academicYear?->name ?: '-' }}</span>
                </div>
                <div class="d-flex justify-content-between py-1">
                    <span class="text-muted">Status:</span>
                    <span class="badge badge-pastel-success text-capitalize">{{ $student->status }}</span>
                </div>
            </div>
        </div>

        <!-- Contact & Guardian -->
        <div class="card p-3 mb-3">
            <h6 class="fw-bold mb-3 pb-2 border-bottom small text-uppercase" style="letter-spacing: 0.05em; color: var(--pastel-text);">
                Kontak & Orang Tua
            </h6>
            <div class="small">
                <div class="mb-2">
                    <span class="text-muted d-block">No. Telepon Murid:</span>
                    <span class="fw-medium">{{ $student->phone ?: '-' }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted d-block">Nama Orang Tua / Wali:</span>
                    <span class="fw-medium">{{ $student->parent_name ?: '-' }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted d-block">No. Telepon Orang Tua:</span>
                    <span class="fw-medium">{{ $student->parent_phone ?: '-' }}</span>
                </div>
                <div>
                    <span class="text-muted d-block">Alamat:</span>
                    <span>{{ $student->address ?: '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Linked User Account -->
        <div class="card p-3">
            <h6 class="fw-bold mb-3 pb-2 border-bottom small text-uppercase" style="letter-spacing: 0.05em; color: var(--pastel-text);">
                Akun Login Sistem
            </h6>
            @if($student->user)
                <div class="small">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Username:</span>
                        <span class="fw-semibold">{{ $student->user->username }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Email:</span>
                        <span>{{ $student->user->email }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Role:</span>
                        <span class="badge badge-pastel-lavender">Murid</span>
                    </div>
                </div>
            @else
                <p class="text-muted small mb-0">Murid ini belum memiliki akun login siswa.</p>
            @endif
        </div>
    </div>

    <!-- Right Side: Billing & Financial Snapshot -->
    <div class="col-lg-8">
        <div class="row g-3 mb-3">
            <div class="col-sm-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: var(--pastel-primary-light); color: var(--pastel-primary);">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block" style="font-size: 0.75rem;">Total Tagihan</small>
                        <h6 class="fw-bold mb-0">{{ $student->bills->count() }} Tagihan</h6>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: var(--pastel-success-light); color: var(--pastel-success);">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block" style="font-size: 0.75rem;">Tagihan Lunas</small>
                        <h6 class="fw-bold mb-0">{{ $student->bills->where('status', 'paid')->count() }} Lunas</h6>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: var(--pastel-accent-light); color: var(--pastel-accent);">
                        <i class="bi bi-piggy-bank"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block" style="font-size: 0.75rem;">Saldo Tabungan</small>
                        <h6 class="fw-bold mb-0">Rp {{ number_format($student->currentSavingsBalance(), 0, ',', '.') }}</h6>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tagihan Siswa Card -->
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span>Daftar Tagihan Siswa</span>
                <span class="badge bg-light text-dark">{{ $student->bills->count() }} Tagihan</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Jenis Tagihan</th>
                            <th>Bulan/Periode</th>
                            <th>Nominal</th>
                            <th>Diskon</th>
                            <th>Sisa Bayar</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($student->bills as $bill)
                            <tr>
                                <td class="fw-semibold">{{ $bill->billType?->name }}</td>
                                <td>{{ $bill->month_name ? $bill->month_name . ' ' . $bill->year : '-' }}</td>
                                <td>Rp {{ number_format($bill->amount, 0, ',', '.') }}</td>
                                <td>
                                    @if($bill->discount_percentage > 0)
                                        <span class="badge badge-pastel-warning">{{ $bill->discount_percentage }}% (Rp {{ number_format($bill->discount_amount, 0, ',', '.') }})</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="fw-bold">Rp {{ number_format($bill->remainingAmount(), 0, ',', '.') }}</td>
                                <td>
                                    @if($bill->status === 'paid')
                                        <span class="badge badge-pastel-success">Lunas</span>
                                    @elseif($bill->status === 'partial')
                                        <span class="badge badge-pastel-warning">Cicilan</span>
                                    @else
                                        <span class="badge badge-pastel-danger">Belum Bayar</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="bi bi-receipt d-block mb-1 fs-3 text-muted opacity-50"></i>
                                    Belum ada tagihan diterbitkan untuk siswa ini. Tagihan akan di-generate pada modul Tagihan (Phase 4).
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Riwayat Pembayaran Terakhir -->
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span>Riwayat Transaksi Pembayaran</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No. Kwitansi</th>
                            <th>Tanggal</th>
                            <th>Total Bayar</th>
                            <th>Metode</th>
                            <th>Kasir / Teller</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($student->payments as $payment)
                            <tr>
                                <td class="fw-bold">{{ $payment->invoice_number }}</td>
                                <td>{{ $payment->payment_date->format('d M Y') }}</td>
                                <td class="fw-semibold text-success">Rp {{ number_format($payment->total_amount, 0, ',', '.') }}</td>
                                <td><span class="badge bg-light text-dark">{{ strtoupper($payment->payment_method) }}</span></td>
                                <td>{{ $payment->user?->name ?: 'Sistem' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada riwayat transaksi pembayaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
