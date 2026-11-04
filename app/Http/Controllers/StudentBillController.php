<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\BillAssignment;
use App\Models\BillType;
use App\Models\ClassRoom;
use App\Models\Discount;
use App\Models\Student;
use App\Models\StudentBill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudentBillController extends Controller
{
    public function index(Request $request): View
    {
        $academicYears = AcademicYear::orderByDesc('name')->get();
        $classRooms = ClassRoom::orderBy('name')->get();
        $billTypes = BillType::orderBy('name')->get();

        $query = StudentBill::with(['student.classRoom', 'billType', 'academicYear']);

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->input('academic_year_id'));
        }

        if ($request->filled('class_room_id')) {
            $classRoomId = $request->input('class_room_id');
            $query->whereHas('student', function ($q) use ($classRoomId) {
                $q->where('class_room_id', $classRoomId);
            });
        }

        if ($request->filled('bill_type_id')) {
            $query->where('bill_type_id', $request->input('bill_type_id'));
        }

        if ($request->filled('month')) {
            $query->where('month', $request->input('month'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('category')) {
            $category = $request->input('category');
            $query->whereHas('student', function ($q) use ($category) {
                $q->where('category', $category);
            });
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        // Summary metrics before pagination
        $summaryQuery = clone $query;
        $totalBilled = $summaryQuery->sum('final_amount');
        $totalPaid = $summaryQuery->sum('paid_amount');
        $totalRemaining = max(0, $totalBilled - $totalPaid);

        $studentBills = $query->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('billing.student-bills.index', compact(
            'studentBills',
            'academicYears',
            'classRooms',
            'billTypes',
            'totalBilled',
            'totalPaid',
            'totalRemaining'
        ));
    }

    public function create(): View
    {
        $billTypes = BillType::with('discounts')->orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('name')->get();
        $activeYear = AcademicYear::where('is_active', true)->first();
        $classRooms = ClassRoom::with(['major', 'academicYear'])
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        $months = [
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
        ];

        return view('billing.student-bills.create', compact('billTypes', 'academicYears', 'activeYear', 'classRooms', 'months'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bill_type_id' => ['required', 'exists:bill_types,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'months' => ['nullable', 'array'],
            'months.*' => ['integer', 'between:1,12'],
            'year' => ['nullable', 'integer'],
            'class_room_ids' => ['required', 'array', 'min:1'],
            'class_room_ids.*' => ['exists:class_rooms,id'],
            'student_categories' => ['required', 'array', 'min:1'],
            'student_categories.*' => ['in:reguler,beasiswa_50,beasiswa_100'],
            'due_date' => ['nullable', 'date'],
        ]);

        $billType = BillType::with('discounts')->findOrFail($validated['bill_type_id']);
        $academicYear = AcademicYear::findOrFail($validated['academic_year_id']);

        $baseAmount = $request->filled('amount') ? (float) $validated['amount'] : (float) $billType->amount;
        $dueDate = $validated['due_date'] ?? null;
        $yearNumber = $validated['year'] ?? (int) date('Y');

        // Discount lookup
        $discounts = $billType->discounts->keyBy('student_category');

        // Target students
        $students = Student::whereIn('class_room_id', $validated['class_room_ids'])
            ->whereIn('category', $validated['student_categories'])
            ->where('status', 'aktif')
            ->get();

        if ($students->isEmpty()) {
            return back()->withInput()->with('error', 'Tidak ada siswa aktif yang cocok dengan kriteria rombel dan kategori terpilih.');
        }

        $createdCount = 0;
        $skippedCount = 0;

        DB::transaction(function () use (
            $students,
            $billType,
            $academicYear,
            $baseAmount,
            $dueDate,
            $yearNumber,
            $discounts,
            $validated,
            &$createdCount,
            &$skippedCount
        ) {
            // Record assignment metadata
            foreach ($validated['class_room_ids'] as $classRoomId) {
                BillAssignment::firstOrCreate([
                    'bill_type_id' => $billType->id,
                    'academic_year_id' => $academicYear->id,
                    'class_room_id' => $classRoomId,
                    'student_category' => count($validated['student_categories']) === 3 ? 'all' : implode(',', $validated['student_categories']),
                ]);
            }

            if ($billType->type === 'spp') {
                $targetMonths = ! empty($validated['months']) ? $validated['months'] : [7]; // default current month

                foreach ($students as $student) {
                    $discountPercent = 0.00;
                    if ($billType->is_discountable && $student->category !== 'reguler') {
                        if (isset($discounts[$student->category])) {
                            $discountPercent = (float) $discounts[$student->category]->percentage;
                        } elseif ($student->category === 'beasiswa_50') {
                            $discountPercent = 50.00;
                        } elseif ($student->category === 'beasiswa_100') {
                            $discountPercent = 100.00;
                        }
                    }

                    $discountAmount = round(($baseAmount * $discountPercent) / 100, 2);
                    $finalAmount = max(0, $baseAmount - $discountAmount);
                    $isFullyWaived = ($finalAmount == 0);

                    foreach ($targetMonths as $month) {
                        // Check duplicate
                        $exists = StudentBill::where([
                            'student_id' => $student->id,
                            'bill_type_id' => $billType->id,
                            'academic_year_id' => $academicYear->id,
                            'month' => $month,
                            'year' => $yearNumber,
                        ])->exists();

                        if ($exists) {
                            $skippedCount++;

                            continue;
                        }

                        StudentBill::create([
                            'student_id' => $student->id,
                            'bill_type_id' => $billType->id,
                            'academic_year_id' => $academicYear->id,
                            'month' => $month,
                            'year' => $yearNumber,
                            'amount' => $baseAmount,
                            'discount_percentage' => $discountPercent,
                            'discount_amount' => $discountAmount,
                            'final_amount' => $finalAmount,
                            'paid_amount' => $isFullyWaived ? 0.00 : 0.00,
                            'status' => $isFullyWaived ? 'paid' : 'unpaid',
                            'due_date' => $dueDate,
                        ]);

                        $createdCount++;
                    }
                }
            } else {
                // Custom or non-monthly bill
                foreach ($students as $student) {
                    $discountPercent = 0.00;
                    if ($billType->is_discountable && $student->category !== 'reguler') {
                        if (isset($discounts[$student->category])) {
                            $discountPercent = (float) $discounts[$student->category]->percentage;
                        } elseif ($student->category === 'beasiswa_50') {
                            $discountPercent = 50.00;
                        } elseif ($student->category === 'beasiswa_100') {
                            $discountPercent = 100.00;
                        }
                    }

                    $discountAmount = round(($baseAmount * $discountPercent) / 100, 2);
                    $finalAmount = max(0, $baseAmount - $discountAmount);
                    $isFullyWaived = ($finalAmount == 0);

                    // Check duplicate for same academic year & bill type
                    $exists = StudentBill::where([
                        'student_id' => $student->id,
                        'bill_type_id' => $billType->id,
                        'academic_year_id' => $academicYear->id,
                    ])->exists();

                    if ($exists) {
                        $skippedCount++;

                        continue;
                    }

                    StudentBill::create([
                        'student_id' => $student->id,
                        'bill_type_id' => $billType->id,
                        'academic_year_id' => $academicYear->id,
                        'month' => null,
                        'year' => $yearNumber,
                        'amount' => $baseAmount,
                        'discount_percentage' => $discountPercent,
                        'discount_amount' => $discountAmount,
                        'final_amount' => $finalAmount,
                        'paid_amount' => 0.00,
                        'status' => $isFullyWaived ? 'paid' : 'unpaid',
                        'due_date' => $dueDate,
                    ]);

                    $createdCount++;
                }
            }
        });

        $message = "Berhasil membuat {$createdCount} tagihan siswa.";
        if ($skippedCount > 0) {
            $message .= " ({$skippedCount} tagihan dilewati karena sudah ada sebelumnya)";
        }

        return redirect()->route('billing.student-bills.index')->with('success', $message);
    }

    public function destroy(StudentBill $studentBill): RedirectResponse
    {
        if ($studentBill->paid_amount > 0 || $studentBill->paymentItems()->exists()) {
            return back()->with('error', 'Tagihan yang sudah memiliki riwayat pembayaran tidak dapat dihapus.');
        }

        $studentBill->delete();

        return redirect()->route('billing.student-bills.index')
            ->with('success', 'Tagihan berhasil dihapus.');
    }
}
