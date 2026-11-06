<?php

namespace App\Http\Controllers;

use App\Helpers\CsvParser;
use App\Models\ClassRoom;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_murid.csv"',
        ];

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['nis', 'nisn', 'nama', 'jenis_kelamin', 'rombel', 'kategori', 'no_hp', 'nama_wali', 'no_hp_wali', 'alamat', 'status']);
            fputcsv($handle, ['2026001', '0081234501', 'Ahmad Fauzi', 'L', 'X RPL 1', 'reguler', '081234567801', 'Bambang', '081234567901', 'Magelang', 'aktif']);
            fputcsv($handle, ['2026002', '0081234502', 'Budi Santoso', 'L', 'X RPL 1', 'beasiswa_50', '081234567802', 'Slamet', '081234567902', 'Mungkid', 'aktif']);
            fputcsv($handle, ['2026003', '0081234503', 'Citra Lestari', 'P', 'X TKRO 1', 'beasiswa_100', '081234567803', 'Suranto', '081234567903', 'Mertoyudan', 'aktif']);
            fclose($handle);
        }, 'template_murid.csv', $headers);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);

        $rows = CsvParser::parse($request->file('file')->getRealPath());

        if (empty($rows)) {
            return back()->with('error', 'File CSV kosong atau format tidak valid.');
        }

        $imported = 0;
        $errors = [];

        // Preload class rooms for fast matching
        $classRooms = ClassRoom::with('academicYear')->get();

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;
            $nis = trim($row['nis'] ?? '');
            $nisn = trim($row['nisn'] ?? '');
            $name = trim($row['nama'] ?? ($row['name'] ?? ''));
            $genderRaw = strtoupper(trim($row['jenis_kelamin'] ?? ($row['gender'] ?? '')));
            $rombelName = trim($row['rombel'] ?? ($row['kelas'] ?? ($row['class_room'] ?? '')));
            $categoryRaw = strtolower(trim($row['kategori'] ?? ($row['category'] ?? 'reguler')));
            $phone = trim($row['no_hp'] ?? ($row['phone'] ?? ''));
            $parentName = trim($row['nama_wali'] ?? ($row['parent_name'] ?? ''));
            $parentPhone = trim($row['no_hp_wali'] ?? ($row['parent_phone'] ?? ''));
            $address = trim($row['alamat'] ?? ($row['address'] ?? ''));
            $statusRaw = strtolower(trim($row['status'] ?? 'aktif'));

            if ($nis === '' || $name === '') {
                $errors[] = "Baris #{$rowNum}: NIS dan Nama murid wajib diisi.";

                continue;
            }

            $gender = match ($genderRaw) {
                'L', 'LAKI-LAKI', 'PRIA', 'M' => 'L',
                'P', 'PEREMPUAN', 'WANITA', 'F' => 'P',
                default => 'L',
            };

            $category = match (true) {
                str_contains($categoryRaw, '100') => 'beasiswa_100',
                str_contains($categoryRaw, '50') => 'beasiswa_50',
                default => 'reguler',
            };

            $status = match ($statusRaw) {
                'lulus' => 'lulus',
                'mutasi' => 'mutasi',
                'keluar' => 'keluar',
                default => 'aktif',
            };

            $classRoomId = null;
            if ($rombelName !== '') {
                $foundClass = $classRooms->first(function ($c) use ($rombelName) {
                    return strcasecmp($c->name, $rombelName) === 0 && $c->academicYear?->is_active;
                }) ?? $classRooms->first(function ($c) use ($rombelName) {
                    return strcasecmp($c->name, $rombelName) === 0;
                });

                $classRoomId = $foundClass?->id;
            }

            Student::updateOrCreate(
                ['nis' => $nis],
                [
                    'nisn' => $nisn !== '' ? $nisn : null,
                    'name' => $name,
                    'gender' => $gender,
                    'class_room_id' => $classRoomId,
                    'category' => $category,
                    'phone' => $phone !== '' ? $phone : null,
                    'parent_name' => $parentName !== '' ? $parentName : null,
                    'parent_phone' => $parentPhone !== '' ? $parentPhone : null,
                    'address' => $address !== '' ? $address : null,
                    'status' => $status,
                ]
            );

            $imported++;
        }

        $message = "Berhasil mengimpor {$imported} data murid.";
        if (! empty($errors)) {
            $errPreview = implode(' ', array_slice($errors, 0, 3));
            if (count($errors) > 3) {
                $errPreview .= ' (dan '.(count($errors) - 3).' catatan lainnya)';
            }

            return redirect()->route('management.students.index')
                ->with('warning', "{$message} Terdapat beberapa catatan: {$errPreview}");
        }

        return redirect()->route('management.students.index')
            ->with('success', $message);
    }
}
