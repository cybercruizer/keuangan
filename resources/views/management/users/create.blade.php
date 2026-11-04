@extends('layouts.app')

@section('title', 'Tambah User')
@section('header_title', 'Tambah Pengguna Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card p-4">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h6 class="fw-bold mb-0">Form Tambah User</h6>
                <a href="{{ route('management.users.index') }}" class="btn btn-sm btn-light">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <form action="{{ route('management.users.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" placeholder="Nama pengguna" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" id="username" class="form-control @error('username') is-invalid @enderror" placeholder="Username (alfanumerik)" value="{{ old('username') }}" required>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">Alamat Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" placeholder="email@contoh.com" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="phone" class="form-label">No. Telepon / HP</label>
                        <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="08xxxxxxxxxx" value="{{ old('phone') }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimal 8 karakter" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="role" class="form-label">Peran / Role <span class="text-danger">*</span></label>
                        <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" onchange="toggleStudentSelect(this.value)" required>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin (Administrator)</option>
                            <option value="teller" {{ old('role') === 'teller' ? 'selected' : '' }}>Teller / Kasir</option>
                            <option value="kepsek" {{ old('role') === 'kepsek' ? 'selected' : '' }}>Kepala Sekolah</option>
                            <option value="murid" {{ old('role') === 'murid' ? 'selected' : '' }}>Murid</option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12" id="student_id_wrapper" style="display: {{ old('role') === 'murid' ? 'block' : 'none' }};">
                        <label for="student_id" class="form-label">Tautkan ke Data Siswa <span class="text-danger">*</span></label>
                        <select name="student_id" id="student_id" class="form-select @error('student_id') is-invalid @enderror">
                            <option value="">-- Pilih Siswa Terkait --</option>
                            @foreach($students as $st)
                                <option value="{{ $st->id }}" {{ old('student_id') == $st->id ? 'selected' : '' }}>
                                    {{ $st->nis }} - {{ $st->name }} ({{ $st->classRoom?->name ?: 'Tanpa Rombel' }})
                                </option>
                            @endforeach
                        </select>
                        @error('student_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('management.users.index') }}" class="btn btn-light">Batal</a>
                    <button type="submit" class="btn btn-pastel-primary">
                        <i class="bi bi-save me-1"></i> Simpan User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleStudentSelect(role) {
        const wrapper = document.getElementById('student_id_wrapper');
        const select = document.getElementById('student_id');
        if (role === 'murid') {
            wrapper.style.display = 'block';
            select.required = true;
        } else {
            wrapper.style.display = 'none';
            select.required = false;
        }
    }
</script>
@endsection
