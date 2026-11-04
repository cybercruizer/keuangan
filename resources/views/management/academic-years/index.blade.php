@extends('layouts.app')

@section('title', 'Tahun Ajaran')
@section('header_title', 'Manajemen Tahun Ajaran')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h5 class="fw-bold mb-1">Daftar Tahun Ajaran</h5>
        <p class="text-muted small mb-0">Kelola tahun ajaran akademik dan tentukan periode aktif untuk sistem tagihan.</p>
    </div>
    @if(auth()->user()->hasRole('admin'))
        <a href="{{ route('management.academic-years.create') }}" class="btn btn-pastel-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Tahun Ajaran
        </a>
    @endif
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Tahun Ajaran</th>
                    <th>Semester</th>
                    <th>Periode Tanggal</th>
                    <th>Jumlah Rombel</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($academicYears as $year)
                    <tr>
                        <td class="fw-bold">{{ $year->name }}</td>
                        <td>
                            <span class="badge {{ $year->semester === 'ganjil' ? 'badge-pastel-primary' : 'badge-pastel-accent' }}">
                                Semester {{ ucfirst($year->semester) }}
                            </span>
                        </td>
                        <td class="text-muted small">
                            @if($year->start_date && $year->end_date)
                                {{ $year->start_date->format('d M Y') }} s/d {{ $year->end_date->format('d M Y') }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark">{{ $year->class_rooms_count }} Rombel</span>
                        </td>
                        <td>
                            @if($year->is_active)
                                <span class="badge badge-pastel-success">
                                    <i class="bi bi-check-circle-fill me-1"></i> Aktif
                                </span>
                            @else
                                <span class="badge bg-light text-muted">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                @if(auth()->user()->hasRole('admin'))
                                    @if(!$year->is_active)
                                        <form action="{{ route('management.academic-years.set-active', $year) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-pastel-primary-light" title="Jadikan Tahun Ajaran Aktif">
                                                <i class="bi bi-check2-circle"></i> Aktifkan
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('management.academic-years.edit', $year) }}" class="btn btn-sm btn-light" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    @if(!$year->is_active && $year->class_rooms_count == 0)
                                        <form action="{{ route('management.academic-years.destroy', $year) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tahun ajaran ini?');">
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
                        <td colspan="6" class="text-center py-4 text-muted">Belum ada data tahun ajaran.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($academicYears->hasPages())
        <div class="card-footer d-flex justify-content-end py-2">
            {{ $academicYears->links() }}
        </div>
    @endif
</div>
@endsection
