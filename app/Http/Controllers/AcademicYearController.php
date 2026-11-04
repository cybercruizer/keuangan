<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AcademicYearController extends Controller
{
    public function index(): View
    {
        $academicYears = AcademicYear::withCount(['classRooms', 'studentBills'])
            ->orderByDesc('name')
            ->orderByDesc('semester')
            ->paginate(10);

        return view('management.academic-years.index', compact('academicYears'));
    }

    public function create(): View
    {
        return view('management.academic-years.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:20'],
            'semester' => ['required', Rule::in(['ganjil', 'genap'])],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['boolean'],
        ]);

        $isActive = $request->boolean('is_active');

        DB::transaction(function () use ($validated, $isActive) {
            if ($isActive) {
                AcademicYear::query()->update(['is_active' => false]);
            }

            AcademicYear::create([
                ...$validated,
                'is_active' => $isActive,
            ]);
        });

        return redirect()->route('management.academic-years.index')
            ->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    public function edit(AcademicYear $academicYear): View
    {
        return view('management.academic-years.edit', compact('academicYear'));
    }

    public function update(Request $request, AcademicYear $academicYear): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:20'],
            'semester' => ['required', Rule::in(['ganjil', 'genap'])],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['boolean'],
        ]);

        $isActive = $request->boolean('is_active');

        DB::transaction(function () use ($academicYear, $validated, $isActive) {
            if ($isActive) {
                AcademicYear::where('id', '!=', $academicYear->id)->update(['is_active' => false]);
            }

            $academicYear->update([
                ...$validated,
                'is_active' => $isActive,
            ]);
        });

        return redirect()->route('management.academic-years.index')
            ->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    public function destroy(AcademicYear $academicYear): RedirectResponse
    {
        if ($academicYear->is_active) {
            return back()->with('error', 'Tahun ajaran yang sedang aktif tidak dapat dihapus.');
        }

        if ($academicYear->classRooms()->exists()) {
            return back()->with('error', 'Tahun ajaran memiliki data rombel terkait dan tidak dapat dihapus.');
        }

        $academicYear->delete();

        return redirect()->route('management.academic-years.index')
            ->with('success', 'Tahun ajaran berhasil dihapus.');
    }

    public function setActive(AcademicYear $academicYear): RedirectResponse
    {
        DB::transaction(function () use ($academicYear) {
            AcademicYear::where('id', '!=', $academicYear->id)->update(['is_active' => false]);
            $academicYear->update(['is_active' => true]);
        });

        return back()->with('success', 'Tahun ajaran '.$academicYear->name.' ('.ucfirst($academicYear->semester).') berhasil diaktifkan.');
    }
}
