@extends('layouts.app')

@section('title', 'Jenis & Tarif Tagihan')
@section('header_title', 'Manajemen Jenis & Tarif Tagihan')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h5 class="fw-bold mb-1">Jenis Tagihan & Skema Tarif</h5>
        <p class="text-muted small mb-0">Kelola master biaya sekolah (SPP, Uang Gedung, Praktik) dan aturan diskon beasiswa.</p>
    </div>
    @if(auth()->user()->hasRole('admin'))
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#importBillTypesModal">
                <i class="bi bi-file-earmark-arrow-up me-1"></i> Import CSV
            </button>
            <a href="{{ route('billing.bill-types.create') }}" class="btn btn-pastel-primary">
                <i class="bi bi-plus-lg me-1"></i> Tambah Jenis Tagihan
            </a>
        </div>
    @endif
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Nama Tagihan</th>
                    <th>Tipe Pembayaran</th>
                    <th>Tarif Standar (Rp)</th>
                    <th>Hak Diskon Beasiswa</th>
                    <th>Skema Potongan</th>
                    <th>Total Tagihan Terbit</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($billTypes as $bt)
                    <tr>
                        <td>
                            <div class="fw-bold text-dark">{{ $bt->name }}</div>
                            <small class="text-muted">{{ $bt->description ?: '-' }}</small>
                        </td>
                        <td>
                            @if($bt->type === 'spp')
                                <span class="badge badge-pastel-primary"><i class="bi bi-calendar-month me-1"></i> SPP (Bulanan)</span>
                            @elseif($bt->type === 'custom')
                                <span class="badge badge-pastel-accent"><i class="bi bi-wallet2 me-1"></i> Custom / Bisa Dicicil</span>
                            @else
                                <span class="badge badge-pastel-warning"><i class="bi bi-piggy-bank me-1"></i> Tabungan</span>
                            @endif
                        </td>
                        <td class="fw-bold">
                            Rp {{ number_format($bt->amount, 0, ',', '.') }}
                        </td>
                        <td>
                            @if($bt->is_discountable)
                                <span class="badge badge-pastel-success">
                                    <i class="bi bi-check-circle-fill me-1"></i> Bisa Didiskon
                                </span>
                            @else
                                <span class="badge bg-light text-muted">
                                    <i class="bi bi-dash-circle me-1"></i> Tanpa Diskon
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($bt->is_discountable && $bt->discounts->isNotEmpty())
                                <div class="d-flex flex-column gap-1">
                                    @foreach($bt->discounts as $d)
                                        <small class="d-inline-flex align-items-center gap-1">
                                            <span class="badge {{ $d->student_category === 'beasiswa_100' ? 'badge-pastel-accent' : 'badge-pastel-warning' }}" style="font-size: 0.7rem;">
                                                {{ $d->student_category === 'beasiswa_100' ? 'Beasiswa 100%' : 'Beasiswa 50%' }}
                                            </span>
                                            <span class="fw-semibold">{{ number_format($d->percentage, 0) }}%</span>
                                        </small>
                                    @endforeach
                                </div>
                            @elseif($bt->is_discountable)
                                <span class="text-muted small">Aturan default (50% / 100%)</span>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark">{{ $bt->student_bills_count }} Tagihan</span>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                @if(auth()->user()->hasRole('admin'))
                                    <a href="{{ route('billing.bill-types.edit', $bt) }}" class="btn btn-sm btn-light" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    @if($bt->student_bills_count == 0)
                                        <form action="{{ route('billing.bill-types.destroy', $bt) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jenis tagihan ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light text-danger" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Belum ada data jenis tagihan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($billTypes->hasPages())
        <div class="card-footer d-flex justify-content-end py-2">
            {{ $billTypes->links() }}
        </div>
    @endif
</div>

@if(auth()->user()->hasRole('admin'))
    <!-- Modal Import Jenis Tagihan -->
    <div class="modal fade" id="importBillTypesModal" tabindex="-1" aria-labelledby="importBillTypesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('billing.bill-types.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold" id="importBillTypesModalLabel">
                            <i class="bi bi-file-earmark-arrow-up me-1 text-pastel-primary"></i> Import Jenis Tagihan dari CSV
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info border-0 small mb-3" style="background-color: var(--pastel-accent-light); color: var(--pastel-accent-text);">
                            <i class="bi bi-info-circle-fill me-1"></i>
                            Gunakan file <strong>.csv</strong> dengan header:
                            <code>nama, tarif, tipe, bisa_diskon, diskon_50, diskon_100, keterangan</code>.<br>
                            <small class="text-muted d-block mt-1">Tipe yang didukung: <em>spp</em>, <em>custom</em>, <em>tabungan</em>.</small>
                        </div>

                        <div class="mb-3">
                            <label for="bill_type_csv_file" class="form-label small fw-semibold">Pilih File CSV <span class="text-danger">*</span></label>
                            <input type="file" name="file" id="bill_type_csv_file" class="form-control" accept=".csv,text/csv" required>
                        </div>

                        <div class="d-flex align-items-center justify-content-between p-2 rounded border bg-light">
                            <div class="small text-muted">
                                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Format belum ada?
                            </div>
                            <a href="{{ route('billing.bill-types.template') }}" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 0.8rem;">
                                <i class="bi bi-download me-1"></i> Unduh Template CSV
                            </a>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-pastel-primary">
                            <i class="bi bi-upload me-1"></i> Mulai Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection
