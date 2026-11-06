<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Informasi Keuangan') - SMK Muhammadiyah Mungkid</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>
    <div class="app-wrapper">
        <!-- Sidebar Backdrop (Mobile) -->
        <div class="sidebar-backdrop d-lg-none" id="sidebarBackdrop"></div>

        <!-- Sidebar -->
        <aside class="app-sidebar" id="appSidebar">
            <div class="d-flex align-items-center justify-content-between border-bottom">
                <a href="{{ route('dashboard') }}" class="app-sidebar-brand flex-grow-1 border-bottom-0">
                    <i class="bi bi-wallet2"></i>
                    <div class="lh-sm">
                        <span class="d-block fw-bold text-dark" style="font-size: 0.95rem;">SI-KEUANGAN</span>
                        <small class="text-muted fw-normal" style="font-size: 0.72rem;">SMK Muh Mungkid</small>
                    </div>
                </a>
                <button type="button" class="btn btn-sm btn-light text-muted d-lg-none me-3" id="sidebarCloseBtn" aria-label="Tutup Menu">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="sidebar-nav">
                <div class="nav-section-title">Menu Utama</div>
                <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') || request()->routeIs('home') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2"></i>
                    <span>Dashboard</span>
                </a>

                @if(auth()->user()->hasRole(['admin', 'teller']))
                    <div class="nav-section-title">Transaksi Kasir</div>
                    <a href="{{ route('payments.create') }}" class="sidebar-link {{ request()->routeIs('payments.create') ? 'active' : '' }}">
                        <i class="bi bi-cash-stack"></i>
                        <span>Bayar Tagihan</span>
                    </a>
                    <a href="{{ route('payments.index') }}" class="sidebar-link {{ request()->routeIs('payments.index') || request()->routeIs('payments.show') || request()->routeIs('payments.receipt') ? 'active' : '' }}">
                        <i class="bi bi-receipt"></i>
                        <span>Riwayat Pembayaran</span>
                    </a>
                @elseif(auth()->user()->hasRole('kepsek'))
                    <div class="nav-section-title">Transaksi Kasir</div>
                    <a href="{{ route('payments.index') }}" class="sidebar-link {{ request()->routeIs('payments.index') || request()->routeIs('payments.show') ? 'active' : '' }}">
                        <i class="bi bi-receipt"></i>
                        <span>Riwayat Pembayaran</span>
                    </a>
                @endif

                @if(auth()->user()->hasRole(['admin', 'teller', 'kepsek']))
                    <div class="nav-section-title">Manajemen Tagihan</div>
                    <a href="{{ route('billing.student-bills.index') }}" class="sidebar-link {{ request()->routeIs('billing.student-bills.*') ? 'active' : '' }}">
                        <i class="bi bi-journal-check"></i>
                        <span>Daftar Tagihan Siswa</span>
                    </a>
                    @if(auth()->user()->hasRole('admin'))
                        <a href="{{ route('billing.bill-types.index') }}" class="sidebar-link {{ request()->routeIs('billing.bill-types.*') ? 'active' : '' }}">
                            <i class="bi bi-tags"></i>
                            <span>Jenis & Tarif Tagihan</span>
                        </a>
                    @endif
                @endif

                @if(auth()->user()->hasRole(['admin', 'teller', 'kepsek']))
                    <div class="nav-section-title">Laporan & Rekap</div>
                    <a href="{{ route('reports.cash-flows.index') }}" class="sidebar-link {{ request()->routeIs('reports.cash-flows.*') ? 'active' : '' }}">
                        <i class="bi bi-bar-chart-line"></i>
                        <span>Laporan Arus Kas</span>
                    </a>
                    <a href="{{ route('reports.recaps.students') }}" class="sidebar-link {{ request()->routeIs('reports.recaps.students') ? 'active' : '' }}">
                        <i class="bi bi-person-lines-fill"></i>
                        <span>Rekap per Siswa</span>
                    </a>
                    <a href="{{ route('reports.recaps.class-rooms') }}" class="sidebar-link {{ request()->routeIs('reports.recaps.class-rooms') ? 'active' : '' }}">
                        <i class="bi bi-mortarboard"></i>
                        <span>Rekap per Rombel</span>
                    </a>
                    <a href="{{ route('reports.recaps.bill-types') }}" class="sidebar-link {{ request()->routeIs('reports.recaps.bill-types') ? 'active' : '' }}">
                        <i class="bi bi-pie-chart"></i>
                        <span>Rekap per Tagihan</span>
                    </a>
                @endif

                @if(auth()->user()->hasRole(['admin', 'teller', 'kepsek']))
                    <div class="nav-section-title">Manajemen Data</div>

                    @if(auth()->user()->hasRole(['admin', 'kepsek']))
                        <a href="{{ route('management.academic-years.index') }}" class="sidebar-link {{ request()->routeIs('management.academic-years.*') ? 'active' : '' }}">
                            <i class="bi bi-calendar-range"></i>
                            <span>Tahun Ajaran</span>
                        </a>
                        <a href="{{ route('management.majors.index') }}" class="sidebar-link {{ request()->routeIs('management.majors.*') ? 'active' : '' }}">
                            <i class="bi bi-diagram-3"></i>
                            <span>Jurusan</span>
                        </a>
                    @endif

                    <a href="{{ route('management.class-rooms.index') }}" class="sidebar-link {{ request()->routeIs('management.class-rooms.*') ? 'active' : '' }}">
                        <i class="bi bi-door-open"></i>
                        <span>Rombel & Kelas</span>
                    </a>

                    <a href="{{ route('management.students.index') }}" class="sidebar-link {{ request()->routeIs('management.students.*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i>
                        <span>Data Murid</span>
                    </a>

                    @if(auth()->user()->hasRole('admin'))
                        <a href="{{ route('management.users.index') }}" class="sidebar-link {{ request()->routeIs('management.users.*') ? 'active' : '' }}">
                            <i class="bi bi-person-gear"></i>
                            <span>Manajemen User</span>
                        </a>
                        <div class="nav-section-title">Pengaturan</div>
                        <a href="{{ route('settings.index') }}" class="sidebar-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                            <i class="bi bi-gear"></i>
                            <span>Identitas Sekolah</span>
                        </a>
                    @endif
                @endif
            </div>

            <!-- Footer User Info inside sidebar -->
            <div class="p-3 border-top d-flex align-items-center justify-content-between">
                <div class="lh-sm overflow-hidden me-2">
                    <span class="d-block fw-semibold text-truncate" style="font-size: 0.85rem;">{{ auth()->user()->name }}</span>
                    <span class="badge badge-pastel-primary text-uppercase" style="font-size: 0.68rem;">{{ auth()->user()->role }}</span>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-light text-danger" title="Logout">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="app-content">
            <!-- Topbar -->
            <header class="app-topbar">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-light border d-lg-none" id="sidebarToggleBtn" aria-label="Buka Menu" title="Menu Navigasi">
                        <i class="bi bi-list fs-5"></i>
                    </button>
                    <span class="fw-semibold text-secondary text-truncate" style="font-size: 0.95rem;">
                        @yield('header_title', 'Dashboard')
                    </span>
                </div>

                <div class="d-flex align-items-center gap-2">
                    @php
                        $topActiveYear = \App\Models\AcademicYear::where('is_active', true)->first();
                    @endphp
                    @if($topActiveYear)
                        <span class="badge badge-pastel-success d-none d-md-inline-flex align-items-center gap-1 py-2 px-3">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>TA: {{ $topActiveYear->name }}</span>
                        </span>
                    @endif

                    <div class="dropdown">
                        <button class="btn btn-sm btn-light dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                            <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border shadow-none" style="border-radius: 8px;">
                            <li><h6 class="dropdown-header">Akun ({{ ucfirst(auth()->user()->role) }})</h6></li>
                            <li><span class="dropdown-item-text text-muted small">{{ auth()->user()->email }}</span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2">
                                        <i class="bi bi-box-arrow-right"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Main Content Container -->
            <main class="app-main">
                <!-- Alerts -->
                @if(session('success'))
                    <div class="alert alert-success d-flex align-items-center justify-content-between border-0 shadow-none mb-4" style="background-color: var(--pastel-success-light); color: var(--pastel-success-text); border-radius: 8px;" role="alert">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill"></i>
                            <div>{{ session('success') }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger d-flex align-items-center justify-content-between border-0 shadow-none mb-4" style="background-color: var(--pastel-danger-light); color: var(--pastel-danger-text); border-radius: 8px;" role="alert">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <div>{{ session('error') }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="alert alert-info d-flex align-items-center justify-content-between border-0 shadow-none mb-4" style="background-color: var(--pastel-accent-light); color: var(--pastel-accent-text); border-radius: 8px;" role="alert">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-info-circle-fill"></i>
                            <div>{{ session('info') }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger border-0 shadow-none mb-4" style="background-color: var(--pastel-danger-light); color: var(--pastel-danger-text); border-radius: 8px;" role="alert">
                        <div class="fw-semibold mb-1 d-flex align-items-center gap-2">
                            <i class="bi bi-x-circle-fill"></i> Mohon periksa kesalahan input berikut:
                        </div>
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('appSidebar');
            const toggleBtn = document.getElementById('sidebarToggleBtn');
            const closeBtn = document.getElementById('sidebarCloseBtn');
            const backdrop = document.getElementById('sidebarBackdrop');

            function openSidebar() {
                if (sidebar) sidebar.classList.add('show');
                if (backdrop) backdrop.classList.add('show');
                document.body.classList.add('sidebar-open');
            }

            function closeSidebar() {
                if (sidebar) sidebar.classList.remove('show');
                if (backdrop) backdrop.classList.remove('show');
                document.body.classList.remove('sidebar-open');
            }

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    openSidebar();
                });
            }

            if (closeBtn) {
                closeBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    closeSidebar();
                });
            }

            if (backdrop) {
                backdrop.addEventListener('click', closeSidebar);
            }

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && sidebar && sidebar.classList.contains('show')) {
                    closeSidebar();
                }
            });

            if (sidebar) {
                sidebar.querySelectorAll('.sidebar-link').forEach(function (link) {
                    link.addEventListener('click', function () {
                        if (window.innerWidth < 992) {
                            closeSidebar();
                        }
                    });
                });
            }
        });
    </script>
</body>
</html>
