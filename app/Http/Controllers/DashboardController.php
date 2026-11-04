<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\Major;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentBill;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $totalStudents = Student::count();
        $totalClasses = ClassRoom::count();
        $totalMajors = Major::count();
        $totalUsers = User::count();

        // Financial KPI Cards
        $todayIncome = Payment::whereDate('payment_date', today())->sum('total_amount');
        $monthIncome = Payment::whereYear('payment_date', now()->year)
            ->whereMonth('payment_date', now()->month)
            ->sum('total_amount');

        $totalBilled = StudentBill::sum('final_amount');
        $totalPaid = StudentBill::sum('paid_amount');
        $totalUnpaid = max(0, $totalBilled - $totalPaid);
        $overallCollectionRate = $totalBilled > 0 ? round(($totalPaid / $totalBilled) * 100, 1) : 0;

        // Student category stats
        $studentsByCategory = [
            'reguler' => Student::where('category', 'reguler')->count(),
            'beasiswa_50' => Student::where('category', 'beasiswa_50')->count(),
            'beasiswa_100' => Student::where('category', 'beasiswa_100')->count(),
        ];

        // 1. Chart: Harian (14 hari terakhir)
        $dailyLabels = [];
        $dailyValues = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dailyLabels[] = $date->format('d M');
            $dailyValues[$date->format('Y-m-d')] = 0;
        }

        $dailyPayments = Payment::whereDate('payment_date', '>=', Carbon::today()->subDays(13))
            ->selectRaw('DATE(payment_date) as p_date, SUM(total_amount) as total')
            ->groupBy('p_date')
            ->pluck('total', 'p_date')
            ->toArray();

        foreach ($dailyValues as $dateKey => $val) {
            $dailyValues[$dateKey] = isset($dailyPayments[$dateKey]) ? (float) $dailyPayments[$dateKey] : 0;
        }
        $dailyChartData = [
            'labels' => $dailyLabels,
            'values' => array_values($dailyValues),
        ];

        // 2. Chart: Mingguan (8 minggu terakhir)
        $weeklyLabels = [];
        $weeklyValues = [];
        for ($w = 7; $w >= 0; $w--) {
            $startOfWeek = Carbon::now()->subWeeks($w)->startOfWeek();
            $endOfWeek = Carbon::now()->subWeeks($w)->endOfWeek();

            $weeklyLabels[] = $startOfWeek->format('d M').' - '.$endOfWeek->format('d M');

            $sumWeek = Payment::whereDate('payment_date', '>=', $startOfWeek)
                ->whereDate('payment_date', '<=', $endOfWeek)
                ->sum('total_amount');

            $weeklyValues[] = (float) $sumWeek;
        }
        $weeklyChartData = [
            'labels' => $weeklyLabels,
            'values' => $weeklyValues,
        ];

        // 3. Chart: Bulanan (Tahun Kalender / 12 bulan)
        $monthlyLabels = [];
        $monthlyValues = [];
        for ($m = 11; $m >= 0; $m--) {
            $monthDate = Carbon::now()->subMonths($m)->startOfMonth();
            $monthlyLabels[] = $monthDate->format('M Y');

            $sumMonth = Payment::whereYear('payment_date', $monthDate->year)
                ->whereMonth('payment_date', $monthDate->month)
                ->sum('total_amount');

            $monthlyValues[] = (float) $sumMonth;
        }
        $monthlyChartData = [
            'labels' => $monthlyLabels,
            'values' => $monthlyValues,
        ];

        // 4. Progress bar pembayaran per rombel
        $classRoomsProgress = ClassRoom::with(['major', 'academicYear'])
            ->withCount('students')
            ->orderBy('level')
            ->orderBy('name')
            ->get()
            ->map(function (ClassRoom $cr) {
                $studentIds = $cr->students()->pluck('id');
                $billed = StudentBill::whereIn('student_id', $studentIds)->sum('final_amount');
                $paid = StudentBill::whereIn('student_id', $studentIds)->sum('paid_amount');
                $percentage = $billed > 0 ? round(($paid / $billed) * 100, 1) : 0;

                return [
                    'id' => $cr->id,
                    'name' => $cr->name,
                    'major_code' => $cr->major?->code,
                    'students_count' => $cr->students_count,
                    'total_billed' => (float) $billed,
                    'total_paid' => (float) $paid,
                    'total_remaining' => max(0, (float) $billed - (float) $paid),
                    'percentage' => min(100, $percentage),
                ];
            });

        // Recent payments
        $recentPayments = Payment::with(['student.classRoom', 'user'])
            ->latest('id')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'activeYear',
            'totalStudents',
            'totalClasses',
            'totalMajors',
            'totalUsers',
            'todayIncome',
            'monthIncome',
            'totalUnpaid',
            'totalPaid',
            'overallCollectionRate',
            'studentsByCategory',
            'dailyChartData',
            'weeklyChartData',
            'monthlyChartData',
            'classRoomsProgress',
            'recentPayments'
        ));
    }
}
