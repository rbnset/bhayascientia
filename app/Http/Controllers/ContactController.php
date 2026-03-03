<?php

namespace App\Http\Controllers;

use App\Mail\ContactAutoReplyMail;
use App\Mail\ContactFormMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function index()
    {
        return view('pages.contact');
    }

    public function submit(Request $request)
    {
        // ── 1. Honeypot anti-bot ──────────────────────────────────────────────
        if ($request->filled('website')) {
            return back()->with('success', 'Terima kasih! Pesan Anda telah berhasil dikirim.');
        }

        // ── 2. Verifikasi reCAPTCHA v3 ────────────────────────────────────────
        $recaptchaToken = $request->input('recaptcha_token');

        if ($recaptchaToken) {
            try {
                $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret'   => config('services.recaptcha.secret_key'),
                    'response' => $recaptchaToken,
                    'remoteip' => $request->ip(),
                ]);

                $result = $response->json();
                $score  = $result['score'] ?? 0;

                Log::info('reCAPTCHA result', [
                    'success' => $result['success'] ?? false,
                    'score'   => $score,
                    'action'  => $result['action'] ?? null,
                    'ip'      => $request->ip(),
                ]);

                if (!($result['success'] ?? false) || $score < 0.5) {
                    Log::warning('reCAPTCHA bot detected', [
                        'score' => $score,
                        'ip'    => $request->ip(),
                    ]);

                    return back()
                        ->withInput()
                        ->with('error', 'Verifikasi keamanan gagal. Silakan coba lagi.');
                }
            } catch (\Exception $e) {
                Log::warning('reCAPTCHA verification error', ['error' => $e->getMessage()]);
            }
        }

        // ── 3. Validasi input ─────────────────────────────────────────────────
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'email', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:20', 'regex:/^[0-9\+\-\s\(\)]+$/'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ], [
            'name.required'    => 'Nama lengkap wajib diisi.',
            'email.required'   => 'Email wajib diisi.',
            'email.email'      => 'Format email tidak valid.',
            'subject.required' => 'Subjek pesan wajib diisi.',
            'message.required' => 'Pesan wajib diisi.',
            'message.min'      => 'Pesan minimal 10 karakter.',
            'message.max'      => 'Pesan maksimal 2000 karakter.',
            'phone.regex'      => 'Format nomor telepon tidak valid.',
        ]);

        // ── 4. Sanitasi input ─────────────────────────────────────────────────
        $validated['name']    = strip_tags($validated['name']);
        $validated['subject'] = strip_tags($validated['subject']);
        $validated['message'] = strip_tags($validated['message']);
        $validated['phone']   = $validated['phone'] ? strip_tags($validated['phone']) : null;

        // ── 5. Anti-duplicate submission ──────────────────────────────────────
        // Hash dibuat dari email + subject + 100 karakter pertama pesan
        // Tujuan: konten sama dari email sama dalam 5 menit = duplikat
        $duplicateKey = 'contact_duplicate:' . md5(
            strtolower(trim($validated['email'])) .
                strtolower(trim($validated['subject'])) .
                substr(strtolower(trim($validated['message'])), 0, 100)
        );

        if (Cache::has($duplicateKey)) {
            Log::info('Duplicate contact form submission blocked', [
                'email'   => $validated['email'],
                'subject' => $validated['subject'],
                'ip'      => $request->ip(),
            ]);

            return back()->with('error', 'Pesan serupa sudah pernah dikirim. Silakan tunggu beberapa menit sebelum mengirim ulang.');
        }

        // ── 6. Kirim email ────────────────────────────────────────────────────
        try {
            Mail::to(config('mail.admin_email'))
                ->send(new ContactFormMail($validated));

            Mail::to($validated['email'])
                ->send(new ContactAutoReplyMail($validated));

            // Simpan hash ke cache selama 5 menit setelah email berhasil terkirim
            Cache::put($duplicateKey, true, now()->addMinutes(5));

            Log::info('Contact form submitted', [
                'name'    => $validated['name'],
                'email'   => $validated['email'],
                'subject' => $validated['subject'],
                'ip'      => $request->ip(),
            ]);

            return back()->with('success', 'Terima kasih! Pesan Anda telah berhasil dikirim. Kami akan segera menghubungi Anda.');
        } catch (\Exception $e) {
            Log::error('Contact form email error', [
                'error' => $e->getMessage(),
                'email' => $validated['email'],
                'ip'    => $request->ip(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Maaf, terjadi kesalahan saat mengirim pesan. Silakan coba lagi.');
        }
    }
}
