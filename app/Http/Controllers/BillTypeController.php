<?php

namespace App\Http\Controllers;

use App\Helpers\CsvParser;
use App\Models\BillType;
use App\Models\Discount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_jenis_tagihan.csv"',
        ];

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['nama', 'tarif', 'tipe', 'bisa_diskon', 'diskon_50', 'diskon_100', 'keterangan']);
            fputcsv($handle, ['SPP Bulanan', '250000', 'spp', 'ya', '50', '100', 'Tagihan SPP reguler bulanan']);
            fputcsv($handle, ['Uang Gedung', '1500000', 'custom', 'ya', '50', '100', 'Infaq pembangunan sarpras']);
            fputcsv($handle, ['Seragam Jurusan', '450000', 'custom', 'tidak', '0', '0', 'Seragam praktik kejuruan']);
            fclose($handle);
        }, 'template_jenis_tagihan.csv', $headers);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $rows = CsvParser::parse($request->file('file')->getRealPath());

        if (empty($rows)) {
            return back()->with('error', 'File CSV kosong atau format tidak valid.');
        }

        $imported = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;
            $name = trim($row['nama'] ?? '');
            $amountRaw = trim($row['tarif'] ?? ($row['nominal'] ?? ($row['amount'] ?? '')));
            $typeRaw = strtolower(trim($row['tipe'] ?? ($row['type'] ?? 'custom')));
            $discountableRaw = strtolower(trim($row['bisa_diskon'] ?? ($row['is_discountable'] ?? 'tidak')));
            $discount50Raw = trim($row['diskon_50'] ?? '50');
            $discount100Raw = trim($row['diskon_100'] ?? '100');
            $description = trim($row['keterangan'] ?? ($row['deskripsi'] ?? ''));

            if ($name === '') {
                $errors[] = "Baris #{$rowNum}: Nama tagihan wajib diisi.";

                continue;
            }

            $amount = (float) preg_replace('/[^0-9.]/', '', str_replace(',', '.', $amountRaw));

            $type = match ($typeRaw) {
                'spp' => 'spp',
                'tabungan' => 'tabungan',
                default => 'custom',
            };

            $isDiscountable = in_array($discountableRaw, ['ya', '1', 'true', 'yes', 'y'], true);
            $discount50 = is_numeric($discount50Raw) ? (float) $discount50Raw : 50.00;
            $discount100 = is_numeric($discount100Raw) ? (float) $discount100Raw : 100.00;

            DB::transaction(function () use ($name, $amount, $type, $isDiscountable, $description, $discount50, $discount100) {
                $billType = BillType::updateOrCreate(
                    ['name' => $name],
                    [
                        'amount' => $amount,
                        'type' => $type,
                        'is_discountable' => $isDiscountable,
                        'description' => $description !== '' ? $description : null,
                    ]
                );

                if ($isDiscountable) {
                    Discount::updateOrCreate(
                        ['bill_type_id' => $billType->id, 'student_category' => 'beasiswa_50'],
                        ['percentage' => $discount50]
                    );

                    Discount::updateOrCreate(
                        ['bill_type_id' => $billType->id, 'student_category' => 'beasiswa_100'],
                        ['percentage' => $discount100]
                    );
                }
            });

            $imported++;
        }

        $message = "Berhasil mengimpor {$imported} jenis tagihan.";
        if (! empty($errors)) {
            $errPreview = implode(' ', array_slice($errors, 0, 3));

            return redirect()->route('billing.bill-types.index')
                ->with('warning', "{$message} Terdapat beberapa catatan: {$errPreview}");
        }

        return redirect()->route('billing.bill-types.index')
            ->with('success', $message);
    }
}
