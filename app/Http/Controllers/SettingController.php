<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Tampilkan form pengaturan laundry
     */
    public function index()
    {
        $settings = [
            'store_name'       => Setting::get('store_name', 'Laundry Management System'),
            'store_tagline'    => Setting::get('store_tagline', 'Cepat, Bersih, & Terpercaya'),
            'store_city'       => Setting::get('store_city', 'Indonesia'),
            'store_phone'      => Setting::get('store_phone', '081234567890'),
            'store_address'    => Setting::get('store_address', 'Jl. Utama No. 1'),
            'receipt_footer'   => Setting::get('receipt_footer', 'Terima kasih atas kepercayaan Anda!'),
            'bank_info'        => Setting::get('bank_info', 'BCA: 1234567890 a/n Laundry Usaha'),
        ];

        return view('settings.index', compact('settings'));
    }

    /**
     * Simpan pembaruan pengaturan
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'store_name'     => 'required|string|max:100',
            'store_tagline'  => 'nullable|string|max:150',
            'store_city'     => 'nullable|string|max:100',
            'store_phone'    => 'nullable|string|max:30',
            'store_address'  => 'nullable|string|max:255',
            'receipt_footer' => 'nullable|string|max:255',
            'bank_info'      => 'nullable|string|max:255',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        return back()->with('success', 'Pengaturan identitas toko berhasil diperbarui!');
    }
}
