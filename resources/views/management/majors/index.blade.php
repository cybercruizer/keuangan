@extends('layouts.app')

@section('title', 'Jurusan')
@section('header_title', 'Manajemen Jurusan')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h5 class="fw-bold mb-1">Kompetensi Keahlian (Jurusan)</h5>
        <p class="text-muted small mb-0">Daftar program keahlian di SMK Muhammadiyah Mungkid.</p>
    </div>
    @if(auth()->user()->hasRole('admin'))
        <a href="{{ route('management.majors.create') }}" class="btn btn-pastel-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Jurusan
        </a>
    @endif
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th style="width: 15%;">Kode</th>
                    <th style="width: 35%;">Nama Jurusan</th>
                    <th style="width: 30%;">Keterangan</th>
                    <th style="width: 10%;">Rombel</th>
                    <th style="width: 10%;" class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($majors as $major)
                    <tr>
                        <td>
                            <span class="badge badge-pastel-accent font-monospace fs-6">{{ $major->code }}</span>
                        </td>
                        <td class="fw-bold">{{ $major->name }}</td>
                        <td class="text-muted small">{{ $major->description ?: '-' }}</td>
                        <td>
                            <span class="badge bg-light text-dark">{{ $major->class_rooms_count }} Rombel</span>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                @if(auth()->user()->hasRole('admin'))
                                    <a href="{{ route('management.majors.edit', $major) }}" class="btn btn-sm btn-light" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    @if($major->class_rooms_count == 0)
                                        <form action="{{ route('management.majors.destroy', $major) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jurusan ini?');">
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
                        <td colspan="5" class="text-center py-4 text-muted">Belum ada data jurusan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($majors->hasPages())
        <div class="card-footer d-flex justify-content-end py-2">
            {{ $majors->links() }}
        </div>
    @endif
</div>
@endsection
