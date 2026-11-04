<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $classRooms = ClassRoom::with('major')->orderBy('name')->get();

        $query = Student::with(['classRoom.major', 'classRoom.academicYear']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        if ($request->filled('class_room_id')) {
            $query->where('class_room_id', $request->input('class_room_id'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $students = $query->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('management.students.index', compact('students', 'classRooms'));
    }

    public function create(): View
    {
        $classRooms = ClassRoom::with(['major', 'academicYear'])->orderBy('name')->get();

        return view('management.students.create', compact('classRooms'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nis' => ['required', 'string', 'max:20', 'unique:students,nis'],
            'nisn' => ['nullable', 'string', 'max:20', 'unique:students,nisn'],
            'name' => ['required', 'string', 'max:150'],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'class_room_id' => ['nullable', 'exists:class_rooms,id'],
            'category' => ['required', Rule::in(['reguler', 'beasiswa_50', 'beasiswa_100'])],
            'phone' => ['nullable', 'string', 'max:25'],
            'parent_name' => ['nullable', 'string', 'max:150'],
            'parent_phone' => ['nullable', 'string', 'max:25'],
            'address' => ['nullable', 'string', 'max:500'],
            'status' => ['required', Rule::in(['aktif', 'lulus', 'mutasi', 'keluar'])],
        ]);

        Student::create($validated);

        return redirect()->route('management.students.index')
            ->with('success', 'Data murid berhasil ditambahkan.');
    }

    public function show(Student $student): View
    {
        $student->load([
            'classRoom.major',
            'classRoom.academicYear',
            'user',
            'bills.billType',
            'payments.user',
            'savingsTransactions' => fn ($q) => $q->latest('id')->take(5),
        ]);

        return view('management.students.show', compact('student'));
    }

    public function edit(Student $student): View
    {
        $classRooms = ClassRoom::with(['major', 'academicYear'])->orderBy('name')->get();

        return view('management.students.edit', compact('student', 'classRooms'));
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'nis' => ['required', 'string', 'max:20', Rule::unique('students', 'nis')->ignore($student->id)],
            'nisn' => ['nullable', 'string', 'max:20', Rule::unique('students', 'nisn')->ignore($student->id)],
            'name' => ['required', 'string', 'max:150'],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'class_room_id' => ['nullable', 'exists:class_rooms,id'],
            'category' => ['required', Rule::in(['reguler', 'beasiswa_50', 'beasiswa_100'])],
            'phone' => ['nullable', 'string', 'max:25'],
            'parent_name' => ['nullable', 'string', 'max:150'],
            'parent_phone' => ['nullable', 'string', 'max:25'],
            'address' => ['nullable', 'string', 'max:500'],
            'status' => ['required', Rule::in(['aktif', 'lulus', 'mutasi', 'keluar'])],
        ]);

        $student->update($validated);

        return redirect()->route('management.students.index')
            ->with('success', 'Data murid berhasil diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        if ($student->bills()->exists() || $student->payments()->exists()) {
            return back()->with('error', 'Murid memiliki riwayat tagihan/pembayaran dan tidak dapat dihapus.');
        }

        $student->delete();

        return redirect()->route('management.students.index')
            ->with('success', 'Data murid berhasil dihapus.');
    }
}
