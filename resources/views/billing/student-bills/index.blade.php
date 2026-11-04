@extends('layouts.app')

@section('title', 'Daftar Tagihan Siswa')
@section('header_title', 'Manajemen Tagihan Siswa')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h5 class="fw-bold mb-1">Daftar Tagihan Siswa</h5>
        <p class="text-muted small mb-0">Kelola dan pantau seluruh tagihan siswa (SPP bulanan, uang gedung, praktikum).</p>
    </div>
    @if(auth()->user()->hasRole(['admin', 'teller']))
        <a href="{{ route('billing.student-bills.create') }}" class="btn btn-pastel-primary">
            <i class="bi bi-file-earmark-plus me-1"></i> Terapkan / Generate Tagihan
        </a>
    @endif
</div>

<!-- Financial Summary Widgets -->
<div class="row g-3 mb-3">
    <div class="col-sm-4">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--pastel-primary-light); color: var(--pastel-primary);">
                <i class="bi bi-calculator"></i>
            </div>
            <div>
                <small class="text-muted d-block" style="font-size: 0.75rem;">Total Tagihan Terbit</small>
                <h6 class="fw-bold mb-0 text-dark">Rp {{ number_format($totalBilled, 0, ',', '.') }}</h6>
                <small class="text-muted" style="font-size: 0.7rem;">Sesuai filter aktif</small>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--pastel-success-light); color: var(--pastel-success);">
                <i class="bi bi-check2-circle"></i>
            </div>
            <div>
                <small class="text-muted d-block" style="font-size: 0.75rem;">Total Telah Dibayar</small>
                <h6 class="fw-bold mb-0 text-success">Rp {{ number_format($totalPaid, 0, ',', '.') }}</h6>
                <small class="text-muted" style="font-size: 0.7rem;">Penerimaan tercatat</small>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--pastel-danger-light); color: var(--pastel-danger);">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div>
                <small class="text-muted d-block" style="font-size: 0.75rem;">Sisa Tunggakan</small>
                <h6 class="fw-bold mb-0 text-danger">Rp {{ number_format($totalRemaining, 0, ',', '.') }}</h6>
                <small class="text-muted" style="font-size: 0.7rem;">Belum tertagih</small>
            </div>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card p-3 mb-3">
    <form method="GET" action="{{ route('billing.student-bills.index') }}" class="row g-2 align-items-center">
        <div class="col-md-3">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Cari NIS atau Nama Siswa..." value="{{ request('search') }}">
            </div>
        </div>

        <div class="col-md-2">
            <select name="academic_year_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Tahun Ajaran</option>
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                        {{ $year->name }} {{ $year->is_active ? '★' : '' }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <select name="class_room_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Rombel</option>
                @foreach($classRooms as $cr)
                    <option value="{{ $cr->id }}" {{ request('class_room_id') == $cr->id ? 'selected' : '' }}>
                        {{ $cr->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <select name="bill_type_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Jenis Tagihan</option>
                @foreach($billTypes as $bt)
                    <option value="{{ $bt->id }}" {{ request('bill_type_id') == $bt->id ? 'selected' : '' }}>
                        {{ $bt->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Belum Bayar</option>
                <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Cicilan / Sebagian</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Lunas</option>
            </select>
        </div>

        <div class="col-md-1 d-flex">
            <a href="{{ route('billing.student-bills.index') }}" class="btn btn-sm btn-light w-100" title="Reset">
                <i class="bi bi-arrow-counterclockwise"></i>
            </a>
        </div>
    </form>
</div>

<!-- Bills Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Siswa & Rombel</th>
                    <th>Jenis Tagihan</th>
                    <th>Periode</th>
                    <th>Tarif Asli</th>
                    <th>Potongan</th>
                    <th>Wajib Bayar</th>
                    <th>Telah Dibayar</th>
                    <th>Sisa Bayar</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($studentBills as $bill)
                    <tr>
                        <td>
                            <a href="{{ route('management.students.show', $bill->student_id) }}" class="fw-bold text-dark text-decoration-none">
                                {{ $bill->student?->name }}
                            </a>
                            <small class="d-block text-muted">
                                NIS: {{ $bill->student?->nis }} &bull; {{ $bill->student?->classRoom?->name ?: '-' }}
                            </small>
                        </td>
                        <td>
                            <span class="fw-semibold">{{ $bill->billType?->name }}</span>
                            @if($bill->student?->category !== 'reguler')
                                <small class="d-block">
                                    <span class="badge {{ $bill->student?->category === 'beasiswa_100' ? 'badge-pastel-accent' : 'badge-pastel-warning' }}" style="font-size: 0.65rem;">
                                        {{ $bill->student?->category_label }}
                                    </span>
                                </small>
                            @endif
                        </td>
                        <td>
                            @if($bill->month)
                                <span class="badge badge-pastel-primary">{{ $bill->month_name }} {{ $bill->year }}</span>
                            @else
                                <span class="text-muted small">TA {{ $bill->academicYear?->name }}</span>
                            @endif
                        </td>
                        <td class="text-muted small">Rp {{ number_format($bill->amount, 0, ',', '.') }}</td>
                        <td>
                            @if($bill->discount_percentage > 0)
                                <span class="badge badge-pastel-warning">
                                    -{{ number_format($bill->discount_percentage, 0) }}% (Rp {{ number_format($bill->discount_amount, 0, ',', '.') }})
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="fw-semibold">Rp {{ number_format($bill->final_amount, 0, ',', '.') }}</td>
                        <td class="text-success fw-semibold">Rp {{ number_format($bill->paid_amount, 0, ',', '.') }}</td>
                        <td class="fw-bold {{ $bill->remainingAmount() > 0 ? 'text-danger' : 'text-muted' }}">
                            Rp {{ number_format($bill->remainingAmount(), 0, ',', '.') }}
                        </td>
                        <td>
                            @if($bill->status === 'paid')
                                <span class="badge badge-pastel-success"><i class="bi bi-check-circle me-1"></i> Lunas</span>
                            @elseif($bill->status === 'partial')
                                <span class="badge badge-pastel-warning"><i class="bi bi-pie-chart me-1"></i> Cicilan</span>
                            @else
                                <span class="badge badge-pastel-danger"><i class="bi bi-clock me-1"></i> Belum Bayar</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                @if(auth()->user()->hasRole(['admin', 'teller']) && $bill->status !== 'paid')
                                    <a href="{{ route('payments.create', ['student_id' => $bill->student_id]) }}" class="btn btn-sm btn-pastel-primary py-1 px-2" title="Bayar Sekarang">
                                        <i class="bi bi-cash me-1"></i> Bayar
                                    </a>
                                @endif

                                @if(auth()->user()->hasRole('admin') && $bill->paid_amount == 0)
                                    <form action="{{ route('billing.student-bills.destroy', $bill) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan/menghapus tagihan ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light text-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-4 text-muted">Belum ada data tagihan yang sesuai kriteria pencarian.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($studentBills->hasPages())
        <div class="card-footer d-flex justify-content-end py-2">
            {{ $studentBills->links() }}
        </div>
    @endif
</div>
@endsection
