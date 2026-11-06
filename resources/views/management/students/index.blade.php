@extends('layouts.app')

@section('title', 'Data Murid')
@section('header_title', 'Manajemen Data Murid')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h5 class="fw-bold mb-1">Data Siswa / Murid</h5>
        <p class="text-muted small mb-0">Kelola data peserta didik beserta kategori beasiswa untuk penentuan skema tarif.</p>
    </div>
    @if(auth()->user()->hasRole(['admin', 'teller']))
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#importStudentsModal">
                <i class="bi bi-file-earmark-arrow-up me-1"></i> Import CSV
            </button>
            <a href="{{ route('management.students.create') }}" class="btn btn-pastel-primary">
                <i class="bi bi-person-plus me-1"></i> Tambah Murid
            </a>
        </div>
    @endif
</div>

<!-- Filters & Search -->
<div class="card p-3 mb-3">
    <form method="GET" action="{{ route('management.students.index') }}" class="row g-2 align-items-center">
        <div class="col-md-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama, NIS, atau NISN..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-3">
            <select name="class_room_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Rombel</option>
                @foreach($classRooms as $cr)
                    <option value="{{ $cr->id }}" {{ request('class_room_id') == $cr->id ? 'selected' : '' }}>
                        {{ $cr->name }} ({{ $cr->major?->code }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Kategori</option>
                <option value="reguler" {{ request('category') === 'reguler' ? 'selected' : '' }}>Reguler</option>
                <option value="beasiswa_50" {{ request('category') === 'beasiswa_50' ? 'selected' : '' }}>Beasiswa 50%</option>
                <option value="beasiswa_100" {{ request('category') === 'beasiswa_100' ? 'selected' : '' }}>Beasiswa 100%</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="lulus" {{ request('status') === 'lulus' ? 'selected' : '' }}>Lulus</option>
                <option value="mutasi" {{ request('status') === 'mutasi' ? 'selected' : '' }}>Mutasi</option>
                <option value="keluar" {{ request('status') === 'keluar' ? 'selected' : '' }}>Keluar</option>
            </select>
        </div>
        <div class="col-md-1 d-flex">
            <a href="{{ route('management.students.index') }}" class="btn btn-sm btn-light w-100" title="Reset Filter">
                <i class="bi bi-arrow-counterclockwise"></i>
            </a>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>NIS / NISN</th>
                    <th>Nama Murid</th>
                    <th>JK</th>
                    <th>Rombel & Jurusan</th>
                    <th>Kategori Siswa</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                    <tr>
                        <td>
                            <span class="fw-bold text-dark">{{ $student->nis }}</span>
                            @if($student->nisn)
                                <small class="d-block text-muted">{{ $student->nisn }}</small>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('management.students.show', $student) }}" class="text-decoration-none fw-semibold text-dark">
                                {{ $student->name }}
                            </a>
                            @if($student->phone)
                                <small class="d-block text-muted"><i class="bi bi-telephone"></i> {{ $student->phone }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $student->gender == 'L' ? 'badge-pastel-accent' : 'badge-pastel-lavender' }}">
                                {{ $student->gender == 'L' ? 'L' : 'P' }}
                            </span>
                        </td>
                        <td>
                            @if($student->classRoom)
                                <span class="fw-medium">{{ $student->classRoom->name }}</span>
                                <small class="d-block text-muted">{{ $student->classRoom->major?->name }}</small>
                            @else
                                <span class="text-muted">Belum ada kelas</span>
                            @endif
                        </td>
                        <td>
                            @if($student->category === 'beasiswa_100')
                                <span class="badge badge-pastel-accent">
                                    <i class="bi bi-award me-1"></i> Beasiswa 100%
                                </span>
                            @elseif($student->category === 'beasiswa_50')
                                <span class="badge badge-pastel-warning">
                                    <i class="bi bi-award me-1"></i> Beasiswa 50%
                                </span>
                            @else
                                <span class="badge badge-pastel-primary">Reguler</span>
                            @endif
                        </td>
                        <td>
                            @if($student->status === 'aktif')
                                <span class="badge badge-pastel-success">Aktif</span>
                            @elseif($student->status === 'lulus')
                                <span class="badge badge-pastel-accent">Lulus</span>
                            @else
                                <span class="badge bg-light text-muted">{{ ucfirst($student->status) }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('management.students.show', $student) }}" class="btn btn-sm btn-light" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>

                                @if(auth()->user()->hasRole(['admin', 'teller']))
                                    <a href="{{ route('management.students.edit', $student) }}" class="btn btn-sm btn-light" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endif

                                @if(auth()->user()->hasRole('admin'))
                                    <form action="{{ route('management.students.destroy', $student) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data murid ini?');">
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
                        <td colspan="7" class="text-center py-4 text-muted">Tidak ada data murid yang sesuai kriteria.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($students->hasPages())
        <div class="card-footer d-flex justify-content-end py-2">
            {{ $students->links() }}
        </div>
    @endif
</div>

@if(auth()->user()->hasRole(['admin', 'teller']))
    <!-- Modal Import Murid -->
    <div class="modal fade" id="importStudentsModal" tabindex="-1" aria-labelledby="importStudentsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('management.students.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold" id="importStudentsModalLabel">
                            <i class="bi bi-file-earmark-arrow-up me-1 text-pastel-primary"></i> Import Data Murid dari CSV
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info border-0 small mb-3" style="background-color: var(--pastel-accent-light); color: var(--pastel-accent-text);">
                            <i class="bi bi-info-circle-fill me-1"></i>
                            Gunakan file berformat <strong>.csv</strong> dengan kolom header:
                            <code>nis, nisn, nama, jenis_kelamin, rombel, kategori, no_hp, nama_wali, no_hp_wali, alamat, status</code>.
                        </div>

                        <div class="mb-3">
                            <label for="student_csv_file" class="form-label small fw-semibold">Pilih File CSV <span class="text-danger">*</span></label>
                            <input type="file" name="file" id="student_csv_file" class="form-control" accept=".csv,text/csv" required>
                        </div>

                        <div class="d-flex align-items-center justify-content-between p-2 rounded border bg-light">
                            <div class="small text-muted">
                                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Belum punya format?
                            </div>
                            <a href="{{ route('management.students.template') }}" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 0.8rem;">
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
