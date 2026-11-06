<?php

namespace App\Http\Controllers;

use App\Helpers\Terbilang;
use App\Models\CashFlow;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\StudentBill;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $tellers = User::whereIn('role', ['admin', 'teller'])->orderBy('name')->get();

        $query = Payment::with(['student.classRoom.major', 'user', 'items.studentBill.billType']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%")
                            ->orWhere('nis', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->input('date_to'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        $summaryQuery = clone $query;
        $totalCollected = $summaryQuery->sum('total_amount');
        $totalTransactions = $summaryQuery->count();

        $payments = $query->latest('payment_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('payments.index', compact('payments', 'tellers', 'totalCollected', 'totalTransactions'));
    }

    /**
     * Search students dynamically for payment suggestion dropdown.
     */
    public function searchStudents(Request $request): JsonResponse
    {
        $search = trim((string) $request->input('q', ''));

        $query = Student::with(['classRoom.major'])
            ->where('status', 'aktif');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('name')
            ->limit(15)
            ->get();

        $data = $students->map(function (Student $student) {
            return [
                'id' => $student->id,
                'name' => $student->name,
                'nis' => $student->nis,
                'nisn' => $student->nisn,
                'class_name' => $student->classRoom?->name ?? 'Tanpa Kelas',
                'major_name' => $student->classRoom?->major?->name ?? '-',
                'category_label' => $student->category_label,
                'select_url' => route('payments.create', ['student_id' => $student->id]),
            ];
        });

        return response()->json($data);
    }

    public function create(Request $request): View
    {
        $selectedStudent = null;
        $unpaidBills = collect();

        if ($request->filled('student_id')) {
            $selectedStudent = Student::with(['classRoom.major', 'classRoom.academicYear'])
                ->findOrFail($request->input('student_id'));

            $unpaidBills = StudentBill::with(['billType', 'academicYear'])
                ->where('student_id', $selectedStudent->id)
                ->whereIn('status', ['unpaid', 'partial'])
                ->orderBy('academic_year_id')
                ->orderBy('month')
                ->get();
        }

        return view('payments.create', compact('selectedStudent', 'unpaidBills'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'max:50'],
            'bills' => ['required', 'array', 'min:1'],
            'bills.*.selected' => ['nullable'],
            'bills.*.bill_id' => ['required_with:bills.*.selected', 'exists:student_bills,id'],
            'bills.*.pay_amount' => ['required_with:bills.*.selected', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $student = Student::findOrFail($validated['student_id']);

        // Filter only checked bills with pay_amount > 0
        $itemsToPay = [];
        $totalPayment = 0.00;

        foreach ($validated['bills'] as $billInput) {
            if (! empty($billInput['selected']) && isset($billInput['pay_amount']) && (float) $billInput['pay_amount'] > 0) {
                $bill = StudentBill::where('id', $billInput['bill_id'])
                    ->where('student_id', $student->id)
                    ->firstOrFail();

                $payAmount = min((float) $billInput['pay_amount'], $bill->remainingAmount());

                if ($payAmount > 0) {
                    $itemsToPay[] = [
                        'bill' => $bill,
                        'pay_amount' => $payAmount,
                    ];
                    $totalPayment += $payAmount;
                }
            }
        }

        if (empty($itemsToPay)) {
            return back()->withInput()->with('error', 'Pilih minimal satu tagihan untuk dibayarkan dengan nominal lebih dari 0.');
        }

        $payment = DB::transaction(function () use ($student, $validated, $itemsToPay, $totalPayment) {
            // Generate Invoice number: INV-YYYYMMDD-XXXX
            $datePrefix = date('Ymd', strtotime($validated['payment_date']));
            $todayCount = Payment::whereDate('payment_date', $validated['payment_date'])->count() + 1;
            $invoiceNumber = 'KW-'.$datePrefix.'-'.str_pad((string) $todayCount, 4, '0', STR_PAD_LEFT);

            // Create Payment
            $payment = Payment::create([
                'invoice_number' => $invoiceNumber,
                'student_id' => $student->id,
                'user_id' => Auth::id(),
                'total_amount' => $totalPayment,
                'payment_method' => $validated['payment_method'],
                'payment_date' => $validated['payment_date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create Payment Items and update bills
            foreach ($itemsToPay as $item) {
                /** @var StudentBill $bill */
                $bill = $item['bill'];
                $payAmount = $item['pay_amount'];

                PaymentItem::create([
                    'payment_id' => $payment->id,
                    'student_bill_id' => $bill->id,
                    'amount' => $payAmount,
                    'notes' => $bill->billType->name.($bill->month_name ? ' - '.$bill->month_name.' '.$bill->year : ''),
                ]);

                $newPaid = (float) $bill->paid_amount + $payAmount;
                $newStatus = ($newPaid >= (float) $bill->final_amount) ? 'paid' : 'partial';

                $bill->update([
                    'paid_amount' => $newPaid,
                    'status' => $newStatus,
                ]);
            }

            // Record to Cash Flow
            CashFlow::create([
                'date' => $validated['payment_date'],
                'type' => 'income',
                'category' => 'Pembayaran Siswa',
                'amount' => $totalPayment,
                'description' => "Pembayaran {$payment->invoice_number} an. {$student->name} (NIS: {$student->nis})",
                'user_id' => Auth::id(),
                'payment_id' => $payment->id,
            ]);

            return $payment;
        });

        return redirect()->route('payments.receipt', $payment)
            ->with('success', 'Pembayaran berhasil disimpan. Kuitansi siap dicetak.');
    }

    public function show(Payment $payment): View
    {
        return $this->receipt($payment);
    }

    public function receipt(Payment $payment): View
    {
        $payment->load([
            'student.classRoom.major',
            'student.classRoom.academicYear',
            'user',
            'items.studentBill.billType',
            'items.studentBill.academicYear',
        ]);

        $terbilang = Terbilang::make((float) $payment->total_amount);

        $schoolName = SchoolSetting::get('school_name', 'SMK Muhammadiyah Mungkid');
        $schoolAddress = SchoolSetting::get('school_address', 'Jl. Pemandangan Indah No. 12, Mungkid, Magelang');
        $schoolPhone = SchoolSetting::get('school_phone', '(0293) 788123');
        $headmasterName = SchoolSetting::get('headmaster_name', 'Drs. H. Sukirman, M.Pd.');
        $treasurerName = SchoolSetting::get('treasurer_name', 'Siti Aminah, S.E.');

        return view('payments.receipt', compact(
            'payment',
            'terbilang',
            'schoolName',
            'schoolAddress',
            'schoolPhone',
            'headmasterName',
            'treasurerName'
        ));
    }
}
