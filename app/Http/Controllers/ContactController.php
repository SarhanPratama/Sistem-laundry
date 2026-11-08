<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function show()
    {
        return view('marketing.contact');
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'contact' => ['required', 'email', 'max:120'],
            'business_name' => ['nullable', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:2000'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'contact.required' => 'Alamat email aktif wajib diisi agar tim kami dapat membalas pesan Anda.',
            'contact.email' => 'Format alamat email tidak valid (contoh: nama@email.com).',
            'message.required' => 'Isi pertanyaan atau pesan wajib diisi.',
        ]);

        ContactMessage::create($validated);

        return redirect()->route('marketing.contact')
            ->with('success', 'Pesan Anda sudah diterima! Tim kami akan mengirimkan balasan langsung ke alamat email ' . $validated['contact'] . '.');
    }
}
