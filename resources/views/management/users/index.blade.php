@extends('layouts.app')

@section('title', 'Manajemen User')
@section('header_title', 'Manajemen Pengguna Sistem')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h5 class="fw-bold mb-1">Daftar Pengguna Sistem</h5>
        <p class="text-muted small mb-0">Kelola akun dan hak akses: Admin, Teller, Kepala Sekolah, dan Murid.</p>
    </div>
    <a href="{{ route('management.users.create') }}" class="btn btn-pastel-primary">
        <i class="bi bi-person-plus me-1"></i> Tambah Pengguna
    </a>
</div>

<!-- Filters -->
<div class="card p-3 mb-3">
    <form method="GET" action="{{ route('management.users.index') }}" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama, username, atau email..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-3">
            <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Peran (Role)</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin (Administrator)</option>
                <option value="teller" {{ request('role') === 'teller' ? 'selected' : '' }}>Teller / Kasir</option>
                <option value="kepsek" {{ request('role') === 'kepsek' ? 'selected' : '' }}>Kepala Sekolah</option>
                <option value="murid" {{ request('role') === 'murid' ? 'selected' : '' }}>Murid</option>
            </select>
        </div>
        <div class="col-md-2 d-flex">
            <a href="{{ route('management.users.index') }}" class="btn btn-sm btn-light w-100">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
            </a>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Nama & Username</th>
                    <th>Email</th>
                    <th>Role / Peran</th>
                    <th>Terkait Siswa</th>
                    <th>No. Telepon</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="fw-bold">{{ $user->name }}</div>
                            <small class="text-muted font-monospace">@<span>{{ $user->username }}</span></small>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($user->role === 'admin')
                                <span class="badge badge-pastel-primary"><i class="bi bi-shield-lock me-1"></i> Admin</span>
                            @elseif($user->role === 'teller')
                                <span class="badge badge-pastel-accent"><i class="bi bi-cash-coin me-1"></i> Teller</span>
                            @elseif($user->role === 'kepsek')
                                <span class="badge badge-pastel-warning"><i class="bi bi-building me-1"></i> Kepsek</span>
                            @else
                                <span class="badge badge-pastel-lavender"><i class="bi bi-mortarboard me-1"></i> Murid</span>
                            @endif
                        </td>
                        <td>
                            @if($user->student)
                                <a href="{{ route('management.students.show', $user->student) }}" class="text-decoration-none fw-medium text-dark">
                                    {{ $user->student->name }}
                                </a>
                                <small class="d-block text-muted">NIS: {{ $user->student->nis }} &bull; {{ $user->student->classRoom?->name }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $user->phone ?: '-' }}</td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('management.users.edit', $user) }}" class="btn btn-sm btn-light" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                @if($user->id !== auth()->id())
                                    <form action="{{ route('management.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light text-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Tidak ada pengguna yang cocok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
        <div class="card-footer d-flex justify-content-end py-2">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
