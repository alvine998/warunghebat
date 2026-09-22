<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('contact');
    }

    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'in:Pesanan,Pembayaran,Mitra,Bantuan Teknis,Lainnya'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'subject.required' => 'Pilih topik pesan.',
            'message.required' => 'Pesan wajib diisi.',
            'message.min' => 'Pesan minimal 10 karakter, ceritakan sedikit lebih detail ya.',
        ]);

        ContactMessage::create($validated);

        return redirect()->route('contact')->with(
            'success',
            'Pesan terkirim! Tim kami akan membalas ke emailmu maksimal 1×24 jam kerja.'
        );
    }
}
