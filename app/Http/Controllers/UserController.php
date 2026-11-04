<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with('student.classRoom');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        $users = $query->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('management.users.index', compact('users'));
    }

    public function create(): View
    {
        $students = Student::orderBy('name')->get();

        return view('management.users.create', compact('students'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
            'role' => ['required', Rule::in(['admin', 'teller', 'kepsek', 'murid'])],
            'student_id' => [
                'nullable',
                Rule::requiredIf($request->input('role') === 'murid'),
                'exists:students,id',
            ],
            'phone' => ['nullable', 'string', 'max:25'],
        ]);

        User::create([
            'name' => $validated['name'],
            'username' => strtolower($validated['username']),
            'email' => strtolower($validated['email']),
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'student_id' => $validated['role'] === 'murid' ? $validated['student_id'] : null,
            'phone' => $validated['phone'] ?? null,
        ]);

        return redirect()->route('management.users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        $students = Student::orderBy('name')->get();

        return view('management.users.edit', compact('user', 'students'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', Password::defaults()],
            'role' => ['required', Rule::in(['admin', 'teller', 'kepsek', 'murid'])],
            'student_id' => [
                'nullable',
                Rule::requiredIf($request->input('role') === 'murid'),
                'exists:students,id',
            ],
            'phone' => ['nullable', 'string', 'max:25'],
        ]);

        $userData = [
            'name' => $validated['name'],
            'username' => strtolower($validated['username']),
            'email' => strtolower($validated['email']),
            'role' => $validated['role'],
            'student_id' => $validated['role'] === 'murid' ? $validated['student_id'] : null,
            'phone' => $validated['phone'] ?? null,
        ];

        if (! empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $user->update($userData);

        return redirect()->route('management.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->route('management.users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
