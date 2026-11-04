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
        <a href="{{ route('billing.bill-types.create') }}" class="btn btn-pastel-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Jenis Tagihan
        </a>
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
@endsection
