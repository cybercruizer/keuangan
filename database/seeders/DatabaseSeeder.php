<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\BillType;
use App\Models\ClassRoom;
use App\Models\Discount;
use App\Models\Major;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. School Settings
        $settings = [
            'school_name' => 'SMK Muhammadiyah Mungkid',
            'school_address' => 'Jl. Pemandangan Indah No. 12, Mungkid, Magelang, Jawa Tengah',
            'school_phone' => '(0293) 788123',
            'school_email' => 'info@smkmuhmungkid.sch.id',
            'school_website' => 'https://smkmuhmungkid.sch.id',
            'headmaster_name' => 'Drs. H. Sukirman, M.Pd.',
            'headmaster_nip' => '19680512 199303 1 004',
            'treasurer_name' => 'Siti Aminah, S.E.',
        ];

        foreach ($settings as $key => $value) {
            SchoolSetting::set($key, $value);
        }

        // 2. Academic Years
        $ayPast = AcademicYear::create([
            'name' => '2024/2025',
            'is_active' => false,
            'start_date' => '2024-07-01',
            'end_date' => '2025-06-30',
        ]);

        $ayActive = AcademicYear::create([
            'name' => '2025/2026',
            'is_active' => true,
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
        ]);

        // 3. Majors
        $rpl = Major::create([
            'code' => 'RPL',
            'name' => 'Rekayasa Perangkat Lunak',
            'description' => 'Program Keahlian Pengembangan Perangkat Lunak dan Gim',
        ]);

        $tkro = Major::create([
            'code' => 'TKRO',
            'name' => 'Teknik Kendaraan Ringan Otomotif',
            'description' => 'Program Keahlian Otomotif Kendaraan Ringan',
        ]);

        $tbsm = Major::create([
            'code' => 'TBSM',
            'name' => 'Teknik Bisnis Sepeda Motor',
            'description' => 'Program Keahlian Otomotif Sepeda Motor',
        ]);

        $akl = Major::create([
            'code' => 'AKL',
            'name' => 'Akuntansi dan Keuangan Lembaga',
            'description' => 'Program Keahlian Akuntansi dan Keuangan Lembaga',
        ]);

        // 4. Class Rooms (Rombel)
        $rombelXRPL1 = ClassRoom::create(['academic_year_id' => $ayActive->id, 'major_id' => $rpl->id, 'name' => 'X RPL 1', 'level' => 'X']);
        $rombelXRPL2 = ClassRoom::create(['academic_year_id' => $ayActive->id, 'major_id' => $rpl->id, 'name' => 'X RPL 2', 'level' => 'X']);
        $rombelXIRPL = ClassRoom::create(['academic_year_id' => $ayActive->id, 'major_id' => $rpl->id, 'name' => 'XI RPL 1', 'level' => 'XI']);
        $rombelXIIRPL = ClassRoom::create(['academic_year_id' => $ayActive->id, 'major_id' => $rpl->id, 'name' => 'XII RPL 1', 'level' => 'XII']);

        $rombelXTKRO = ClassRoom::create(['academic_year_id' => $ayActive->id, 'major_id' => $tkro->id, 'name' => 'X TKRO 1', 'level' => 'X']);
        $rombelXITKRO = ClassRoom::create(['academic_year_id' => $ayActive->id, 'major_id' => $tkro->id, 'name' => 'XI TKRO 1', 'level' => 'XI']);

        $rombelXTBSM = ClassRoom::create(['academic_year_id' => $ayActive->id, 'major_id' => $tbsm->id, 'name' => 'X TBSM 1', 'level' => 'X']);
        $rombelXAKL = ClassRoom::create(['academic_year_id' => $ayActive->id, 'major_id' => $akl->id, 'name' => 'X AKL 1', 'level' => 'X']);

        // 5. Students
        $studentsData = [
            [
                'nis' => '10201',
                'nisn' => '0071234561',
                'name' => 'Ahmad Fauzi',
                'gender' => 'L',
                'class_room_id' => $rombelXRPL1->id,
                'category' => 'reguler',
                'phone' => '081234567890',
                'parent_name' => 'Bambang Sudiro',
                'parent_phone' => '081234567899',
                'address' => 'Mungkid, Magelang',
                'status' => 'aktif',
            ],
            [
                'nis' => '10202',
                'nisn' => '0071234562',
                'name' => 'Budi Santoso',
                'gender' => 'L',
                'class_room_id' => $rombelXRPL1->id,
                'category' => 'reguler',
                'phone' => '081234567891',
                'parent_name' => 'Hartono',
                'parent_phone' => '081234567898',
                'address' => 'Mertoyudan, Magelang',
                'status' => 'aktif',
            ],
            [
                'nis' => '10203',
                'nisn' => '0071234563',
                'name' => 'Citra Lestari',
                'gender' => 'P',
                'class_room_id' => $rombelXRPL1->id,
                'category' => 'reguler',
                'phone' => '081234567892',
                'parent_name' => 'Supriyanto',
                'parent_phone' => '081234567897',
                'address' => 'Sawangan, Magelang',
                'status' => 'aktif',
            ],
            [
                'nis' => '10204',
                'nisn' => '0071234564',
                'name' => 'Dewi Anggraeni',
                'gender' => 'P',
                'class_room_id' => $rombelXRPL1->id,
                'category' => 'beasiswa_50',
                'phone' => '081234567893',
                'parent_name' => 'Suryono',
                'parent_phone' => '081234567896',
                'address' => 'Borobudur, Magelang',
                'status' => 'aktif',
            ],
            [
                'nis' => '10205',
                'nisn' => '0071234565',
                'name' => 'Eko Prasetyo',
                'gender' => 'L',
                'class_room_id' => $rombelXRPL2->id,
                'category' => 'beasiswa_50',
                'phone' => '081234567894',
                'parent_name' => 'Wahyudi',
                'parent_phone' => '081234567895',
                'address' => 'Mungkid, Magelang',
                'status' => 'aktif',
            ],
            [
                'nis' => '10206',
                'nisn' => '0071234566',
                'name' => 'Farhan Rizki',
                'gender' => 'L',
                'class_room_id' => $rombelXRPL2->id,
                'category' => 'beasiswa_100',
                'phone' => '081234567801',
                'parent_name' => 'Suyadi',
                'parent_phone' => '081234567802',
                'address' => 'Muntilan, Magelang',
                'status' => 'aktif',
            ],
            [
                'nis' => '10207',
                'nisn' => '0071234567',
                'name' => 'Gita Permata',
                'gender' => 'P',
                'class_room_id' => $rombelXTKRO->id,
                'category' => 'beasiswa_100',
                'phone' => '081234567803',
                'parent_name' => 'Darsono',
                'parent_phone' => '081234567804',
                'address' => 'Tempuran, Magelang',
                'status' => 'aktif',
            ],
            [
                'nis' => '10208',
                'nisn' => '0071234568',
                'name' => 'Hendra Saputra',
                'gender' => 'L',
                'class_room_id' => $rombelXAKL->id,
                'category' => 'reguler',
                'phone' => '081234567805',
                'parent_name' => 'Santoso',
                'parent_phone' => '081234567806',
                'address' => 'Mungkid, Magelang',
                'status' => 'aktif',
            ],
        ];

        $createdStudents = [];
        foreach ($studentsData as $data) {
            $createdStudents[] = Student::create($data);
        }

        // 6. Users (Admin, Teller, Kepsek, Murid)
        User::create([
            'name' => 'Administrator Keuangan',
            'username' => 'admin',
            'email' => 'admin@smkmuhmungkid.sch.id',
            'role' => 'admin',
            'phone' => '081200000001',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Teller / Kasir Utama',
            'username' => 'teller',
            'email' => 'teller@smkmuhmungkid.sch.id',
            'role' => 'teller',
            'phone' => '081200000002',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Kepala Sekolah (Drs. H. Sukirman, M.Pd.)',
            'username' => 'kepsek',
            'email' => 'kepsek@smkmuhmungkid.sch.id',
            'role' => 'kepsek',
            'phone' => '081200000003',
            'password' => Hash::make('password'),
        ]);

        // Student User
        User::create([
            'name' => 'Ahmad Fauzi (Murid)',
            'username' => '10201',
            'email' => 'ahmad.fauzi@student.smkmuhmungkid.sch.id',
            'role' => 'murid',
            'student_id' => $createdStudents[0]->id,
            'phone' => '081234567890',
            'password' => Hash::make('password'),
        ]);

        // 7. Bill Types
        $spp = BillType::create([
            'name' => 'SPP Bulanan',
            'amount' => 250000,
            'type' => 'spp',
            'is_discountable' => true,
            'description' => 'Sumbangan Pembinaan Pendidikan per bulan',
        ]);

        $uangGedung = BillType::create([
            'name' => 'Uang Gedung / Sarpras',
            'amount' => 1500000,
            'type' => 'custom',
            'is_discountable' => true,
            'description' => 'Iuran Pengembangan Sarana Prasarana (dapat dicicil)',
        ]);

        $uangPraktik = BillType::create([
            'name' => 'Uang Praktik Kejuruan',
            'amount' => 350000,
            'type' => 'custom',
            'is_discountable' => false,
            'description' => 'Biaya bahan dan modul praktik kejuruan',
        ]);

        $tabungan = BillType::create([
            'name' => 'Tabungan Wajib Siswa',
            'amount' => 50000,
            'type' => 'tabungan',
            'is_discountable' => false,
            'description' => 'Setoran tabungan sukarela/wajib siswa',
        ]);

        // 8. Discounts
        Discount::create([
            'bill_type_id' => $spp->id,
            'student_category' => 'beasiswa_50',
            'percentage' => 50.00,
        ]);

        Discount::create([
            'bill_type_id' => $spp->id,
            'student_category' => 'beasiswa_100',
            'percentage' => 100.00,
        ]);

        Discount::create([
            'bill_type_id' => $uangGedung->id,
            'student_category' => 'beasiswa_50',
            'percentage' => 50.00,
        ]);

        Discount::create([
            'bill_type_id' => $uangGedung->id,
            'student_category' => 'beasiswa_100',
            'percentage' => 100.00,
        ]);
    }
}
