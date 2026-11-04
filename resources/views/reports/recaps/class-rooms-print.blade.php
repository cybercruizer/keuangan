<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Pembayaran per Rombel - SMK Muhammadiyah Mungkid</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @page { size: A4 landscape; margin: 15mm; }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; font-size: 9pt; }
            .table-report th { background-color: #f1f5f9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        body { background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; }
        .report-container { max-width: 297mm; margin: 20px auto; background: #fff; padding: 25px; border: 1px solid #e2e8f0; border-radius: 8px; }
        .kop-sekolah { border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 16px; }
        .table-report { width: 100%; border-collapse: collapse; margin: 12px 0; font-size: 0.85rem; }
        .table-report th, .table-report td { border: 1px solid #cbd5e1; padding: 6px 10px; }
        .table-report th { background-color: #f8fafc; }
    </style>
</head>
<body>
    <div class="no-print bg-white border-bottom py-2 px-4 shadow-sm mb-3">
        <div class="container-fluid d-flex align-items-center justify-content-between">
            <span class="fw-semibold small">Rekapitulasi Pembayaran per Rombel</span>
            <button type="button" class="btn btn-sm btn-pastel-primary px-3" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Cetak Dokumen
            </button>
        </div>
    </div>

    <div class="report-container">
        <div class="kop-sekolah text-center">
            <h4 class="fw-bold mb-1 text-uppercase">{{ $schoolName }}</h4>
            <p class="mb-0 text-muted small">{{ $schoolAddress }}</p>
            <p class="mb-0 text-muted small">Laporan Rekapitulasi Rombongan Belajar</p>
        </div>

        <div class="text-center mb-3">
            <h5 class="fw-bold mb-0 text-uppercase">REKAPITULASI PEMBAYARAN PER ROMBEL</h5>
            <small class="text-muted">Tahun Ajaran: {{ $selectedYear ? $selectedYear->name . ' (' . ucfirst($selectedYear->semester) . ')' : 'Semua Tahun' }} &bull; Dicetak: {{ date('d F Y') }}</small>
        </div>

        <table class="table-report">
            <thead>
                <tr>
                    <th style="width: 35px;" class="text-center">No</th>
                    <th style="width: 60px;">Tingkat</th>
                    <th>Nama Rombel</th>
                    <th>Jurusan</th>
                    <th class="text-center" style="width: 90px;">Jumlah Murid</th>
                    <th class="text-end">Total Tagihan</th>
                    <th class="text-end">Telah Dibayar</th>
                    <th class="text-end">Sisa Tunggakan</th>
                    <th class="text-center" style="width: 90px;">% Kelunasan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recapData as $idx => $row)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>{{ $row['class_room']->level }}</td>
                        <td class="fw-bold">{{ $row['class_room']->name }}</td>
                        <td>{{ $row['class_room']->major?->name ?: '-' }}</td>
                        <td class="text-center">{{ $row['students_count'] }}</td>
                        <td class="text-end">Rp {{ number_format($row['total_billed'], 0, ',', '.') }}</td>
                        <td class="text-end text-success">Rp {{ number_format($row['total_paid'], 0, ',', '.') }}</td>
                        <td class="text-end text-danger fw-semibold">Rp {{ number_format($row['total_remaining'], 0, ',', '.') }}</td>
                        <td class="text-center fw-bold">{{ $row['percentage'] }}%</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="fw-bold" style="background-color: #f1f5f9;">
                    <td colspan="5" class="text-end">TOTAL KESELURUHAN :</td>
                    <td class="text-end">Rp {{ number_format($totalBilledAll, 0, ',', '.') }}</td>
                    <td class="text-end text-success">Rp {{ number_format($totalPaidAll, 0, ',', '.') }}</td>
                    <td class="text-end text-danger">Rp {{ number_format($totalRemainingAll, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $overallPercentage }}% Lunas</td>
                </tr>
            </tfoot>
        </table>

        <!-- Signatures -->
        <div class="d-flex justify-content-between text-center mt-5" style="font-size: 0.85rem;">
            <div style="width: 220px;">
                <p class="mb-5">Mengetahui,<br>Kepala Sekolah</p>
                <div class="border-bottom border-dark pb-1 fw-bold">{{ $headmasterName }}</div>
                <small class="text-muted">NIP. {{ $headmasterNip }}</small>
            </div>

            <div style="width: 220px;">
                <p class="mb-5">Mungkid, {{ date('d F Y') }}<br>Bendahara Sekolah</p>
                <div class="border-bottom border-dark pb-1 fw-bold">{{ $treasurerName }}</div>
                <small class="text-muted">Bendahara Keuangan</small>
            </div>
        </div>
    </div>
</body>
</html>
