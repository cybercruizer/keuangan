<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\BillType;
use App\Models\ClassRoom;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\StudentBill;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Rekap Pembayaran per Siswa
     */
    public function students(Request $request): View|StreamedResponse
    {
        $classRooms = ClassRoom::orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('name')->get();

        $query = Student::with(['classRoom.major', 'bills']);

        if ($request->filled('class_room_id')) {
            $query->where('class_room_id', $request->input('class_room_id'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        // Calculate metrics for current filtered collection
        $students->getCollection()->transform(function (Student $student) {
            $totalBilled = (float) $student->bills->sum('final_amount');
            $totalPaid = (float) $student->bills->sum('paid_amount');
            $totalDiscount = (float) $student->bills->sum('discount_amount');
            $remaining = max(0, $totalBilled - $totalPaid);
            $percentage = $totalBilled > 0 ? round(($totalPaid / $totalBilled) * 100, 1) : 100;

            $student->recap = [
                'total_billed' => $totalBilled,
                'total_paid' => $totalPaid,
                'total_discount' => $totalDiscount,
                'remaining' => $remaining,
                'percentage' => $percentage,
            ];

            return $student;
        });

        $isPrint = $request->boolean('print');
        if ($isPrint) {
            $studentsAll = $query->orderBy('name')->get();
            $studentsAll->transform(function (Student $student) {
                $totalBilled = (float) $student->bills->sum('final_amount');
                $totalPaid = (float) $student->bills->sum('paid_amount');
                $remaining = max(0, $totalBilled - $totalPaid);
                $percentage = $totalBilled > 0 ? round(($totalPaid / $totalBilled) * 100, 1) : 100;

                $student->recap = [
                    'total_billed' => $totalBilled,
                    'total_paid' => $totalPaid,
                    'remaining' => $remaining,
                    'percentage' => $percentage,
                ];

                return $student;
            });

            return view('reports.recaps.students-print', [
                'students' => $studentsAll,
                'schoolName' => SchoolSetting::get('school_name', 'SMK Muhammadiyah Mungkid'),
                'schoolAddress' => SchoolSetting::get('school_address', 'Jl. Pemandangan Indah No. 12, Mungkid'),
                'headmasterName' => SchoolSetting::get('headmaster_name', 'Drs. H. Sukirman, M.Pd.'),
                'headmasterNip' => SchoolSetting::get('headmaster_nip', '19680512 199303 1 004'),
                'treasurerName' => SchoolSetting::get('treasurer_name', 'Siti Aminah, S.E.'),
            ]);
        }

        if ($request->boolean('export')) {
            $studentsAll = (clone $query)->orderBy('name')->get();
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename=rekap_pembayaran_siswa.csv',
            ];

            return response()->streamDownload(function () use ($studentsAll) {
                $handle = fopen('php://output', 'w');
                if ($handle === false) {
                    return;
                }
                fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
                fputcsv($handle, ['NIS', 'Nama Siswa', 'Rombel', 'Jurusan', 'Kategori', 'Wajib Bayar (Rp)', 'Sudah Dibayar (Rp)', 'Sisa Tunggakan (Rp)', 'Status Kelunasan']);
                foreach ($studentsAll as $st) {
                    $totalBilled = (float) $st->bills->sum('final_amount');
                    $totalPaid = (float) $st->bills->sum('paid_amount');
                    $remaining = max(0, $totalBilled - $totalPaid);
                    $percentage = $totalBilled > 0 ? round(($totalPaid / $totalBilled) * 100, 1) : 100;
                    fputcsv($handle, [
                        $st->nis,
                        $st->name,
                        $st->classRoom?->name ?? '-',
                        $st->classRoom?->major?->name ?? '-',
                        $st->category_label,
                        $totalBilled,
                        $totalPaid,
                        $remaining,
                        $percentage.'%',
                    ]);
                }
                fclose($handle);
            }, 'rekap_pembayaran_siswa.csv', $headers);
        }

        return view('reports.recaps.students', compact('students', 'classRooms', 'academicYears'));
    }

    /**
     * Rekap Pembayaran per Rombel
     */
    public function classRooms(Request $request): View|StreamedResponse
    {
        $academicYears = AcademicYear::orderByDesc('name')->get();
        $selectedYearId = $request->input('academic_year_id', AcademicYear::where('is_active', true)->value('id'));

        $query = ClassRoom::with(['major', 'academicYear'])
            ->withCount('students');

        if ($selectedYearId) {
            $query->where('academic_year_id', $selectedYearId);
        }

        $classRooms = $query->orderBy('level')->orderBy('name')->get();

        $recapData = $classRooms->map(function (ClassRoom $cr) use ($selectedYearId) {
            $studentIds = $cr->students()->pluck('id');

            $billsQuery = StudentBill::whereIn('student_id', $studentIds);
            if ($selectedYearId) {
                $billsQuery->where('academic_year_id', $selectedYearId);
            }

            $billed = (float) (clone $billsQuery)->sum('final_amount');
            $paid = (float) (clone $billsQuery)->sum('paid_amount');
            $discount = (float) (clone $billsQuery)->sum('discount_amount');
            $remaining = max(0, $billed - $paid);
            $percentage = $billed > 0 ? round(($paid / $billed) * 100, 1) : 0;

            return [
                'class_room' => $cr,
                'students_count' => $cr->students_count,
                'total_billed' => $billed,
                'total_paid' => $paid,
                'total_discount' => $discount,
                'total_remaining' => $remaining,
                'percentage' => $percentage,
            ];
        });

        $totalBilledAll = $recapData->sum('total_billed');
        $totalPaidAll = $recapData->sum('total_paid');
        $totalRemainingAll = $recapData->sum('total_remaining');
        $overallPercentage = $totalBilledAll > 0 ? round(($totalPaidAll / $totalBilledAll) * 100, 1) : 0;

        $isPrint = $request->boolean('print');
        if ($isPrint) {
            return view('reports.recaps.class-rooms-print', [
                'recapData' => $recapData,
                'totalBilledAll' => $totalBilledAll,
                'totalPaidAll' => $totalPaidAll,
                'totalRemainingAll' => $totalRemainingAll,
                'overallPercentage' => $overallPercentage,
                'selectedYear' => AcademicYear::find($selectedYearId),
                'schoolName' => SchoolSetting::get('school_name', 'SMK Muhammadiyah Mungkid'),
                'schoolAddress' => SchoolSetting::get('school_address', 'Jl. Pemandangan Indah No. 12, Mungkid'),
                'headmasterName' => SchoolSetting::get('headmaster_name', 'Drs. H. Sukirman, M.Pd.'),
                'headmasterNip' => SchoolSetting::get('headmaster_nip', '19680512 199303 1 004'),
                'treasurerName' => SchoolSetting::get('treasurer_name', 'Siti Aminah, S.E.'),
            ]);
        }

        if ($request->boolean('export')) {
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename=rekap_pembayaran_rombel.csv',
            ];

            return response()->streamDownload(function () use ($recapData) {
                $handle = fopen('php://output', 'w');
                if ($handle === false) {
                    return;
                }
                fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
                fputcsv($handle, ['Tingkat', 'Nama Rombel', 'Jurusan', 'Jumlah Murid', 'Total Tagihan (Rp)', 'Total Terbayar (Rp)', 'Sisa Tunggakan (Rp)', 'Persentase Kelunasan']);
                foreach ($recapData as $row) {
                    fputcsv($handle, [
                        $row['class_room']->level,
                        $row['class_room']->name,
                        $row['class_room']->major?->name ?? '-',
                        $row['students_count'],
                        $row['total_billed'],
                        $row['total_paid'],
                        $row['total_remaining'],
                        $row['percentage'].'%',
                    ]);
                }
                fclose($handle);
            }, 'rekap_pembayaran_rombel.csv', $headers);
        }

        return view('reports.recaps.class-rooms', compact(
            'recapData',
            'academicYears',
            'selectedYearId',
            'totalBilledAll',
            'totalPaidAll',
            'totalRemainingAll',
            'overallPercentage'
        ));
    }

    /**
     * Rekap Pembayaran per Jenis Tagihan
     */
    public function billTypes(Request $request): View|StreamedResponse
    {
        $academicYears = AcademicYear::orderByDesc('name')->get();
        $selectedYearId = $request->input('academic_year_id', AcademicYear::where('is_active', true)->value('id'));

        $billTypes = BillType::with('discounts')->orderBy('name')->get();

        $recapData = $billTypes->map(function (BillType $bt) use ($selectedYearId) {
            $query = StudentBill::where('bill_type_id', $bt->id);
            if ($selectedYearId) {
                $query->where('academic_year_id', $selectedYearId);
            }

            $countBills = (clone $query)->count();
            $countPaid = (clone $query)->where('status', 'paid')->count();
            $billed = (float) (clone $query)->sum('final_amount');
            $paid = (float) (clone $query)->sum('paid_amount');
            $discount = (float) (clone $query)->sum('discount_amount');
            $remaining = max(0, $billed - $paid);
            $percentage = $billed > 0 ? round(($paid / $billed) * 100, 1) : 0;

            return [
                'bill_type' => $bt,
                'count_bills' => $countBills,
                'count_paid' => $countPaid,
                'total_billed' => $billed,
                'total_paid' => $paid,
                'total_discount' => $discount,
                'total_remaining' => $remaining,
                'percentage' => $percentage,
            ];
        });

        $totalBilledAll = $recapData->sum('total_billed');
        $totalPaidAll = $recapData->sum('total_paid');
        $totalRemainingAll = $recapData->sum('total_remaining');
        $overallPercentage = $totalBilledAll > 0 ? round(($totalPaidAll / $totalBilledAll) * 100, 1) : 0;

        $isPrint = $request->boolean('print');
        if ($isPrint) {
            return view('reports.recaps.bill-types-print', [
                'recapData' => $recapData,
                'totalBilledAll' => $totalBilledAll,
                'totalPaidAll' => $totalPaidAll,
                'totalRemainingAll' => $totalRemainingAll,
                'overallPercentage' => $overallPercentage,
                'selectedYear' => AcademicYear::find($selectedYearId),
                'schoolName' => SchoolSetting::get('school_name', 'SMK Muhammadiyah Mungkid'),
                'schoolAddress' => SchoolSetting::get('school_address', 'Jl. Pemandangan Indah No. 12, Mungkid'),
                'headmasterName' => SchoolSetting::get('headmaster_name', 'Drs. H. Sukirman, M.Pd.'),
                'headmasterNip' => SchoolSetting::get('headmaster_nip', '19680512 199303 1 004'),
                'treasurerName' => SchoolSetting::get('treasurer_name', 'Siti Aminah, S.E.'),
            ]);
        }

        if ($request->boolean('export')) {
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename=rekap_pembayaran_tagihan.csv',
            ];

            return response()->streamDownload(function () use ($recapData) {
                $handle = fopen('php://output', 'w');
                if ($handle === false) {
                    return;
                }
                fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
                fputcsv($handle, ['Nama Tagihan', 'Tipe', 'Tarif Satuan (Rp)', 'Jml Tagihan', 'Jml Lunas', 'Total Tagihan (Rp)', 'Total Terbayar (Rp)', 'Sisa Tunggakan (Rp)', 'Persentase Kelunasan']);
                foreach ($recapData as $row) {
                    $bt = $row['bill_type'];
                    fputcsv($handle, [
                        $bt->name,
                        strtoupper($bt->type),
                        $bt->amount,
                        $row['count_bills'],
                        $row['count_paid'],
                        $row['total_billed'],
                        $row['total_paid'],
                        $row['total_remaining'],
                        $row['percentage'].'%',
                    ]);
                }
                fclose($handle);
            }, 'rekap_pembayaran_tagihan.csv', $headers);
        }

        return view('reports.recaps.bill-types', compact(
            'recapData',
            'academicYears',
            'selectedYearId',
            'totalBilledAll',
            'totalPaidAll',
            'totalRemainingAll',
            'overallPercentage'
        ));
    }
}
