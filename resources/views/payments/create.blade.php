@extends('layouts.app')

@section('title', 'Transaksi Pembayaran Kasir')
@section('header_title', 'Form Pembayaran Siswa')

@section('content')
<div class="row g-3">
    <!-- Student Selector & Snapshot Card -->
    <div class="col-lg-4">
        <!-- Student Search / Selection -->
        <div class="card p-3 mb-3">
            <h6 class="fw-bold mb-2 pb-2 border-bottom">1. Pilih Siswa</h6>
            <form method="GET" action="{{ route('payments.create') }}">
                <div class="mb-3">
                    <label for="student_id_select" class="form-label small text-muted">Cari Nama / NIS Murid:</label>
                    <select name="student_id" id="student_id_select" class="form-select" onchange="this.form.submit()" required>
                        <option value="">-- Pilih Siswa --</option>
                        @foreach($students as $st)
                            <option value="{{ $st->id }}" {{ ($selectedStudent && $selectedStudent->id == $st->id) ? 'selected' : '' }}>
                                {{ $st->nis }} - {{ $st->name }} ({{ $st->classRoom?->name ?: 'Tanpa Kelas' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        @if($selectedStudent)
            <!-- Student Detail Card -->
            <div class="card p-3 mb-3">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="d-flex align-items-center justify-content-center rounded-circle" style="width: 54px; height: 54px; background-color: var(--pastel-primary-light); color: var(--pastel-primary); font-size: 1.5rem;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">{{ $selectedStudent->name }}</h6>
                        <small class="text-muted d-block">NIS: {{ $selectedStudent->nis }}</small>
                        <span class="badge {{ $selectedStudent->category === 'beasiswa_100' ? 'badge-pastel-accent' : ($selectedStudent->category === 'beasiswa_50' ? 'badge-pastel-warning' : 'badge-pastel-primary') }} mt-1">
                            {{ $selectedStudent->category_label }}
                        </span>
                    </div>
                </div>

                <div class="small border-top pt-2">
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted">Kelas / Rombel:</span>
                        <span class="fw-semibold">{{ $selectedStudent->classRoom?->name ?: '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted">Jurusan:</span>
                        <span class="fw-semibold">{{ $selectedStudent->classRoom?->major?->name ?: '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-muted">Saldo Tabungan:</span>
                        <span class="fw-bold text-success">Rp {{ number_format($selectedStudent->currentSavingsBalance(), 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        @else
            <div class="card p-4 text-center text-muted">
                <i class="bi bi-search fs-1 mb-2 opacity-50"></i>
                <p class="small mb-0">Silakan pilih siswa pada dropdown di atas untuk memuat tagihan tertunggak.</p>
            </div>
        @endif
    </div>

    <!-- Payment Bills & Checkout Form -->
    <div class="col-lg-8">
        @if($selectedStudent)
            <form action="{{ route('payments.store') }}" method="POST" id="paymentForm">
                @csrf
                <input type="hidden" name="student_id" value="{{ $selectedStudent->id }}">

                <div class="card mb-3">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <span>2. Pilih Tagihan yang Akan Dibayar</span>
                        <button type="button" class="btn btn-sm btn-light py-0 px-2" onclick="toggleAllBills()">Pilih Semua</button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">Pilih</th>
                                    <th>Jenis Tagihan & Periode</th>
                                    <th>Total Tagihan</th>
                                    <th>Sisa Bayar</th>
                                    <th style="width: 180px;">Jumlah Dibayar (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($unpaidBills as $index => $bill)
                                    <tr>
                                        <td>
                                            <input class="form-check-input bill-checkbox"
                                                   type="checkbox"
                                                   name="bills[{{ $index }}][selected]"
                                                   id="bill_check_{{ $bill->id }}"
                                                   value="1"
                                                   data-bill-id="{{ $bill->id }}"
                                                   data-remaining="{{ $bill->remainingAmount() }}"
                                                   onchange="onBillSelectionChange(this, {{ $bill->id }})">
                                            <input type="hidden" name="bills[{{ $index }}][bill_id]" value="{{ $bill->id }}">
                                        </td>
                                        <td>
                                            <label for="bill_check_{{ $bill->id }}" class="fw-semibold mb-0 d-block cursor-pointer">
                                                {{ $bill->billType?->name }}
                                                @if($bill->month)
                                                    <span class="badge badge-pastel-primary ms-1">{{ $bill->month_name }} {{ $bill->year }}</span>
                                                @else
                                                    <span class="badge bg-light text-dark ms-1">TA {{ $bill->academicYear?->name }}</span>
                                                @endif
                                            </label>
                                            @if($bill->discount_percentage > 0)
                                                <small class="text-success d-block" style="font-size: 0.72rem;">
                                                    Diskon Beasiswa: -{{ number_format($bill->discount_percentage, 0) }}% (Rp {{ number_format($bill->discount_amount, 0, ',', '.') }})
                                                </small>
                                            @endif
                                            @if($bill->status === 'partial')
                                                <small class="text-warning d-block" style="font-size: 0.72rem;">
                                                    <i class="bi bi-pie-chart"></i> Sudah dicicil Rp {{ number_format($bill->paid_amount, 0, ',', '.') }}
                                                </small>
                                            @endif
                                        </td>
                                        <td class="text-muted small">
                                            Rp {{ number_format($bill->final_amount, 0, ',', '.') }}
                                        </td>
                                        <td class="fw-bold text-danger">
                                            Rp {{ number_format($bill->remainingAmount(), 0, ',', '.') }}
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light">Rp</span>
                                                <input type="number"
                                                       step="1000"
                                                       min="1"
                                                       max="{{ $bill->remainingAmount() }}"
                                                       name="bills[{{ $index }}][pay_amount]"
                                                       id="pay_input_{{ $bill->id }}"
                                                       class="form-control pay-amount-input text-end fw-bold"
                                                       value="{{ (int) $bill->remainingAmount() }}"
                                                       data-remaining="{{ $bill->remainingAmount() }}"
                                                       oninput="recalcTotal()"
                                                       {{ $bill->billType?->type === 'spp' ? 'readonly' : '' }}>
                                            </div>
                                            @if($bill->billType?->type === 'custom')
                                                <small class="text-muted d-block" style="font-size: 0.68rem;">*Bisa dicicil sebagian</small>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-success">
                                            <i class="bi bi-check-circle fs-3 d-block mb-1"></i>
                                            Alhamdulillah, siswa ini tidak memiliki tunggakan tagihan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($unpaidBills->isNotEmpty())
                    <!-- Payment Details & Checkout Summary -->
                    <div class="card p-3 mb-3">
                        <h6 class="fw-bold mb-3 pb-2 border-bottom">3. Rincian Pembayaran Kasir</h6>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="payment_date" class="form-label small fw-semibold">Tanggal Pembayaran <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date" id="payment_date" class="form-control" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label for="payment_method" class="form-label small fw-semibold">Metode Pembayaran <span class="text-danger">*</span></label>
                                <select name="payment_method" id="payment_method" class="form-select" required>
                                    <option value="Tunai" {{ old('payment_method') === 'Tunai' ? 'selected' : '' }}>Tunai / Cash</option>
                                    <option value="Transfer Bank" {{ old('payment_method') === 'Transfer Bank' ? 'selected' : '' }}>Transfer Bank</option>
                                    <option value="QRIS" {{ old('payment_method') === 'QRIS' ? 'selected' : '' }}>QRIS</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label for="notes" class="form-label small fw-semibold">Catatan Transaksi (Opsional)</label>
                                <input type="text" name="notes" id="notes" class="form-control" placeholder="Contoh: Diterima dari orang tua siswa...">
                            </div>
                        </div>

                        <!-- Cashier Total & Change Calculation -->
                        <div class="p-3 rounded" style="background-color: var(--pastel-bg); border: 1px solid var(--pastel-border);">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-semibold">Total Tagihan Terpilih:</span>
                                <h4 class="fw-bold mb-0 text-success" id="display_total_payment">Rp 0</h4>
                            </div>

                            <div class="row g-2 align-items-center mt-2 pt-2 border-top">
                                <div class="col-sm-6">
                                    <label for="cash_given" class="form-label small mb-1 fw-semibold">Uang Diterima (Rp):</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white">Rp</span>
                                        <input type="number" step="1000" id="cash_given" class="form-control fw-bold text-end" placeholder="0" oninput="calcChange()">
                                    </div>
                                </div>
                                <div class="col-sm-6 text-end">
                                    <span class="small text-muted d-block">Kembalian:</span>
                                    <h5 class="fw-bold mb-0 text-primary" id="display_change">Rp 0</h5>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('payments.index') }}" class="btn btn-light">Batal</a>
                            <button type="submit" class="btn btn-pastel-primary py-2 px-4" id="submitPaymentBtn" disabled>
                                <i class="bi bi-printer me-1"></i> Simpan & Cetak Kuitansi (A5)
                            </button>
                        </div>
                    </div>
                @endif
            </form>
        @else
            <div class="card p-5 text-center text-muted">
                <i class="bi bi-wallet2 fs-1 mb-2 opacity-25"></i>
                <h5>Pilih Siswa Terlebih Dahulu</h5>
                <p class="small mb-0">Cari murid berdasarkan Nama atau NIS di panel sebelah kiri untuk memulai pembayaran.</p>
            </div>
        @endif
    </div>
</div>

<script>
    let currentTotal = 0;

    function onBillSelectionChange(checkbox, billId) {
        const input = document.getElementById('pay_input_' + billId);
        if (checkbox.checked) {
            if (!input.value || parseFloat(input.value) <= 0) {
                input.value = checkbox.getAttribute('data-remaining');
            }
        }
        recalcTotal();
    }

    function toggleAllBills() {
        const checkboxes = document.querySelectorAll('.bill-checkbox');
        const allChecked = Array.from(checkboxes).every(c => c.checked);
        checkboxes.forEach(c => {
            c.checked = !allChecked;
            const billId = c.getAttribute('data-bill-id');
            const input = document.getElementById('pay_input_' + billId);
            if (!allChecked) {
                input.value = c.getAttribute('data-remaining');
            }
        });
        recalcTotal();
    }

    function recalcTotal() {
        let sum = 0;
        const checkboxes = document.querySelectorAll('.bill-checkbox');
        let checkedCount = 0;

        checkboxes.forEach(c => {
            if (c.checked) {
                checkedCount++;
                const billId = c.getAttribute('data-bill-id');
                const input = document.getElementById('pay_input_' + billId);
                const val = parseFloat(input.value) || 0;
                const max = parseFloat(c.getAttribute('data-remaining')) || 0;

                // Clamp to max remaining
                if (val > max) {
                    input.value = max;
                    sum += max;
                } else if (val > 0) {
                    sum += val;
                }
            }
        });

        currentTotal = sum;
        document.getElementById('display_total_payment').innerText = 'Rp ' + sum.toLocaleString('id-ID');

        const submitBtn = document.getElementById('submitPaymentBtn');
        if (submitBtn) {
            submitBtn.disabled = (sum <= 0 || checkedCount === 0);
        }

        calcChange();
    }

    function calcChange() {
        const cashGivenInput = document.getElementById('cash_given');
        if (!cashGivenInput) return;

        const cashGiven = parseFloat(cashGivenInput.value) || 0;
        const change = Math.max(0, cashGiven - currentTotal);
        document.getElementById('display_change').innerText = 'Rp ' + change.toLocaleString('id-ID');
    }
</script>
@endsection
