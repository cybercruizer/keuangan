<?php

namespace App\Http\Controllers;

use App\Models\SchoolSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchoolSettingController extends Controller
{
    public function index(): View
    {
        $settings = [
            'school_name' => SchoolSetting::get('school_name', 'SMK Muhammadiyah Mungkid'),
            'school_address' => SchoolSetting::get('school_address', 'Jl. Pemandangan Indah No. 12, Mungkid, Magelang, Jawa Tengah'),
            'school_phone' => SchoolSetting::get('school_phone', '(0293) 788123'),
            'school_email' => SchoolSetting::get('school_email', 'info@smkmuhmungkid.sch.id'),
            'school_website' => SchoolSetting::get('school_website', 'https://smkmuhmungkid.sch.id'),
            'headmaster_name' => SchoolSetting::get('headmaster_name', 'Drs. H. Sukirman, M.Pd.'),
            'headmaster_nip' => SchoolSetting::get('headmaster_nip', '19680512 199303 1 004'),
            'treasurer_name' => SchoolSetting::get('treasurer_name', 'Siti Aminah, S.E.'),
        ];

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'school_name' => ['required', 'string', 'max:150'],
            'school_address' => ['required', 'string', 'max:500'],
            'school_phone' => ['nullable', 'string', 'max:50'],
            'school_email' => ['nullable', 'email', 'max:100'],
            'school_website' => ['nullable', 'string', 'max:150'],
            'headmaster_name' => ['required', 'string', 'max:150'],
            'headmaster_nip' => ['nullable', 'string', 'max:50'],
            'treasurer_name' => ['required', 'string', 'max:150'],
        ]);

        foreach ($validated as $key => $value) {
            SchoolSetting::set($key, $value);
        }

        return back()->with('success', 'Pengaturan identitas sekolah berhasil diperbarui.');
    }
}
