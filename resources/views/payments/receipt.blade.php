<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuitansi {{ $payment->invoice_number }} - {{ $payment->student?->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @page {
            size: A5 landscape;
            margin: 10mm 15mm;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                color: #000000 !important;
                font-size: 11pt;
            }
            .receipt-container {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                width: 100% !important;
            }
            .table-receipt th {
                background-color: #f1f5f9 !important;
                color: #000000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        body {
            background-color: var(--pastel-bg);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            color: #1e293b;
        }

        .receipt-container {
            max-width: 210mm;
            min-height: 148mm;
            margin: 20px auto;
            background: #ffffff;
            border: 1px solid var(--pastel-border);
            border-radius: 8px;
            padding: 20px 28px;
        }

        .kop-sekolah {
            border-bottom: 2px solid #1e293b;
            padding-bottom: 8px;
            margin-bottom: 12px;
            position: relative;
        }

        .kop-logo {
            width: 58px;
            height: 58px;
            border-radius: 50%;
            background-color: var(--pastel-primary-light);
            color: var(--pastel-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
        }

        .table-receipt {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 0.88rem;
        }

        .table-receipt th, .table-receipt td {
            border: 1px solid #cbd5e1;
            padding: 6px 10px;
        }

        .table-receipt th {
            background-color: #f8fafc;
            font-weight: 600;
        }

        .terbilang-box {
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 6px;
            padding: 8px 12px;
            font-style: italic;
            font-size: 0.85rem;
        }

        .signature-box {
            margin-top: 15px;
            font-size: 0.82rem;
        }
    </style>
</head>
<body>
    <!-- Top Action Bar (Screen Only) -->
    <div class="no-print bg-white border-bottom py-2 px-4 shadow-sm mb-3">
        <div class="container-fluid d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('payments.index') }}" class="btn btn-sm btn-light">
                    <i class="bi bi-arrow-left me-1"></i> Riwayat Pembayaran
                </a>
                <span class="text-muted">|</span>
                <span class="fw-semibold small">Kuitansi Pembayaran #{{ $payment->invoice_number }}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('payments.create', ['student_id' => $payment->student_id]) }}" class="btn btn-sm btn-pastel-primary-light">
                    <i class="bi bi-plus-circle me-1"></i> Transaksi Siswa Ini Lagi
                </a>
                <button type="button" class="btn btn-sm btn-pastel-primary px-3" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Cetak Kuitansi (A5 Landscape)
                </button>
            </div>
        </div>
    </div>

    <!-- Official A5 Landscape Receipt Container -->
    <div class="receipt-container">
        <!-- Kop Surat Sekolah -->
        <div class="kop-sekolah d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <div class="kop-logo">
                    <i class="bi bi-bank"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0 text-uppercase" style="letter-spacing: 0.04em;">{{ $schoolName }}</h5>
                    <small class="text-muted d-block" style="font-size: 0.78rem;">{{ $schoolAddress }} &bull; Telp: {{ $schoolPhone }}</small>
                    <small class="text-muted" style="font-size: 0.72rem;">Sistem Informasi Keuangan Siswa Terpadu</small>
                </div>
            </div>
            <div class="text-end">
                <span class="d-block fw-bold fs-6 text-uppercase" style="letter-spacing: 0.05em; color: var(--pastel-primary);">BUKTI PEMBAYARAN</span>
                <span class="badge bg-light text-dark font-monospace border" style="font-size: 0.82rem;">{{ $payment->invoice_number }}</span>
            </div>
        </div>

        <!-- Meta Identitas Siswa & Transaksi -->
        <div class="row g-2 mb-2" style="font-size: 0.85rem;">
            <div class="col-7">
                <table class="table-borderless w-100">
                    <tr>
                        <td style="width: 110px;" class="text-muted">Nama Siswa</td>
                        <td style="width: 10px;">:</td>
                        <td class="fw-bold">{{ $payment->student?->name }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">NIS / NISN</td>
                        <td>:</td>
                        <td>{{ $payment->student?->nis }} / {{ $payment->student?->nisn ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Kelas / Jurusan</td>
                        <td>:</td>
                        <td>{{ $payment->student?->classRoom?->name ?: '-' }} ({{ $payment->student?->classRoom?->major?->name ?: '-' }})</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Kategori</td>
                        <td>:</td>
                        <td>
                            <span class="fw-medium">{{ $payment->student?->category_label }}</span>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-5">
                <table class="table-borderless w-100">
                    <tr>
                        <td style="width: 120px;" class="text-muted">Tanggal Bayar</td>
                        <td style="width: 10px;">:</td>
                        <td class="fw-bold">{{ $payment->payment_date->format('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Metode Bayar</td>
                        <td>:</td>
                        <td>{{ strtoupper($payment->payment_method) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Kasir / Teller</td>
                        <td>:</td>
                        <td>{{ $payment->user?->name ?: 'Kasir' }}</td>
                    </tr>
                    @if($payment->notes)
                        <tr>
                            <td class="text-muted">Keterangan</td>
                            <td>:</td>
                            <td>{{ $payment->notes }}</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- Tabel Rincian Pembayaran -->
        <table class="table-receipt">
            <thead>
                <tr>
                    <th style="width: 35px;" class="text-center">No</th>
                    <th>Uraian / Jenis Tagihan</th>
                    <th>Periode</th>
                    <th class="text-end">Tarif Normal</th>
                    <th class="text-end">Potongan Beasiswa</th>
                    <th class="text-end">Jumlah Dibayar</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payment->items as $idx => $item)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="fw-semibold">
                            {{ $item->studentBill?->billType?->name ?: $item->notes }}
                        </td>
                        <td>
                            @if($item->studentBill?->month)
                                {{ $item->studentBill?->month_name }} {{ $item->studentBill?->year }}
                            @else
                                TA {{ $item->studentBill?->academicYear?->name ?: '-' }}
                            @endif
                        </td>
                        <td class="text-end text-muted">
                            Rp {{ number_format($item->studentBill?->amount ?: $item->amount, 0, ',', '.') }}
                        </td>
                        <td class="text-end text-muted">
                            @if($item->studentBill && $item->studentBill->discount_amount > 0)
                                Rp {{ number_format($item->studentBill->discount_amount, 0, ',', '.') }} ({{ number_format($item->studentBill->discount_percentage, 0) }}%)
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-end fw-bold">
                            Rp {{ number_format($item->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="5" class="text-end fw-bold">TOTAL PEMBAYARAN :</th>
                    <th class="text-end fw-bold fs-6" style="color: var(--pastel-primary-text);">
                        Rp {{ number_format($payment->total_amount, 0, ',', '.') }}
                    </th>
                </tr>
            </tfoot>
        </table>

        <!-- Terbilang Box -->
        <div class="terbilang-box mb-3">
            <span class="fw-semibold text-muted">Terbilang:</span>
            <span class="fw-bold text-dark"># {{ $terbilang }} #</span>
        </div>

        <!-- Kolom Tanda Tangan -->
        <div class="signature-box d-flex justify-content-between text-center">
            <div style="width: 200px;">
                <p class="mb-5 text-muted">Penyetor / Murid</p>
                <div class="border-bottom border-dark pb-1 fw-bold">{{ $payment->student?->name }}</div>
                <small class="text-muted">NIS: {{ $payment->student?->nis }}</small>
            </div>

            <div style="width: 220px;">
                <p class="mb-5 text-muted">
                    Mungkid, {{ $payment->payment_date->format('d/m/Y') }}<br>
                    Petugas Kasir / Teller
                </p>
                <div class="border-bottom border-dark pb-1 fw-bold">{{ $payment->user?->name ?: 'Teller Keuangan' }}</div>
                <small class="text-muted">NIP/ID: {{ $payment->user?->username ?: '-' }}</small>
            </div>
        </div>

        <div class="text-center mt-3 pt-2 text-muted" style="font-size: 0.68rem; border-top: 1px dotted #cbd5e1;">
            * Simpanlah bukti pembayaran kuitansi ini sebagai bukti sah pembayaran biaya sekolah di SMK Muhammadiyah Mungkid.
        </div>
    </div>
</body>
</html>
