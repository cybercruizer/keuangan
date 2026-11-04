@extends('layouts.app')

@section('title', 'Tambah Jenis Tagihan')
@section('header_title', 'Tambah Jenis Tagihan')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card p-4">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <h6 class="fw-bold mb-0">Form Tambah Jenis Tagihan</h6>
                <a href="{{ route('billing.bill-types.index') }}" class="btn btn-sm btn-light">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <form action="{{ route('billing.bill-types.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Nama Tagihan <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" placeholder="Contoh: SPP Bulanan, Uang Gedung" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="type" class="form-label">Tipe Tagihan <span class="text-danger">*</span></label>
                        <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                            <option value="spp" {{ old('type') === 'spp' ? 'selected' : '' }}>SPP (Bulanan per periode)</option>
                            <option value="custom" {{ old('type') === 'custom' ? 'selected' : '' }}>Tagihan Lain (Bisa dicicil)</option>
                            <option value="tabungan" {{ old('type') === 'tabungan' ? 'selected' : '' }}>Tabungan Siswa</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="amount" class="form-label">Nominal Standar (Rp) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">Rp</span>
                            <input type="number" step="1000" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" placeholder="250000" value="{{ old('amount', 0) }}" required>
                        </div>
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Deskripsi / Keterangan</label>
                    <textarea name="description" id="description" rows="2" class="form-control @error('description') is-invalid @enderror" placeholder="Keterangan alokasi tagihan...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Discount Section -->
                <div class="card p-3 mb-4" style="background-color: var(--pastel-bg); border: 1px dashed var(--pastel-border);">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_discountable" id="is_discountable" value="1" {{ old('is_discountable') ? 'checked' : '' }} onchange="toggleDiscountInputs(this.checked)">
                        <label class="form-check-label fw-bold" for="is_discountable">
                            Aktifkan Hak Diskon Beasiswa untuk Jenis Tagihan Ini
                        </label>
                    </div>
                    <small class="text-muted d-block mb-3">Jika dicentang, siswa berkategori Beasiswa 50% dan Beasiswa 100% akan otomatis mendapat potongan saat tagihan digenerate.</small>

                    <div id="discount_rules_box" style="display: {{ old('is_discountable') ? 'block' : 'none' }};">
                        <div class="row g-2">
                            <div class="col-sm-6">
                                <label for="discount_50" class="form-label small fw-semibold">Diskon Siswa Beasiswa 50%</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="1" min="0" max="100" name="discount_50" id="discount_50" class="form-control" value="{{ old('discount_50', 50) }}">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label for="discount_100" class="form-label small fw-semibold">Diskon Siswa Beasiswa 100%</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="1" min="0" max="100" name="discount_100" id="discount_100" class="form-control" value="{{ old('discount_100', 100) }}">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('billing.bill-types.index') }}" class="btn btn-light">Batal</a>
                    <button type="submit" class="btn btn-pastel-primary">
                        <i class="bi bi-save me-1"></i> Simpan Jenis Tagihan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleDiscountInputs(checked) {
        document.getElementById('discount_rules_box').style.display = checked ? 'block' : 'none';
    }
</script>
@endsection
