@extends('layouts.app')

@section('title', 'Dashboard')
@section('header_title', 'Dashboard Keuangan Siswa')

@section('content')
<!-- Top KPI Cards -->
<div class="row g-3 mb-4">
    <!-- Today's Income -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--pastel-success-light); color: var(--pastel-success);">
                <i class="bi bi-cash-coin"></i>
            </div>
            <div>
                <small class="text-muted d-block" style="font-size: 0.75rem;">Penerimaan Hari Ini</small>
                <h5 class="fw-bold mb-0 text-success">Rp {{ number_format($todayIncome, 0, ',', '.') }}</h5>
                <small class="text-muted" style="font-size: 0.7rem;">{{ date('d M Y') }}</small>
            </div>
        </div>
    </div>

    <!-- Monthly Income -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--pastel-primary-light); color: var(--pastel-primary);">
                <i class="bi bi-wallet2"></i>
            </div>
            <div>
                <small class="text-muted d-block" style="font-size: 0.75rem;">Penerimaan Bulan Ini</small>
                <h5 class="fw-bold mb-0 text-dark">Rp {{ number_format($monthIncome, 0, ',', '.') }}</h5>
                <small class="text-muted" style="font-size: 0.7rem;">Bulan {{ date('F Y') }}</small>
            </div>
        </div>
    </div>

    <!-- Outstanding Bills -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--pastel-danger-light); color: var(--pastel-danger);">
                <i class="bi bi-exclamation-circle"></i>
            </div>
            <div>
                <small class="text-muted d-block" style="font-size: 0.75rem;">Total Sisa Tunggakan</small>
                <h5 class="fw-bold mb-0 text-danger">Rp {{ number_format($totalUnpaid, 0, ',', '.') }}</h5>
                <small class="text-muted" style="font-size: 0.7rem;">Tagihan belum lunas</small>
            </div>
        </div>
    </div>

    <!-- Overall Collection Rate -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background-color: var(--pastel-accent-light); color: var(--pastel-accent);">
                <i class="bi bi-pie-chart"></i>
            </div>
            <div>
                <small class="text-muted d-block" style="font-size: 0.75rem;">Tingkat Ketercapaian</small>
                <h5 class="fw-bold mb-0 text-primary">{{ $overallCollectionRate }}%</h5>
                <small class="text-muted" style="font-size: 0.7rem;">Rp {{ number_format($totalPaid, 0, ',', '.') }} terkumpul</small>
            </div>
        </div>
    </div>
</div>

<!-- Chart & Category Breakdown Row -->
<div class="row g-4 mb-4">
    <!-- Chart: Daily, Weekly, Monthly Payments -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-graph-up text-primary"></i>
                    <span class="fw-bold">Grafik Tren Pembayaran</span>
                </div>

                <!-- Tabs: Harian / Mingguan / Bulanan -->
                <ul class="nav nav-pills nav-fill gap-1" id="chartTabs" role="tablist" style="background-color: #f1f5f9; padding: 3px; border-radius: 8px;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active py-1 px-3 small" style="border-radius: 6px; font-size: 0.78rem;" id="tab-daily" type="button" onclick="switchChartMode('daily')">
                            Harian (14 Hari)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-1 px-3 small" style="border-radius: 6px; font-size: 0.78rem;" id="tab-weekly" type="button" onclick="switchChartMode('weekly')">
                            Mingguan (8 Minggu)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-1 px-3 small" style="border-radius: 6px; font-size: 0.78rem;" id="tab-monthly" type="button" onclick="switchChartMode('monthly')">
                            Bulanan (12 Bulan)
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <div style="position: relative; height: 280px; width: 100%;">
                    <canvas id="paymentChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Categories & Quick Stats -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="fw-bold">Distribusi Murid & Beasiswa</span>
                <span class="badge bg-light text-dark">{{ $totalStudents }} Murid</span>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Distribusi skema beasiswa di SMK Muhammadiyah Mungkid:</p>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-semibold">Reguler (Tarif Penuh)</span>
                        <span class="badge badge-pastel-primary">{{ $studentsByCategory['reguler'] }} Siswa</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar" style="background-color: var(--pastel-primary); width: {{ $totalStudents > 0 ? ($studentsByCategory['reguler'] / $totalStudents) * 100 : 0 }}%;"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-semibold">Beasiswa 50%</span>
                        <span class="badge badge-pastel-warning">{{ $studentsByCategory['beasiswa_50'] }} Siswa</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar" style="background-color: var(--pastel-warning); width: {{ $totalStudents > 0 ? ($studentsByCategory['beasiswa_50'] / $totalStudents) * 100 : 0 }}%;"></div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-semibold">Beasiswa 100% (Penuh)</span>
                        <span class="badge badge-pastel-accent">{{ $studentsByCategory['beasiswa_100'] }} Siswa</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar" style="background-color: var(--pastel-accent); width: {{ $totalStudents > 0 ? ($studentsByCategory['beasiswa_100'] / $totalStudents) * 100 : 0 }}%;"></div>
                    </div>
                </div>

                <div class="p-3 rounded" style="background-color: var(--pastel-bg); border: 1px dashed var(--pastel-border);">
                    <div class="d-flex justify-content-between py-1 small">
                        <span class="text-muted">Tahun Ajaran Aktif:</span>
                        <span class="fw-semibold">{{ $activeYear ? $activeYear->name : '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 small">
                        <span class="text-muted">Total Rombel:</span>
                        <span class="fw-semibold">{{ $totalClasses }} Kelas</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 small">
                        <span class="text-muted">Kompetensi Keahlian:</span>
                        <span class="fw-semibold">{{ $totalMajors }} Jurusan</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Progress Bar Pembayaran per Rombel Row -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <span class="fw-bold">Progress Pembayaran per Rombongan Belajar (Rombel)</span>
                    <small class="text-muted d-block" style="font-size: 0.75rem;">Persentase kelunasan tagihan yang diterbitkan pada masing-masing rombel.</small>
                </div>
                <a href="{{ route('billing.student-bills.index') }}" class="btn btn-sm btn-light py-1 px-2" style="font-size: 0.8rem;">
                    Kelola Tagihan <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @forelse($classRoomsProgress as $cr)
                        <div class="col-md-6 col-lg-4">
                            <div class="p-3 rounded border" style="background-color: #ffffff;">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark">{{ $cr['name'] }}</h6>
                                        <small class="text-muted" style="font-size: 0.72rem;">{{ $cr['major_code'] }} &bull; {{ $cr['students_count'] }} murid</small>
                                    </div>
                                    <span class="badge {{ $cr['percentage'] >= 80 ? 'badge-pastel-success' : ($cr['percentage'] >= 50 ? 'badge-pastel-accent' : 'badge-pastel-warning') }} fs-6">
                                        {{ $cr['percentage'] }}%
                                    </span>
                                </div>

                                <div class="progress mb-2" style="height: 9px; border-radius: 6px;">
                                    <div class="progress-bar"
                                         role="progressbar"
                                         style="width: {{ $cr['percentage'] }}%; background-color: {{ $cr['percentage'] >= 80 ? 'var(--pastel-success)' : ($cr['percentage'] >= 50 ? 'var(--pastel-accent)' : 'var(--pastel-warning)') }};"
                                         aria-valuenow="{{ $cr['percentage'] }}"
                                         aria-valuemin="0"
                                         aria-valuemax="100">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between small text-muted" style="font-size: 0.75rem;">
                                    <span>Lunas: <strong class="text-success">Rp {{ number_format($cr['total_paid'], 0, ',', '.') }}</strong></span>
                                    <span>Total: <strong>Rp {{ number_format($cr['total_billed'], 0, ',', '.') }}</strong></span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-4 text-muted">
                            Belum ada rombel terdaftar.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Payments Table -->
<div class="row g-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="fw-bold">Transaksi Pembayaran Kasir Terkini</span>
                @if(auth()->user()->hasRole(['admin', 'teller']))
                    <a href="{{ route('payments.create') }}" class="btn btn-sm btn-pastel-primary py-1 px-3" style="font-size: 0.8rem;">
                        <i class="bi bi-plus-lg me-1"></i> Bayar Tagihan Baru
                    </a>
                @endif
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No. Kuitansi</th>
                            <th>Tanggal</th>
                            <th>Nama Siswa & Rombel</th>
                            <th>Total Pembayaran</th>
                            <th>Metode</th>
                            <th>Kasir</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPayments as $rp)
                            <tr>
                                <td>
                                    <a href="{{ route('payments.receipt', $rp) }}" class="fw-bold text-dark font-monospace text-decoration-none">
                                        {{ $rp->invoice_number }}
                                    </a>
                                </td>
                                <td>{{ $rp->payment_date->format('d/m/Y') }}</td>
                                <td>
                                    <span class="fw-semibold">{{ $rp->student?->name }}</span>
                                    <small class="d-block text-muted">{{ $rp->student?->classRoom?->name ?: '-' }} ({{ $rp->student?->nis }})</small>
                                </td>
                                <td class="fw-bold text-success">
                                    Rp {{ number_format($rp->total_amount, 0, ',', '.') }}
                                </td>
                                <td>
                                    <span class="badge badge-pastel-primary text-uppercase">{{ $rp->payment_method }}</span>
                                </td>
                                <td class="text-muted small">{{ $rp->user?->name ?: '-' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('payments.receipt', $rp) }}" class="btn btn-sm btn-light py-1 px-2" title="Cetak Kuitansi">
                                        <i class="bi bi-printer me-1"></i> Cetak A5
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    Belum ada transaksi pembayaran tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const chartModes = {
        daily: {
            labels: @json($dailyChartData['labels']),
            values: @json($dailyChartData['values']),
            color: '#4E7D6B',
            bg: 'rgba(78, 125, 107, 0.15)',
            label: 'Penerimaan Harian (Rp)'
        },
        weekly: {
            labels: @json($weeklyChartData['labels']),
            values: @json($weeklyChartData['values']),
            color: '#5B8FB9',
            bg: 'rgba(91, 143, 185, 0.2)',
            label: 'Penerimaan Mingguan (Rp)'
        },
        monthly: {
            labels: @json($monthlyChartData['labels']),
            values: @json($monthlyChartData['values']),
            color: '#8E8BB8',
            bg: 'rgba(142, 139, 184, 0.25)',
            label: 'Penerimaan Bulanan (Rp)'
        }
    };

    let paymentChart = null;

    function renderChart(modeKey) {
        const data = chartModes[modeKey];
        const ctx = document.getElementById('paymentChart').getContext('2d');

        if (paymentChart) {
            paymentChart.destroy();
        }

        paymentChart = new window.Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: data.label,
                    data: data.values,
                    backgroundColor: data.bg,
                    borderColor: data.color,
                    borderWidth: 2,
                    borderRadius: 6,
                    maxBarThickness: 36,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + Number(context.raw).toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) {
                                    return (value / 1000000).toFixed(1) + ' Jt';
                                } else if (value >= 1000) {
                                    return (value / 1000).toFixed(0) + ' Rb';
                                }
                                return value;
                            },
                            font: {
                                size: 11
                            }
                        },
                        grid: {
                            color: '#f1f5f9'
                        }
                    }
                }
            }
        });
    }

    function switchChartMode(mode) {
        document.querySelectorAll('#chartTabs .nav-link').forEach(btn => btn.classList.remove('active'));
        document.getElementById('tab-' + mode).classList.add('active');
        renderChart(mode);
    }

    document.addEventListener('DOMContentLoaded', function() {
        renderChart('daily');
    });
</script>
@endpush
@endsection
