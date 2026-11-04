<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\Major;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClassRoomController extends Controller
{
    public function index(Request $request): View
    {
        $academicYears = AcademicYear::orderByDesc('name')->get();
        $majors = Major::orderBy('code')->get();

        $query = ClassRoom::with(['academicYear', 'major'])
            ->withCount('students');

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->input('academic_year_id'));
        }

        if ($request->filled('major_id')) {
            $query->where('major_id', $request->input('major_id'));
        }

        if ($request->filled('level')) {
            $query->where('level', $request->input('level'));
        }

        $classRooms = $query->orderBy('level')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('management.class-rooms.index', compact('classRooms', 'academicYears', 'majors'));
    }

    public function create(): View
    {
        $academicYears = AcademicYear::orderByDesc('name')->get();
        $majors = Major::orderBy('name')->get();
        $activeYear = AcademicYear::where('is_active', true)->first();

        return view('management.class-rooms.create', compact('academicYears', 'majors', 'activeYear'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'major_id' => ['required', 'exists:majors,id'],
            'name' => ['required', 'string', 'max:50'],
            'level' => ['required', Rule::in(['X', 'XI', 'XII'])],
        ]);

        ClassRoom::create($validated);

        return redirect()->route('management.class-rooms.index')
            ->with('success', 'Rombel berhasil ditambahkan.');
    }

    public function edit(ClassRoom $classRoom): View
    {
        $academicYears = AcademicYear::orderByDesc('name')->get();
        $majors = Major::orderBy('name')->get();

        return view('management.class-rooms.edit', compact('classRoom', 'academicYears', 'majors'));
    }

    public function update(Request $request, ClassRoom $classRoom): RedirectResponse
    {
        $validated = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'major_id' => ['required', 'exists:majors,id'],
            'name' => ['required', 'string', 'max:50'],
            'level' => ['required', Rule::in(['X', 'XI', 'XII'])],
        ]);

        $classRoom->update($validated);

        return redirect()->route('management.class-rooms.index')
            ->with('success', 'Rombel berhasil diperbarui.');
    }

    public function destroy(ClassRoom $classRoom): RedirectResponse
    {
        if ($classRoom->students()->exists()) {
            return back()->with('error', 'Rombel memiliki siswa terdaftar dan tidak dapat dihapus.');
        }

        $classRoom->delete();

        return redirect()->route('management.class-rooms.index')
            ->with('success', 'Rombel berhasil dihapus.');
    }
}
