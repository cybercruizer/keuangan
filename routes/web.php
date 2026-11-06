<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillTypeController;
use App\Http\Controllers\CashFlowController;
use App\Http\Controllers\ClassRoomController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MajorController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SchoolSettingController;
use App\Http\Controllers\StudentBillController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Phase 4: Billing Module (Tagihan & Diskon)
    Route::prefix('tagihan')->name('billing.')->group(function () {
        // Template & Import Jenis Tagihan
        Route::get('bill-types/template', [BillTypeController::class, 'downloadTemplate'])
            ->name('bill-types.template')
            ->middleware('role:admin');
        Route::post('bill-types/import', [BillTypeController::class, 'import'])
            ->name('bill-types.import')
            ->middleware('role:admin');

        // Jenis Tagihan & Aturan Diskon
        Route::resource('bill-types', BillTypeController::class)
            ->except(['show'])
            ->middleware('role:admin');

        // Tagihan Siswa (Generate & Kelola)
        Route::get('student-bills', [StudentBillController::class, 'index'])
            ->name('student-bills.index')
            ->middleware('role:admin,teller,kepsek');
        Route::get('student-bills/create', [StudentBillController::class, 'create'])
            ->name('student-bills.create')
            ->middleware('role:admin,teller');
        Route::post('student-bills', [StudentBillController::class, 'store'])
            ->name('student-bills.store')
            ->middleware('role:admin,teller');
        Route::delete('student-bills/{studentBill}', [StudentBillController::class, 'destroy'])
            ->name('student-bills.destroy')
            ->middleware('role:admin');
    });

    // Phase 5: Payment Module & Kuitansi A5 Landscape
    Route::prefix('pembayaran')->name('payments.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])
            ->name('index')
            ->middleware('role:admin,teller,kepsek');
        Route::get('search-students', [PaymentController::class, 'searchStudents'])
            ->name('search-students')
            ->middleware('role:admin,teller');
        Route::get('create', [PaymentController::class, 'create'])
            ->name('create')
            ->middleware('role:admin,teller');
        Route::post('/', [PaymentController::class, 'store'])
            ->name('store')
            ->middleware('role:admin,teller');
        Route::get('{payment}', [PaymentController::class, 'show'])
            ->name('show')
            ->middleware('role:admin,teller,kepsek');
        Route::get('{payment}/kuitansi', [PaymentController::class, 'receipt'])
            ->name('receipt')
            ->middleware('role:admin,teller,kepsek,murid');
    });

    // Phase 7: Reports & Recaps (Laporan Arus Kas & Rekap Pembayaran)
    Route::prefix('laporan')->name('reports.')->group(function () {
        // Laporan Arus Kas
        Route::get('arus-kas', [CashFlowController::class, 'index'])
            ->name('cash-flows.index')
            ->middleware('role:admin,teller,kepsek');
        Route::post('arus-kas', [CashFlowController::class, 'store'])
            ->name('cash-flows.store')
            ->middleware('role:admin,teller');
        Route::delete('arus-kas/{cashFlow}', [CashFlowController::class, 'destroy'])
            ->name('cash-flows.destroy')
            ->middleware('role:admin');
        Route::get('arus-kas/cetak', [CashFlowController::class, 'print'])
            ->name('cash-flows.print')
            ->middleware('role:admin,teller,kepsek');

        // Rekap Pembayaran
        Route::get('rekap/siswa', [ReportController::class, 'students'])
            ->name('recaps.students')
            ->middleware('role:admin,teller,kepsek');
        Route::get('rekap/rombel', [ReportController::class, 'classRooms'])
            ->name('recaps.class-rooms')
            ->middleware('role:admin,teller,kepsek');
        Route::get('rekap/jenis-tagihan', [ReportController::class, 'billTypes'])
            ->name('recaps.bill-types')
            ->middleware('role:admin,teller,kepsek');
    });

    // Phase 7: School Settings (Pengaturan Identitas Sekolah)
    Route::prefix('pengaturan')->name('settings.')->middleware('role:admin')->group(function () {
        Route::get('/', [SchoolSettingController::class, 'index'])->name('index');
        Route::put('/', [SchoolSettingController::class, 'update'])->name('update');
    });

    // Phase 3: Management Module (Admin, Teller, Kepsek)
    Route::prefix('manajemen')->name('management.')->group(function () {
        // Academic Years
        Route::post('academic-years/{academicYear}/set-active', [AcademicYearController::class, 'setActive'])
            ->name('academic-years.set-active')
            ->middleware('role:admin');
        Route::resource('academic-years', AcademicYearController::class)
            ->except(['show'])
            ->middleware('role:admin,kepsek');

        // Majors
        Route::resource('majors', MajorController::class)
            ->except(['show'])
            ->middleware('role:admin,kepsek');

        // Class Rooms (Rombel)
        Route::get('class-rooms/template', [ClassRoomController::class, 'downloadTemplate'])
            ->name('class-rooms.template')
            ->middleware('role:admin,teller,kepsek');
        Route::post('class-rooms/import', [ClassRoomController::class, 'import'])
            ->name('class-rooms.import')
            ->middleware('role:admin,teller');
        Route::resource('class-rooms', ClassRoomController::class)
            ->except(['show'])
            ->middleware('role:admin,teller,kepsek');

        // Students (Murid)
        Route::get('students/template', [StudentController::class, 'downloadTemplate'])
            ->name('students.template')
            ->middleware('role:admin,teller,kepsek');
        Route::post('students/import', [StudentController::class, 'import'])
            ->name('students.import')
            ->middleware('role:admin,teller');
        Route::resource('students', StudentController::class)
            ->middleware('role:admin,teller,kepsek');

        // Users
        Route::resource('users', UserController::class)
            ->except(['show'])
            ->middleware('role:admin');
    });
});
