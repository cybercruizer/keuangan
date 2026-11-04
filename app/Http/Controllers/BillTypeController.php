<?php

namespace App\Http\Controllers;

use App\Models\BillType;
use App\Models\Discount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BillTypeController extends Controller
{
    public function index(): View
    {
        $billTypes = BillType::with('discounts')
            ->withCount('studentBills')
            ->orderBy('name')
            ->paginate(10);

        return view('billing.bill-types.index', compact('billTypes'));
    }

    public function create(): View
    {
        return view('billing.bill-types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0'],
            'type' => ['required', Rule::in(['spp', 'custom', 'tabungan'])],
            'is_discountable' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:500'],
            'discount_50' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_100' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $isDiscountable = $request->boolean('is_discountable');

        DB::transaction(function () use ($validated, $isDiscountable) {
            $billType = BillType::create([
                'name' => $validated['name'],
                'amount' => $validated['amount'],
                'type' => $validated['type'],
                'is_discountable' => $isDiscountable,
                'description' => $validated['description'] ?? null,
            ]);

            if ($isDiscountable) {
                Discount::updateOrCreate(
                    ['bill_type_id' => $billType->id, 'student_category' => 'beasiswa_50'],
                    ['percentage' => $validated['discount_50'] ?? 50.00]
                );

                Discount::updateOrCreate(
                    ['bill_type_id' => $billType->id, 'student_category' => 'beasiswa_100'],
                    ['percentage' => $validated['discount_100'] ?? 100.00]
                );
            }
        });

        return redirect()->route('billing.bill-types.index')
            ->with('success', 'Jenis tagihan berhasil ditambahkan.');
    }

    public function edit(BillType $billType): View
    {
        $billType->load('discounts');
        $discount50 = $billType->discounts->firstWhere('student_category', 'beasiswa_50')?->percentage ?? 50;
        $discount100 = $billType->discounts->firstWhere('student_category', 'beasiswa_100')?->percentage ?? 100;

        return view('billing.bill-types.edit', compact('billType', 'discount50', 'discount100'));
    }

    public function update(Request $request, BillType $billType): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0'],
            'type' => ['required', Rule::in(['spp', 'custom', 'tabungan'])],
            'is_discountable' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:500'],
            'discount_50' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_100' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $isDiscountable = $request->boolean('is_discountable');

        DB::transaction(function () use ($billType, $validated, $isDiscountable) {
            $billType->update([
                'name' => $validated['name'],
                'amount' => $validated['amount'],
                'type' => $validated['type'],
                'is_discountable' => $isDiscountable,
                'description' => $validated['description'] ?? null,
            ]);

            if ($isDiscountable) {
                Discount::updateOrCreate(
                    ['bill_type_id' => $billType->id, 'student_category' => 'beasiswa_50'],
                    ['percentage' => $validated['discount_50'] ?? 50.00]
                );

                Discount::updateOrCreate(
                    ['bill_type_id' => $billType->id, 'student_category' => 'beasiswa_100'],
                    ['percentage' => $validated['discount_100'] ?? 100.00]
                );
            } else {
                $billType->discounts()->delete();
            }
        });

        return redirect()->route('billing.bill-types.index')
            ->with('success', 'Jenis tagihan berhasil diperbarui.');
    }

    public function destroy(BillType $billType): RedirectResponse
    {
        if ($billType->studentBills()->exists()) {
            return back()->with('error', 'Jenis tagihan sudah memiliki riwayat tagihan siswa dan tidak dapat dihapus.');
        }

        $billType->discounts()->delete();
        $billType->delete();

        return redirect()->route('billing.bill-types.index')
            ->with('success', 'Jenis tagihan berhasil dihapus.');
    }
}
