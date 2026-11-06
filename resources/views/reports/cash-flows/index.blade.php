@extends('layouts.app')

@section('title', 'Laporan Arus Kas')
@section('header_title', 'Laporan Arus Kas (Cash Flow)')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
        <h5 class="fw-bold mb-1">Laporan Arus Kas</h5>
        <p class="text-muted small mb-0">Pantau arus kas masuk (pembayaran kuitansi siswa) dan arus kas keluar operasional sekolah.</p>
    </div>
    <div class="d-flex gap-2">
        @if(auth()->user()->hasRole(['admin', 'teller']))
            <button type="button" class="btn btn-pastel-danger" data-bs-toggle="modal" data-bs-target="#modalExpense">
                <i class="bi bi-dash-circle me-1"></i> Catat Pengeluaran Kas
            </button>
        @endif
        <a href="{{ route('reports.cash-flows.index', array_merge(request()->all(), ['export' => 1])) }}" class="btn btn-light">
            <i class="bi bi-file-earmark-arrow-down me-1"></i> Export CSV
        </a>
        <a href="{{ route('reports.cash-flows.print', request()->all()) }}" target="_blank" class="btn btn-light">
            <i class="bi bi-printer me-1"></i> Cetak Laporan
        </a>
    </div>
</div>

<!-- Financial Summary Widgets -->
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--pastel-success-light); color: var(--pastel-success);">
                <i class="bi bi-arrow-down-left"></i>
            </div>
            <div>
                <small class="text-muted d-block" style="font-size: 0.75rem;">Total Kas Masuk (Pemasukan)</small>
                <h5 class="fw-bold mb-0 text-success">Rp {{ number_format($totalIncome, 0, ',', '.') }}</h5>
                <small class="text-muted" style="font-size: 0.7rem;">{{ $periodLabel }}</small>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--pastel-danger-light); color: var(--pastel-danger);">
                <i class="bi bi-arrow-up-right"></i>
            </div>
            <div>
                <small class="text-muted d-block" style="font-size: 0.75rem;">Total Kas Keluar (Pengeluaran)</small>
                <h5 class="fw-bold mb-0 text-danger">Rp {{ number_format($totalExpense, 0, ',', '.') }}</h5>
                <small class="text-muted" style="font-size: 0.7rem;">Operasional & pengadaan</small>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: {{ $netBalance >= 0 ? 'var(--pastel-primary-light)' : 'var(--pastel-warning-light)' }}; color: {{ $netBalance >= 0 ? 'var(--pastel-primary)' : 'var(--pastel-warning)' }};">
                <i class="bi bi-wallet2"></i>
            </div>
            <div>
                <small class="text-muted d-block" style="font-size: 0.75rem;">Saldo Bersih (Surplus / Defisit)</small>
                <h5 class="fw-bold mb-0 {{ $netBalance >= 0 ? 'text-primary' : 'text-danger' }}">
                    Rp {{ number_format($netBalance, 0, ',', '.') }}
                </h5>
                <small class="text-muted" style="font-size: 0.7rem;">Pemasukan - Pengeluaran</small>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card p-3 mb-3">
    <form method="GET" action="{{ route('reports.cash-flows.index') }}" class="row g-2 align-items-center">
        <div class="col-md-2">
            <label class="form-label small mb-1 fw-semibold">Pilih Periode:</label>
            <select name="period" id="periodSelect" class="form-select form-select-sm" onchange="togglePeriodInputs(this.value)">
                <option value="monthly" {{ $period === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                <option value="daily" {{ $period === 'daily' ? 'selected' : '' }}>Harian</option>
                <option value="yearly" {{ $period === 'yearly' ? 'selected' : '' }}>Tahunan</option>
                <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Rentang Tanggal</option>
            </select>
        </div>

        <!-- Daily input -->
        <div class="col-md-3 period-field" id="field_daily" style="display: {{ $period === 'daily' ? 'block' : 'none' }};">
            <label class="form-label small mb-1">Tanggal:</label>
            <input type="date" name="date" class="form-control form-control-sm" value="{{ $dateParam }}">
        </div>

        <!-- Monthly inputs -->
        <div class="col-md-2 period-field" id="field_month" style="display: {{ $period === 'monthly' ? 'block' : 'none' }};">
            <label class="form-label small mb-1">Bulan:</label>
            <select name="month" class="form-select form-select-sm">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $monthParam == $m ? 'selected' : '' }}>
                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                    </option>
                @endfor
            </select>
        </div>
        <div class="col-md-2 period-field" id="field_year" style="display: {{ in_array($period, ['monthly', 'yearly']) ? 'block' : 'none' }};">
            <label class="form-label small mb-1">Tahun:</label>
            <input type="number" name="year" class="form-control form-control-sm" value="{{ $yearParam }}">
        </div>

        <!-- Custom date inputs -->
        <div class="col-md-2 period-field" id="field_date_from" style="display: {{ $period === 'custom' ? 'block' : 'none' }};">
            <label class="form-label small mb-1">Dari Tanggal:</label>
            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFromParam }}">
        </div>
        <div class="col-md-2 period-field" id="field_date_to" style="display: {{ $period === 'custom' ? 'block' : 'none' }};">
            <label class="form-label small mb-1">Sampai Tanggal:</label>
            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateToParam }}">
        </div>

        <div class="col-md-2">
            <label class="form-label small mb-1 fw-semibold">Jenis Arus Kas:</label>
            <select name="type" class="form-select form-select-sm">
                <option value="">Semua (Masuk & Keluar)</option>
                <option value="income" {{ $type === 'income' ? 'selected' : '' }}>Pemasukan Saja</option>
                <option value="expense" {{ $type === 'expense' ? 'selected' : '' }}>Pengeluaran Saja</option>
            </select>
        </div>

        <div class="col-md-2 d-flex align-items-end gap-1 mt-auto">
            <button type="submit" class="btn btn-sm btn-pastel-primary w-50">Filter</button>
            <a href="{{ route('reports.cash-flows.index') }}" class="btn btn-sm btn-light w-50">Reset</a>
        </div>
    </form>
</div>

<!-- Cash Flow Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Jenis</th>
                    <th>Kategori</th>
                    <th>Uraian / Deskripsi</th>
                    <th>Dicatat Oleh</th>
                    <th class="text-end">Nominal Masuk</th>
                    <th class="text-end">Nominal Keluar</th>
                    <th class="text-end" style="width: 50px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cashFlows as $cf)
                    <tr>
                        <td class="fw-semibold">{{ $cf->date->format('d/m/Y') }}</td>
                        <td>
                            @if($cf->type === 'income')
                                <span class="badge badge-pastel-success">
                                    <i class="bi bi-arrow-down-left me-1"></i> Masuk
                                </span>
                            @else
                                <span class="badge badge-pastel-danger">
                                    <i class="bi bi-arrow-up-right me-1"></i> Keluar
                                </span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $cf->category }}</span>
                        </td>
                        <td>
                            <span>{{ $cf->description }}</span>
                            @if($cf->payment)
                                <a href="{{ route('payments.receipt', $cf->payment) }}" class="badge badge-pastel-accent text-decoration-none ms-1">
                                    {{ $cf->payment->invoice_number }}
                                </a>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $cf->user?->name ?: 'Sistem' }}</td>
                        <td class="text-end fw-bold text-success">
                            {{ $cf->type === 'income' ? 'Rp ' . number_format($cf->amount, 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-end fw-bold text-danger">
                            {{ $cf->type === 'expense' ? 'Rp ' . number_format($cf->amount, 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-end">
                            @if(auth()->user()->hasRole('admin') && $cf->payment_id === null)
                                <form action="{{ route('reports.cash-flows.destroy', $cf) }}" method="POST" onsubmit="return confirm('Hapus catatan pengeluaran kas ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light text-danger py-0 px-2" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Belum ada catatan arus kas pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="fw-bold bg-light">
                    <td colspan="5" class="text-end">TOTAL :</td>
                    <td class="text-end text-success">Rp {{ number_format($totalIncome, 0, ',', '.') }}</td>
                    <td class="text-end text-danger">Rp {{ number_format($totalExpense, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @if($cashFlows->hasPages())
        <div class="card-footer d-flex justify-content-end py-2">
            {{ $cashFlows->links() }}
        </div>
    @endif
</div>

<!-- Modal Catat Pengeluaran Baru -->
<div class="modal fade" id="modalExpense" tabindex="-1" aria-labelledby="modalExpenseLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('reports.cash-flows.store') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="expense">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold" id="modalExpenseLabel">Catat Pengeluaran Kas Sekolah</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="expense_date" class="form-label small fw-semibold">Tanggal Pengeluaran <span class="text-danger">*</span></label>
                        <input type="date" name="date" id="expense_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="expense_category" class="form-label small fw-semibold">Kategori Pengeluaran <span class="text-danger">*</span></label>
                        <select name="category" id="expense_category" class="form-select" required>
                            <option value="Operasional Sekolah">Operasional Sekolah</option>
                            <option value="Gaji & Honorarium">Gaji & Honorarium</option>
                            <option value="Sarana & Prasarana">Sarana & Prasarana</option>
                            <option value="Bahan Praktik & Modul">Bahan Praktik & Modul</option>
                            <option value="ATK & Percetakan">ATK & Percetakan</option>
                            <option value="Kegiatan Kesiswaan">Kegiatan Kesiswaan</option>
                            <option value="Listrik, Air & Internet">Listrik, Air & Internet</option>
                            <option value="Lain-lain">Lain-lain</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="expense_amount" class="form-label small fw-semibold">Nominal Pengeluaran (Rp) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">Rp</span>
                            <input type="number" step="1000" min="1000" name="amount" id="expense_amount" class="form-control fw-bold" placeholder="0" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="expense_desc" class="form-label small fw-semibold">Uraian / Keterangan Belanja <span class="text-danger">*</span></label>
                        <textarea name="description" id="expense_desc" rows="3" class="form-control" placeholder="Contoh: Pembelian kertas F4 dan tinta printer kasir..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-pastel-danger">
                        <i class="bi bi-save me-1"></i> Simpan Pengeluaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function togglePeriodInputs(val) {
        document.querySelectorAll('.period-field').forEach(el => el.style.display = 'none');
        if (val === 'daily') {
            document.getElementById('field_daily').style.display = 'block';
        } else if (val === 'monthly') {
            document.getElementById('field_month').style.display = 'block';
            document.getElementById('field_year').style.display = 'block';
        } else if (val === 'yearly') {
            document.getElementById('field_year').style.display = 'block';
        } else if (val === 'custom') {
            document.getElementById('field_date_from').style.display = 'block';
            document.getElementById('field_date_to').style.display = 'block';
        }
    }
</script>
@endsection
