@extends('layouts.app')

@section('title', 'Tambah Murid')
@section('header_title', 'Tambah Murid Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card p-4">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h6 class="fw-bold mb-0">Form Registrasi Murid</h6>
                <a href="{{ route('management.students.index') }}" class="btn btn-sm btn-light">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <form action="{{ route('management.students.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nis" class="form-label">NIS (Nomor Induk Siswa) <span class="text-danger">*</span></label>
                        <input type="text" name="nis" id="nis" class="form-control @error('nis') is-invalid @enderror" placeholder="Contoh: 10209" value="{{ old('nis') }}" required>
                        @error('nis')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="nisn" class="form-label">NISN (Nasional)</label>
                        <input type="text" name="nisn" id="nisn" class="form-control @error('nisn') is-invalid @enderror" placeholder="Nomor NISN 10 digit" value="{{ old('nisn') }}">
                        @error('nisn')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-8">
                        <label for="name" class="form-label">Nama Lengkap Murid <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" placeholder="Nama lengkap siswa" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="gender" class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                        <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror" required>
                            <option value="L" {{ old('gender') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('gender') === 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        @error('gender')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="class_room_id" class="form-label">Rombel / Kelas</label>
                        <select name="class_room_id" id="class_room_id" class="form-select @error('class_room_id') is-invalid @enderror">
                            <option value="">-- Belum Ditentukan --</option>
                            @foreach($classRooms as $cr)
                                <option value="{{ $cr->id }}" {{ old('class_room_id') == $cr->id ? 'selected' : '' }}>
                                    {{ $cr->name }} ({{ $cr->major?->name }}) - TA {{ $cr->academicYear?->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('class_room_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="category" class="form-label">Kategori Siswa <span class="text-danger">*</span></label>
                        <select name="category" id="category" class="form-select @error('category') is-invalid @enderror" required>
                            <option value="reguler" {{ old('category') === 'reguler' ? 'selected' : '' }}>Reguler (Biaya Penuh)</option>
                            <option value="beasiswa_50" {{ old('category') === 'beasiswa_50' ? 'selected' : '' }}>Beasiswa 50% (Potongan 50%)</option>
                            <option value="beasiswa_100" {{ old('category') === 'beasiswa_100' ? 'selected' : '' }}>Beasiswa 100% (Bebas Biaya)</option>
                        </select>
                        <small class="text-muted d-block mt-1">Digunakan untuk diskon otomatis saat tagihan dibuat.</small>
                        @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="phone" class="form-label">No. Telepon / WhatsApp Siswa</label>
                        <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="08xxxxxxxxxx" value="{{ old('phone') }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="status" class="form-label">Status Siswa <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="aktif" {{ old('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="lulus" {{ old('status') === 'lulus' ? 'selected' : '' }}>Lulus</option>
                            <option value="mutasi" {{ old('status') === 'mutasi' ? 'selected' : '' }}>Mutasi</option>
                            <option value="keluar" {{ old('status') === 'keluar' ? 'selected' : '' }}>Keluar</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="parent_name" class="form-label">Nama Orang Tua / Wali</label>
                        <input type="text" name="parent_name" id="parent_name" class="form-control @error('parent_name') is-invalid @enderror" placeholder="Nama orang tua atau wali" value="{{ old('parent_name') }}">
                        @error('parent_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="parent_phone" class="form-label">No. Telepon Orang Tua</label>
                        <input type="text" name="parent_phone" id="parent_phone" class="form-control @error('parent_phone') is-invalid @enderror" placeholder="08xxxxxxxxxx" value="{{ old('parent_phone') }}">
                        @error('parent_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="address" class="form-label">Alamat Lengkap</label>
                        <textarea name="address" id="address" rows="2" class="form-control @error('address') is-invalid @enderror" placeholder="Alamat domisili siswa...">{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('management.students.index') }}" class="btn btn-light">Batal</a>
                    <button type="submit" class="btn btn-pastel-primary">
                        <i class="bi bi-save me-1"></i> Simpan Murid
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
