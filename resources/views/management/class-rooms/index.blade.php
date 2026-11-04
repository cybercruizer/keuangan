@extends('layouts.app')

@section('title', 'Rombel & Kelas')
@section('header_title', 'Manajemen Rombel & Kelas')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h5 class="fw-bold mb-1">Rombongan Belajar (Rombel)</h5>
        <p class="text-muted small mb-0">Kelola kelas berdasarkan tahun ajaran, tingkat, dan program keahlian.</p>
    </div>
    @if(auth()->user()->hasRole('admin'))
        <a href="{{ route('management.class-rooms.create') }}" class="btn btn-pastel-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Rombel
        </a>
    @endif
</div>

<!-- Filters -->
<div class="card p-3 mb-3">
    <form method="GET" action="{{ route('management.class-rooms.index') }}" class="row g-2 align-items-center">
        <div class="col-md-3">
            <label class="form-label small mb-1">Filter Tahun Ajaran</label>
            <select name="academic_year_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Tahun Ajaran</option>
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                        {{ $year->name }} ({{ ucfirst($year->semester) }}) {{ $year->is_active ? '★ Aktif' : '' }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1">Filter Jurusan</label>
            <select name="major_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Jurusan</option>
                @foreach($majors as $major)
                    <option value="{{ $major->id }}" {{ request('major_id') == $major->id ? 'selected' : '' }}>
                        {{ $major->code }} - {{ $major->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">Filter Tingkat</label>
            <select name="level" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Tingkat</option>
                <option value="X" {{ request('level') === 'X' ? 'selected' : '' }}>Tingkat X</option>
                <option value="XI" {{ request('level') === 'XI' ? 'selected' : '' }}>Tingkat XI</option>
                <option value="XII" {{ request('level') === 'XII' ? 'selected' : '' }}>Tingkat XII</option>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <a href="{{ route('management.class-rooms.index') }}" class="btn btn-sm btn-light w-100">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
            </a>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Tingkat</th>
                    <th>Nama Rombel</th>
                    <th>Jurusan</th>
                    <th>Tahun Ajaran</th>
                    <th>Jumlah Murid</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($classRooms as $classRoom)
                    <tr>
                        <td>
                            <span class="badge badge-pastel-primary px-2">{{ $classRoom->level }}</span>
                        </td>
                        <td class="fw-bold">{{ $classRoom->name }}</td>
                        <td>
                            <span class="badge badge-pastel-accent me-1">{{ $classRoom->major?->code }}</span>
                            <span class="small">{{ $classRoom->major?->name }}</span>
                        </td>
                        <td>
                            {{ $classRoom->academicYear?->name }}
                            @if($classRoom->academicYear?->is_active)
                                <span class="badge badge-pastel-success ms-1">Aktif</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('management.students.index', ['class_room_id' => $classRoom->id]) }}" class="badge bg-light text-dark text-decoration-none">
                                <i class="bi bi-people me-1"></i> {{ $classRoom->students_count }} Siswa
                            </a>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('management.students.index', ['class_room_id' => $classRoom->id]) }}" class="btn btn-sm btn-light" title="Lihat Murid">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if(auth()->user()->hasRole('admin'))
                                    <a href="{{ route('management.class-rooms.edit', $classRoom) }}" class="btn btn-sm btn-light" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    @if($classRoom->students_count == 0)
                                        <form action="{{ route('management.class-rooms.destroy', $classRoom) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus rombel ini?');">
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
                        <td colspan="6" class="text-center py-4 text-muted">Tidak ada data rombel yang cocok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($classRooms->hasPages())
        <div class="card-footer d-flex justify-content-end py-2">
            {{ $classRooms->links() }}
        </div>
    @endif
</div>
@endsection
