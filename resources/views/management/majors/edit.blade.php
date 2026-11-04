@extends('layouts.app')

@section('title', 'Edit Jurusan')
@section('header_title', 'Edit Jurusan')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card p-4">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h6 class="fw-bold mb-0">Edit Jurusan: {{ $major->code }}</h6>
                <a href="{{ route('management.majors.index') }}" class="btn btn-sm btn-light">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <form action="{{ route('management.majors.update', $major) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="code" class="form-label">Kode Jurusan <span class="text-danger">*</span></label>
                    <input type="text" name="code" id="code" class="form-control text-uppercase @error('code') is-invalid @enderror" value="{{ old('code', $major->code) }}" required>
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label">Nama Jurusan <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $major->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="description" class="form-label">Keterangan / Deskripsi</label>
                    <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $major->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('management.majors.index') }}" class="btn btn-light">Batal</a>
                    <button type="submit" class="btn btn-pastel-primary">
                        <i class="bi bi-save me-1"></i> Perbarui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
