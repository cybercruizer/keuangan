<?php

namespace App\Http\Controllers;

use App\Models\Major;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MajorController extends Controller
{
    public function index(): View
    {
        $majors = Major::withCount('classRooms')
            ->orderBy('code')
            ->paginate(10);

        return view('management.majors.index', compact('majors'));
    }

    public function create(): View
    {
        return view('management.majors.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:majors,code'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        Major::create([
            ...$validated,
            'code' => strtoupper($validated['code']),
        ]);

        return redirect()->route('management.majors.index')
            ->with('success', 'Jurusan berhasil ditambahkan.');
    }

    public function edit(Major $major): View
    {
        return view('management.majors.edit', compact('major'));
    }

    public function update(Request $request, Major $major): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('majors', 'code')->ignore($major->id)],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $major->update([
            ...$validated,
            'code' => strtoupper($validated['code']),
        ]);

        return redirect()->route('management.majors.index')
            ->with('success', 'Jurusan berhasil diperbarui.');
    }

    public function destroy(Major $major): RedirectResponse
    {
        if ($major->classRooms()->exists()) {
            return back()->with('error', 'Jurusan ini memiliki rombel terkait dan tidak dapat dihapus.');
        }

        $major->delete();

        return redirect()->route('management.majors.index')
            ->with('success', 'Jurusan berhasil dihapus.');
    }
}
