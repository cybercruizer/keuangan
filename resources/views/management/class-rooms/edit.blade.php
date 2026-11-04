@extends('layouts.app')

@section('title', 'Edit Rombel')
@section('header_title', 'Edit Rombel')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card p-4">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h6 class="fw-bold mb-0">Edit Rombel: {{ $classRoom->name }}</h6>
                <a href="{{ route('management.class-rooms.index') }}" class="btn btn-sm btn-light">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <form action="{{ route('management.class-rooms.update', $classRoom) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="academic_year_id" class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                    <select name="academic_year_id" id="academic_year_id" class="form-select @error('academic_year_id') is-invalid @enderror" required>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ old('academic_year_id', $classRoom->academic_year_id) == $year->id ? 'selected' : '' }}>
                                {{ $year->name }} ({{ ucfirst($year->semester) }}) {{ $year->is_active ? '★ Aktif' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('academic_year_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="major_id" class="form-label">Jurusan / Kompetensi Keahlian <span class="text-danger">*</span></label>
                    <select name="major_id" id="major_id" class="form-select @error('major_id') is-invalid @enderror" required>
                        @foreach($majors as $major)
                            <option value="{{ $major->id }}" {{ old('major_id', $classRoom->major_id) == $major->id ? 'selected' : '' }}>
                                {{ $major->code }} - {{ $major->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('major_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="level" class="form-label">Tingkat Kelas <span class="text-danger">*</span></label>
                    <select name="level" id="level" class="form-select @error('level') is-invalid @enderror" required>
                        <option value="X" {{ old('level', $classRoom->level) === 'X' ? 'selected' : '' }}>Tingkat X (Sepuluh)</option>
                        <option value="XI" {{ old('level', $classRoom->level) === 'XI' ? 'selected' : '' }}>Tingkat XI (Sebelas)</option>
                        <option value="XII" {{ old('level', $classRoom->level) === 'XII' ? 'selected' : '' }}>Tingkat XII (Dua Belas)</option>
                    </select>
                    @error('level')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="name" class="form-label">Nama Rombel <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $classRoom->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('management.class-rooms.index') }}" class="btn btn-light">Batal</a>
                    <button type="submit" class="btn btn-pastel-primary">
                        <i class="bi bi-save me-1"></i> Perbarui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
