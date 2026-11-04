<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SI-Keuangan SMK Muhammadiyah Mungkid</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100" style="background-color: var(--pastel-bg);">
    <div class="container" style="max-width: 440px;">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center mb-3 rounded-circle" style="width: 68px; height: 68px; background-color: var(--pastel-primary-light); color: var(--pastel-primary);">
                <i class="bi bi-wallet2" style="font-size: 2rem;"></i>
            </div>
            <h4 class="fw-bold mb-1" style="color: var(--pastel-text);">SI-Keuangan Siswa</h4>
            <p class="text-muted small mb-0">SMK Muhammadiyah Mungkid</p>
        </div>

        <div class="card p-4">
            @if(session('info'))
                <div class="alert alert-info border-0 shadow-none mb-3 py-2 px-3 small" style="background-color: var(--pastel-accent-light); color: var(--pastel-accent-text); border-radius: 8px;">
                    {{ session('info') }}
                </div>
            @endif

            @if($errors->has('login'))
                <div class="alert alert-danger border-0 shadow-none mb-3 py-2 px-3 small" style="background-color: var(--pastel-danger-light); color: var(--pastel-danger-text); border-radius: 8px;">
                    {{ $errors->first('login') }}
                </div>
            @endif

            <form action="{{ route('login.attempt') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="login" class="form-label">Email atau Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 8px 0 0 8px;">
                            <i class="bi bi-person"></i>
                        </span>
                        <input type="text"
                               name="login"
                               id="login"
                               class="form-control border-start-0 @error('login') is-invalid @enderror"
                               style="border-radius: 0 8px 8px 0;"
                               placeholder="admin / teller / kepsek / NIS"
                               value="{{ old('login', 'admin') }}"
                               required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 8px 0 0 8px;">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password"
                               name="password"
                               id="password"
                               class="form-control border-start-0"
                               style="border-radius: 0 8px 8px 0;"
                               placeholder="Masukkan password"
                               value="password"
                               required>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label small text-muted" for="remember">
                            Ingat Saya
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-pastel-primary w-100 py-2">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Masuk ke Sistem
                </button>
            </form>

            <hr class="my-4 text-muted opacity-25">

            <div>
                <small class="text-muted fw-semibold d-block mb-2 text-center" style="font-size: 0.75rem;">AKUN DEMO CEPAT (Klik untuk mengisi):</small>
                <div class="d-flex flex-wrap gap-1 justify-content-center">
                    <button type="button" class="btn btn-sm btn-light py-1 px-2 small" onclick="fillLogin('admin', 'password')">
                        <span class="badge badge-pastel-primary me-1">Admin</span> admin
                    </button>
                    <button type="button" class="btn btn-sm btn-light py-1 px-2 small" onclick="fillLogin('teller', 'password')">
                        <span class="badge badge-pastel-accent me-1">Teller</span> teller
                    </button>
                    <button type="button" class="btn btn-sm btn-light py-1 px-2 small" onclick="fillLogin('kepsek', 'password')">
                        <span class="badge badge-pastel-warning me-1">Kepsek</span> kepsek
                    </button>
                    <button type="button" class="btn btn-sm btn-light py-1 px-2 small" onclick="fillLogin('10201', 'password')">
                        <span class="badge badge-pastel-lavender me-1">Murid</span> 10201
                    </button>
                </div>
            </div>
        </div>

        <div class="text-center mt-3 text-muted small">
            &copy; {{ date('Y') }} SMK Muhammadiyah Mungkid. Sistem Informasi Keuangan.
        </div>
    </div>

    <script>
        function fillLogin(user, pass) {
            document.getElementById('login').value = user;
            document.getElementById('password').value = pass;
        }
    </script>
</body>
</html>
