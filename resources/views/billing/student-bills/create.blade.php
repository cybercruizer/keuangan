@extends('layouts.app')

@section('title', 'Terapkan / Generate Tagihan')
@section('header_title', 'Terapkan Tagihan ke Siswa')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card p-4">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <div>
                    <h6 class="fw-bold mb-0">Form Penerapan Tagihan Baru</h6>
                    <small class="text-muted">Buat tagihan massal untuk rombel dan kategori murid tertentu secara otomatis.</small>
                </div>
                <a href="{{ route('billing.student-bills.index') }}" class="btn btn-sm btn-light">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <form action="{{ route('billing.student-bills.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <!-- Bill Type -->
                    <div class="col-md-6">
                        <label for="bill_type_id" class="form-label">Jenis Tagihan <span class="text-danger">*</span></label>
                        <select name="bill_type_id" id="bill_type_id" class="form-select @error('bill_type_id') is-invalid @enderror" onchange="onBillTypeChange(this)" required>
                            <option value="">-- Pilih Jenis Tagihan --</option>
                            @foreach($billTypes as $bt)
                                <option value="{{ $bt->id }}"
                                        data-type="{{ $bt->type }}"
                                        data-amount="{{ (int) $bt->amount }}"
                                        data-discountable="{{ $bt->is_discountable ? '1' : '0' }}"
                                        {{ old('bill_type_id') == $bt->id ? 'selected' : '' }}>
                                    {{ $bt->name }} - Rp {{ number_format($bt->amount, 0, ',', '.') }} ({{ strtoupper($bt->type) }}) {{ $bt->is_discountable ? '[Bisa Diskon]' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('bill_type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Academic Year -->
                    <div class="col-md-6">
                        <label for="academic_year_id" class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                        <select name="academic_year_id" id="academic_year_id" class="form-select @error('academic_year_id') is-invalid @enderror" required>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ old('academic_year_id', $activeYear?->id) == $year->id ? 'selected' : '' }}>
                                    {{ $year->name }} ({{ ucfirst($year->semester) }}) {{ $year->is_active ? '★ Aktif' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('academic_year_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Amount Override -->
                    <div class="col-md-6">
                        <label for="amount" class="form-label">Nominal Tagihan (Rp) <small class="text-muted">(Biarkan kosong untuk nominal default)</small></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">Rp</span>
                            <input type="number" step="1000" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" placeholder="Sesuai nominal master jenis tagihan" value="{{ old('amount') }}">
                        </div>
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Due Date -->
                    <div class="col-md-6">
                        <label for="due_date" class="form-label">Tanggal Jatuh Tempo (Opsional)</label>
                        <input type="date" name="due_date" id="due_date" class="form-control @error('due_date') is-invalid @enderror" value="{{ old('due_date') }}">
                        @error('due_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Month Selection (For SPP) -->
                    <div class="col-12" id="months_section" style="display: none;">
                        <div class="card p-3" style="background-color: var(--pastel-bg); border: 1px solid var(--pastel-border);">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label fw-bold mb-0">Pilih Bulan Tagihan SPP <span class="text-danger">*</span></label>
                                <button type="button" class="btn btn-sm btn-light py-0 px-2" onclick="toggleAllMonths()">Pilih / Batal Semua</button>
                            </div>
                            <div class="row g-2">
                                @foreach($months as $num => $name)
                                    <div class="col-6 col-sm-4 col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input month-check" type="checkbox" name="months[]" id="month_{{ $num }}" value="{{ $num }}" {{ in_array($num, old('months', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="month_{{ $num }}">
                                                {{ $name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="row mt-2">
                                <div class="col-sm-4">
                                    <label for="year" class="form-label small text-muted mb-1">Tahun Kalender SPP:</label>
                                    <input type="number" name="year" id="year" class="form-control form-control-sm" value="{{ old('year', date('Y')) }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Target Rombel Selection (Checkboxes) -->
                    <div class="col-12">
                        <div class="card p-3" style="background-color: #ffffff; border: 1px solid var(--pastel-border);">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div>
                                    <label class="form-label fw-bold mb-0">Terapkan pada Rombel Tertentu <span class="text-danger">*</span></label>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Centang rombel yang akan diterbitkan tagihannya.</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-light py-0 px-2" onclick="toggleAllClassRooms()">Pilih / Batal Semua</button>
                            </div>
                            <div class="row g-2">
                                @foreach($classRooms as $cr)
                                    <div class="col-6 col-sm-4 col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input cr-check" type="checkbox" name="class_room_ids[]" id="cr_{{ $cr->id }}" value="{{ $cr->id }}" {{ in_array($cr->id, old('class_room_ids', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="cr_{{ $cr->id }}">
                                                <span class="fw-semibold">{{ $cr->name }}</span>
                                                <small class="text-muted d-block" style="font-size: 0.7rem;">{{ $cr->major?->code }} &bull; {{ $cr->students_count ?? $cr->students()->count() }} murid</small>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('class_room_ids')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Target Student Categories (Checkboxes) -->
                    <div class="col-12">
                        <div class="card p-3" style="background-color: #ffffff; border: 1px solid var(--pastel-border);">
                            <div class="mb-2">
                                <label class="form-label fw-bold mb-0">Terapkan pada Kategori Murid Tertentu <span class="text-danger">*</span></label>
                                <small class="text-muted d-block" style="font-size: 0.75rem;">Diskon beasiswa otomatis dihitung sesuai aturan diskon jenis tagihan.</small>
                            </div>
                            <div class="row g-2">
                                <div class="col-sm-4">
                                    <div class="p-2 rounded border">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="student_categories[]" id="cat_reguler" value="reguler" {{ in_array('reguler', old('student_categories', ['reguler', 'beasiswa_50', 'beasiswa_100'])) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold small" for="cat_reguler">
                                                <span class="badge badge-pastel-primary me-1">Reguler</span>
                                                <span>Tarif Penuh (100%)</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="p-2 rounded border">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="student_categories[]" id="cat_beasiswa_50" value="beasiswa_50" {{ in_array('beasiswa_50', old('student_categories', ['reguler', 'beasiswa_50', 'beasiswa_100'])) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold small" for="cat_beasiswa_50">
                                                <span class="badge badge-pastel-warning me-1">Beasiswa 50%</span>
                                                <span>Potongan 50%</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="p-2 rounded border">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="student_categories[]" id="cat_beasiswa_100" value="beasiswa_100" {{ in_array('beasiswa_100', old('student_categories', ['reguler', 'beasiswa_50', 'beasiswa_100'])) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold small" for="cat_beasiswa_100">
                                                <span class="badge badge-pastel-accent me-1">Beasiswa 100%</span>
                                                <span>Bebas Biaya (0)</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @error('student_categories')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('billing.student-bills.index') }}" class="btn btn-light">Batal</a>
                    <button type="submit" class="btn btn-pastel-primary">
                        <i class="bi bi-magic me-1"></i> Proses Penerapan Tagihan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function onBillTypeChange(select) {
        const option = select.options[select.selectedIndex];
        const type = option.getAttribute('data-type');
        const monthsSection = document.getElementById('months_section');

        if (type === 'spp') {
            monthsSection.style.display = 'block';
        } else {
            monthsSection.style.display = 'none';
        }
    }

    function toggleAllMonths() {
        const checkboxes = document.querySelectorAll('.month-check');
        const allChecked = Array.from(checkboxes).every(c => c.checked);
        checkboxes.forEach(c => c.checked = !allChecked);
    }

    function toggleAllClassRooms() {
        const checkboxes = document.querySelectorAll('.cr-check');
        const allChecked = Array.from(checkboxes).every(c => c.checked);
        checkboxes.forEach(c => c.checked = !allChecked);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const select = document.getElementById('bill_type_id');
        if (select && select.value) {
            onBillTypeChange(select);
        }
    });
</script>
@endsection
