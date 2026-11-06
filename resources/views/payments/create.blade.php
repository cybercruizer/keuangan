@extends('layouts.app')

@section('title', 'Transaksi Pembayaran Kasir')
@section('header_title', 'Form Pembayaran Siswa')

@section('content')
<div class="row g-3">
    <!-- Student Selector & Snapshot Card -->
    <div class="col-lg-4">
        <!-- Student Search Suggestion Card -->
        <div class="card p-3 mb-3">
            <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                <h6 class="fw-bold mb-0">1. Cari & Pilih Siswa</h6>
                @if($selectedStudent)
                    <a href="{{ route('payments.create') }}" class="btn btn-sm btn-light text-danger py-0 px-2" style="font-size: 0.78rem;" title="Reset pilihan siswa">
                        <i class="bi bi-x-circle me-1"></i> Ganti Siswa
                    </a>
                @endif
            </div>

            <div class="student-search-container position-relative" id="studentSearchContainer">
                <label for="student_search_input" class="form-label small text-muted mb-1">
                    Cari Nama / NIS / NISN Murid:
                </label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted">
                        <i class="bi bi-search" id="searchIcon"></i>
                        <span class="spinner-border spinner-border-sm text-pastel-primary d-none" id="searchSpinner" role="status" style="width: 0.9rem; height: 0.9rem;"></span>
                    </span>
                    <input type="text"
                           id="student_search_input"
                           class="form-control border-start-0"
                           placeholder="Ketik nama atau NIS murid..."
                           value="{{ $selectedStudent ? $selectedStudent->nis . ' - ' . $selectedStudent->name : '' }}"
                           autocomplete="off">
                    @if($selectedStudent)
                        <a href="{{ route('payments.create') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center" title="Hapus pilihan">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>

                <!-- Suggestions dropdown results -->
                <div id="studentSuggestionsMenu" class="student-suggestions-menu" style="display: none;">
                    <div id="studentSuggestionsList">
                        <!-- Populated dynamically via JS -->
                    </div>
                </div>

                <div class="mt-2 text-muted" style="font-size: 0.74rem;">
                    <i class="bi bi-lightning-charge me-1 text-warning"></i>Ketik minimal 1 karakter untuk menampilkan saran nama siswa secara otomatis.
                </div>
            </div>
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
                <p class="small mb-0">Ketik nama atau NIS siswa pada kolom pencarian di atas untuk memilih siswa dan memuat tagihannya.</p>
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
        const totalPaymentEl = document.getElementById('display_total_payment');
        if (totalPaymentEl) {
            totalPaymentEl.innerText = 'Rp ' + sum.toLocaleString('id-ID');
        }

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
        const displayChangeEl = document.getElementById('display_change');
        if (displayChangeEl) {
            displayChangeEl.innerText = 'Rp ' + change.toLocaleString('id-ID');
        }
    }

    // Dynamic Student Search Suggestion
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('student_search_input');
        const suggestionsMenu = document.getElementById('studentSuggestionsMenu');
        const suggestionsList = document.getElementById('studentSuggestionsList');
        const searchIcon = document.getElementById('searchIcon');
        const searchSpinner = document.getElementById('searchSpinner');

        if (!searchInput || !suggestionsMenu || !suggestionsList) return;

        let debounceTimeout = null;
        let activeIndex = -1;
        let abortController = null;

        function showLoading() {
            if (searchIcon) searchIcon.classList.add('d-none');
            if (searchSpinner) searchSpinner.classList.remove('d-none');
        }

        function hideLoading() {
            if (searchSpinner) searchSpinner.classList.add('d-none');
            if (searchIcon) searchIcon.classList.remove('d-none');
        }

        function closeSuggestions() {
            suggestionsMenu.style.display = 'none';
            activeIndex = -1;
        }

        function escapeHtml(text) {
            if (!text) return '';
            return String(text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function renderSuggestions(students, query) {
            activeIndex = -1;

            if (!students || students.length === 0) {
                suggestionsList.innerHTML = `
                    <div class="p-3 text-center text-muted small">
                        <i class="bi bi-info-circle d-block mb-1 fs-5 text-secondary"></i>
                        Tidak ada siswa ditemukan untuk "<strong>${escapeHtml(query)}</strong>"
                    </div>
                `;
                suggestionsMenu.style.display = 'block';
                return;
            }

            let html = '';
            students.forEach((student, index) => {
                const isBeasiswa100 = student.category_label.includes('100');
                const isBeasiswa50 = student.category_label.includes('50');
                const badgeClass = isBeasiswa100 ? 'badge-pastel-accent' : (isBeasiswa50 ? 'badge-pastel-warning' : 'badge-pastel-primary');

                html += `
                    <div class="student-suggestion-item" data-index="${index}" data-url="${student.select_url}">
                        <div class="me-2 overflow-hidden">
                            <div class="fw-semibold text-truncate text-dark" style="font-size: 0.9rem;">${escapeHtml(student.name)}</div>
                            <small class="text-muted d-block" style="font-size: 0.74rem;">
                                NIS: <span class="fw-medium text-dark">${escapeHtml(student.nis || '-')}</span> &bull; 
                                Kelas: <span class="fw-medium text-dark">${escapeHtml(student.class_name)}</span>
                            </small>
                        </div>
                        <div class="flex-shrink-0">
                            <span class="badge ${badgeClass}" style="font-size: 0.68rem;">${escapeHtml(student.category_label)}</span>
                        </div>
                    </div>
                `;
            });

            suggestionsList.innerHTML = html;
            suggestionsMenu.style.display = 'block';

            suggestionsList.querySelectorAll('.student-suggestion-item').forEach(item => {
                item.addEventListener('click', function () {
                    const url = this.getAttribute('data-url');
                    if (url) {
                        window.location.href = url;
                    }
                });
            });
        }

        function fetchSuggestions(query) {
            showLoading();

            if (abortController) {
                abortController.abort();
            }
            abortController = new AbortController();

            const searchUrl = `{{ route('payments.search-students') }}?q=${encodeURIComponent(query)}`;

            fetch(searchUrl, {
                signal: abortController.signal,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => {
                if (!res.ok) throw new Error('Network error');
                return res.json();
            })
            .then(data => {
                hideLoading();
                renderSuggestions(data, query);
            })
            .catch(err => {
                if (err.name === 'AbortError') return;
                hideLoading();
                suggestionsList.innerHTML = `
                    <div class="p-3 text-center text-danger small">
                        <i class="bi bi-exclamation-triangle me-1"></i> Gagal memuat pencarian data siswa.
                    </div>
                `;
                suggestionsMenu.style.display = 'block';
            });
        }

        searchInput.addEventListener('input', function () {
            const val = this.value.trim();
            clearTimeout(debounceTimeout);

            if (val.length === 0) {
                closeSuggestions();
                return;
            }

            debounceTimeout = setTimeout(() => {
                fetchSuggestions(val);
            }, 250);
        });

        searchInput.addEventListener('focus', function () {
            this.select();
            const val = this.value.trim();
            if (val.length > 0) {
                fetchSuggestions(val);
            } else {
                fetchSuggestions('');
            }
        });

        searchInput.addEventListener('keydown', function (e) {
            const items = suggestionsList.querySelectorAll('.student-suggestion-item');
            if (!items.length || suggestionsMenu.style.display === 'none') {
                return;
            }

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = (activeIndex + 1 >= items.length) ? 0 : activeIndex + 1;
                updateActiveHighlight(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = (activeIndex - 1 < 0) ? items.length - 1 : activeIndex - 1;
                updateActiveHighlight(items);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (activeIndex >= 0 && items[activeIndex]) {
                    items[activeIndex].click();
                } else if (items.length > 0) {
                    items[0].click();
                }
            } else if (e.key === 'Escape') {
                closeSuggestions();
            }
        });

        function updateActiveHighlight(items) {
            items.forEach((item, idx) => {
                if (idx === activeIndex) {
                    item.classList.add('active');
                    item.scrollIntoView({ block: 'nearest' });
                } else {
                    item.classList.remove('active');
                }
            });
        }

        document.addEventListener('click', function (e) {
            const container = document.getElementById('studentSearchContainer');
            if (container && !container.contains(e.target)) {
                closeSuggestions();
            }
        });
    });
</script>
@endsection
