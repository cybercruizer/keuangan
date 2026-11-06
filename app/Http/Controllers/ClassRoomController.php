<?php

namespace App\Http\Controllers;

use App\Helpers\CsvParser;
use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\Major;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_rombel.csv"',
        ];

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['nama', 'tingkat', 'kode_jurusan', 'tahun_ajaran']);
            fputcsv($handle, ['X RPL 1', 'X', 'RPL', '2025/2026']);
            fputcsv($handle, ['X TKRO 1', 'X', 'TKRO', '2025/2026']);
            fputcsv($handle, ['XI RPL 1', 'XI', 'RPL', '2025/2026']);
            fclose($handle);
        }, 'template_rombel.csv', $headers);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
        ]);

        $rows = CsvParser::parse($request->file('file')->getRealPath());

        if (empty($rows)) {
            return back()->with('error', 'File CSV kosong atau format tidak valid.');
        }

        $imported = 0;
        $errors = [];
        $defaultAcademicYear = $request->filled('academic_year_id')
            ? AcademicYear::find($request->input('academic_year_id'))
            : AcademicYear::where('is_active', true)->first();

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;
            $name = trim($row['nama'] ?? '');
            $levelRaw = strtoupper(trim($row['tingkat'] ?? ''));
            $majorCode = trim($row['kode_jurusan'] ?? ($row['jurusan'] ?? ''));
            $yearName = trim($row['tahun_ajaran'] ?? '');

            if ($name === '') {
                $errors[] = "Baris #{$rowNum}: Nama rombel wajib diisi.";

                continue;
            }

            $level = match ($levelRaw) {
                '10', 'X' => 'X',
                '11', 'XI' => 'XI',
                '12', 'XII' => 'XII',
                default => null,
            };

            if (! $level) {
                if (preg_match('/^(XII|XI|X|12|11|10)\b/i', $name, $matches)) {
                    $matched = strtoupper($matches[1]);
                    $level = match ($matched) {
                        '10', 'X' => 'X',
                        '11', 'XI' => 'XI',
                        '12', 'XII' => 'XII',
                        default => 'X',
                    };
                } else {
                    $errors[] = "Baris #{$rowNum} ({$name}): Tingkat kelas harus X, XI, atau XII.";

                    continue;
                }
            }

            $major = null;
            if ($majorCode !== '') {
                $major = Major::where('code', $majorCode)
                    ->orWhere('name', 'like', "%{$majorCode}%")
                    ->first();
            }

            if (! $major) {
                $errors[] = "Baris #{$rowNum} ({$name}): Jurusan '{$majorCode}' tidak ditemukan di sistem.";

                continue;
            }

            $academicYear = null;
            if ($yearName !== '') {
                $academicYear = AcademicYear::where('name', $yearName)->first();
            }

            $academicYear ??= $defaultAcademicYear;

            if (! $academicYear) {
                $errors[] = "Baris #{$rowNum} ({$name}): Tahun ajaran tidak ditemukan dan tidak ada tahun ajaran aktif.";

                continue;
            }

            ClassRoom::updateOrCreate(
                [
                    'academic_year_id' => $academicYear->id,
                    'name' => $name,
                ],
                [
                    'major_id' => $major->id,
                    'level' => $level,
                ]
            );

            $imported++;
        }

        $message = "Berhasil mengimpor {$imported} rombel.";
        if (! empty($errors)) {
            $errPreview = implode(' ', array_slice($errors, 0, 3));
            if (count($errors) > 3) {
                $errPreview .= ' (dan '.(count($errors) - 3).' catatan lainnya)';
            }

            return redirect()->route('management.class-rooms.index')
                ->with('warning', "{$message} Terdapat beberapa catatan: {$errPreview}");
        }

        return redirect()->route('management.class-rooms.index')
            ->with('success', $message);
    }
}
