<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function index()
    {
        return view('pages.contact');
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ], [
            'name.required' => 'Nama lengkap wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'subject.required' => 'Subjek pesan wajib diisi',
            'message.required' => 'Pesan wajib diisi',
            'message.max' => 'Pesan maksimal 2000 karakter',
        ]);

        try {
            // Log contact submission
            Log::info('Contact form submitted', [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'subject' => $validated['subject'],
            ]);

            // TODO: Send email notification
            Mail::to(config('mail.admin_email'))->send(new ContactFormMail($validated));

            return back()->with('success', 'Terima kasih! Pesan Anda telah berhasil dikirim. Kami akan segera menghubungi Anda.');
        } catch (\Exception $e) {
            Log::error('Contact form error: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Maaf, terjadi kesalahan saat mengirim pesan. Silakan coba lagi.');
        }
    }
}
