<?php

namespace App\Http\Controllers;

use App\Models\CashFlow;
use App\Models\SchoolSetting;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CashFlowController extends Controller
{
    public function index(Request $request): View|StreamedResponse
    {
        $period = $request->input('period', 'monthly'); // daily, monthly, yearly, custom
        $type = $request->input('type'); // null/all, income, expense

        $today = Carbon::today();
        $dateParam = $request->input('date', $today->format('Y-m-d'));
        $monthParam = $request->input('month', $today->month);
        $yearParam = $request->input('year', $today->year);
        $dateFromParam = $request->input('date_from', $today->copy()->startOfMonth()->format('Y-m-d'));
        $dateToParam = $request->input('date_to', $today->format('Y-m-d'));

        $query = CashFlow::with(['user', 'payment.student']);

        // Filter based on period
        $periodLabel = '';
        if ($period === 'daily') {
            $query->whereDate('date', $dateParam);
            $periodLabel = 'Harian: '.Carbon::parse($dateParam)->format('d F Y');
        } elseif ($period === 'yearly') {
            $query->whereYear('date', $yearParam);
            $periodLabel = "Tahunan: Tahun {$yearParam}";
        } elseif ($period === 'custom') {
            $query->whereDate('date', '>=', $dateFromParam)
                ->whereDate('date', '<=', $dateToParam);
            $periodLabel = 'Periode: '.Carbon::parse($dateFromParam)->format('d/m/Y').' s/d '.Carbon::parse($dateToParam)->format('d/m/Y');
        } else {
            // monthly default
            $query->whereYear('date', $yearParam)
                ->whereMonth('date', $monthParam);
            $periodLabel = 'Bulanan: '.Carbon::createFromDate($yearParam, $monthParam, 1)->format('F Y');
        }

        if ($type && in_array($type, ['income', 'expense'], true)) {
            $query->where('type', $type);
        }

        // Summary calculations
        $summaryQuery = clone $query;
        $totalIncome = (float) (clone $summaryQuery)->where('type', 'income')->sum('amount');
        $totalExpense = (float) (clone $summaryQuery)->where('type', 'expense')->sum('amount');
        $netBalance = $totalIncome - $totalExpense;

        if ($request->boolean('export')) {
            $exportData = (clone $query)->orderBy('date', 'asc')->orderBy('id', 'asc')->get();
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename=laporan_arus_kas.csv',
            ];

            return response()->streamDownload(function () use ($exportData) {
                $handle = fopen('php://output', 'w');
                if ($handle === false) {
                    return;
                }
                fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
                fputcsv($handle, ['Tanggal', 'Jenis', 'Kategori', 'Keterangan', 'Nominal (Rp)', 'Dicatat Oleh']);
                foreach ($exportData as $cf) {
                    fputcsv($handle, [
                        $cf->date->format('Y-m-d'),
                        $cf->type === 'income' ? 'Pemasukan' : 'Pengeluaran',
                        $cf->category,
                        $cf->description,
                        $cf->amount,
                        $cf->user?->name ?? 'Sistem',
                    ]);
                }
                fclose($handle);
            }, 'laporan_arus_kas.csv', $headers);
        }

        $cashFlows = $query->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('reports.cash-flows.index', compact(
            'cashFlows',
            'period',
            'periodLabel',
            'type',
            'dateParam',
            'monthParam',
            'yearParam',
            'dateFromParam',
            'dateToParam',
            'totalIncome',
            'totalExpense',
            'netBalance'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'type' => ['required', Rule::in(['income', 'expense'])],
            'category' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:1000'],
            'description' => ['required', 'string', 'max:500'],
        ]);

        CashFlow::create([
            'date' => $validated['date'],
            'type' => $validated['type'],
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'user_id' => Auth::id(),
            'payment_id' => null,
        ]);

        $label = $validated['type'] === 'expense' ? 'Pengeluaran' : 'Pemasukan';

        return redirect()->route('reports.cash-flows.index')
            ->with('success', "Transaksi {$label} kas berhasil dicatat.");
    }

    public function destroy(CashFlow $cashFlow): RedirectResponse
    {
        if ($cashFlow->payment_id !== null) {
            return back()->with('error', 'Transaksi arus kas yang terhubung dengan pembayaran kuitansi siswa tidak dapat dihapus secara manual.');
        }

        $cashFlow->delete();

        return redirect()->route('reports.cash-flows.index')
            ->with('success', 'Catatan arus kas berhasil dihapus.');
    }

    public function print(Request $request): View
    {
        $period = $request->input('period', 'monthly');
        $today = Carbon::today();
        $dateParam = $request->input('date', $today->format('Y-m-d'));
        $monthParam = $request->input('month', $today->month);
        $yearParam = $request->input('year', $today->year);
        $dateFromParam = $request->input('date_from', $today->copy()->startOfMonth()->format('Y-m-d'));
        $dateToParam = $request->input('date_to', $today->format('Y-m-d'));

        $query = CashFlow::with(['user', 'payment.student']);

        $periodLabel = '';
        if ($period === 'daily') {
            $query->whereDate('date', $dateParam);
            $periodLabel = 'Harian ('.Carbon::parse($dateParam)->format('d F Y').')';
        } elseif ($period === 'yearly') {
            $query->whereYear('date', $yearParam);
            $periodLabel = "Tahunan (Tahun {$yearParam})";
        } elseif ($period === 'custom') {
            $query->whereDate('date', '>=', $dateFromParam)
                ->whereDate('date', '<=', $dateToParam);
            $periodLabel = Carbon::parse($dateFromParam)->format('d/m/Y').' s/d '.Carbon::parse($dateToParam)->format('d/m/Y');
        } else {
            $query->whereYear('date', $yearParam)
                ->whereMonth('date', $monthParam);
            $periodLabel = 'Bulan '.Carbon::createFromDate($yearParam, $monthParam, 1)->format('F Y');
        }

        $cashFlows = $query->orderBy('date', 'asc')->orderBy('id', 'asc')->get();
        $totalIncome = (float) $cashFlows->where('type', 'income')->sum('amount');
        $totalExpense = (float) $cashFlows->where('type', 'expense')->sum('amount');
        $netBalance = $totalIncome - $totalExpense;

        $schoolName = SchoolSetting::get('school_name', 'SMK Muhammadiyah Mungkid');
        $schoolAddress = SchoolSetting::get('school_address', 'Jl. Pemandangan Indah No. 12, Mungkid, Magelang');
        $headmasterName = SchoolSetting::get('headmaster_name', 'Drs. H. Sukirman, M.Pd.');
        $headmasterNip = SchoolSetting::get('headmaster_nip', '19680512 199303 1 004');
        $treasurerName = SchoolSetting::get('treasurer_name', 'Siti Aminah, S.E.');

        return view('reports.cash-flows.print', compact(
            'cashFlows',
            'periodLabel',
            'totalIncome',
            'totalExpense',
            'netBalance',
            'schoolName',
            'schoolAddress',
            'headmasterName',
            'headmasterNip',
            'treasurerName'
        ));
    }
}
