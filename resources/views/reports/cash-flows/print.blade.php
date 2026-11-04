<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Arus Kas - {{ $periodLabel }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                color: #000000 !important;
                font-size: 10pt;
            }
            .report-container {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                width: 100% !important;
            }
            .table-report th {
                background-color: #f1f5f9 !important;
                color: #000000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        body {
            background-color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            color: #1e293b;
        }

        .report-container {
            max-width: 210mm;
            margin: 20px auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 30px;
        }

        .kop-sekolah {
            border-bottom: 2px solid #1e293b;
            padding-bottom: 10px;
            margin-bottom: 16px;
        }

        .table-report {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 0.85rem;
        }

        .table-report th, .table-report td {
            border: 1px solid #cbd5e1;
            padding: 6px 10px;
        }

        .table-report th {
            background-color: #f8fafc;
            font-weight: 600;
        }

        .signature-section {
            margin-top: 30px;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="no-print bg-white border-bottom py-2 px-4 shadow-sm mb-3">
        <div class="container-fluid d-flex align-items-center justify-content-between">
            <span class="fw-semibold small">Laporan Arus Kas - {{ $periodLabel }}</span>
            <button type="button" class="btn btn-sm btn-pastel-primary px-3" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Cetak Dokumen
            </button>
        </div>
    </div>

    <div class="report-container">
        <!-- Kop Surat -->
        <div class="kop-sekolah text-center">
            <h4 class="fw-bold mb-1 text-uppercase">{{ $schoolName }}</h4>
            <p class="mb-0 text-muted small">{{ $schoolAddress }}</p>
            <p class="mb-0 text-muted small">Sistem Informasi Keuangan Siswa</p>
        </div>

        <div class="text-center mb-3">
            <h5 class="fw-bold mb-0 text-uppercase" style="letter-spacing: 0.05em;">LAPORAN ARUS KAS (CASH FLOW)</h5>
            <small class="text-muted fw-semibold">Periode: {{ $periodLabel }}</small>
        </div>

        <!-- Ringkasan Finansial -->
        <table class="table table-sm table-bordered mb-3" style="font-size: 0.85rem;">
            <tbody>
                <tr>
                    <td class="fw-semibold" style="width: 35%;">Total Kas Masuk (Pemasukan)</td>
                    <td class="fw-bold text-success text-end">Rp {{ number_format($totalIncome, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="fw-semibold">Total Kas Keluar (Pengeluaran)</td>
                    <td class="fw-bold text-danger text-end">Rp {{ number_format($totalExpense, 0, ',', '.') }}</td>
                </tr>
                <tr class="table-light">
                    <td class="fw-bold">Saldo Bersih (Surplus / Defisit)</td>
                    <td class="fw-bold {{ $netBalance >= 0 ? 'text-primary' : 'text-danger' }} text-end">
                        Rp {{ number_format($netBalance, 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Tabel Rincian -->
        <table class="table-report">
            <thead>
                <tr>
                    <th style="width: 35px;" class="text-center">No</th>
                    <th style="width: 85px;">Tanggal</th>
                    <th style="width: 80px;">Jenis</th>
                    <th style="width: 140px;">Kategori</th>
                    <th>Uraian / Keterangan</th>
                    <th class="text-end" style="width: 110px;">Masuk (Rp)</th>
                    <th class="text-end" style="width: 110px;">Keluar (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cashFlows as $idx => $cf)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>{{ $cf->date->format('d/m/Y') }}</td>
                        <td>{{ $cf->type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}</td>
                        <td>{{ $cf->category }}</td>
                        <td>{{ $cf->description }}</td>
                        <td class="text-end text-success">
                            {{ $cf->type === 'income' ? number_format($cf->amount, 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-end text-danger">
                            {{ $cf->type === 'expense' ? number_format($cf->amount, 0, ',', '.') : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-3 text-muted">Tidak ada transaksi pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="fw-bold" style="background-color: #f1f5f9;">
                    <td colspan="5" class="text-end">TOTAL KESELURUHAN :</td>
                    <td class="text-end text-success">Rp {{ number_format($totalIncome, 0, ',', '.') }}</td>
                    <td class="text-end text-danger">Rp {{ number_format($totalExpense, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Tanda Tangan -->
        <div class="signature-section d-flex justify-content-between text-center mt-5">
            <div style="width: 220px;">
                <p class="mb-5">
                    Mengetahui,<br>
                    Kepala Sekolah
                </p>
                <div class="border-bottom border-dark pb-1 fw-bold">{{ $headmasterName }}</div>
                <small class="text-muted">NIP. {{ $headmasterNip }}</small>
            </div>

            <div style="width: 220px;">
                <p class="mb-5">
                    Mungkid, {{ date('d F Y') }}<br>
                    Bendahara Sekolah
                </p>
                <div class="border-bottom border-dark pb-1 fw-bold">{{ $treasurerName }}</div>
                <small class="text-muted">Bendahara Keuangan</small>
            </div>
        </div>
    </div>
</body>
</html>
