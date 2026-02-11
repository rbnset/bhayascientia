<?php
// app/Http/Controllers/SubmissionGuidelineController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SubmissionGuidelineController extends Controller
{
    public function index()
    {
        // Data untuk submission guidelines
        $data = [
            'timeline' => [
                [
                    'step' => 'Registrasi & Login',
                    'duration' => 'Instant',
                    'description' => 'Buat akun dan verifikasi email'
                ],
                [
                    'step' => 'Submit Naskah',
                    'duration' => '15-30 menit',
                    'description' => 'Upload file dan isi metadata'
                ],
                [
                    'step' => 'Initial Review',
                    'duration' => '1-2 hari kerja',
                    'description' => 'Cek kelengkapan dokumen'
                ],
                [
                    'step' => 'Peer Review',
                    'duration' => '3-5 hari kerja',
                    'description' => 'Review oleh tim ahli'
                ],
                [
                    'step' => 'Revisi (jika perlu)',
                    'duration' => '1-7 hari',
                    'description' => 'Perbaikan sesuai feedback'
                ],
                [
                    'step' => 'Final Review',
                    'duration' => '1-2 hari kerja',
                    'description' => 'Verifikasi revisi'
                ],
                [
                    'step' => 'Publikasi',
                    'duration' => '24 jam',
                    'description' => 'Naskah tayang otomatis'
                ],
            ],

            'requirements' => [
                'jurnal' => [
                    'name' => 'Jurnal Ilmiah',
                    'formats' => ['PDF', 'DOCX'],
                    'max_size' => '10 MB',
                    'min_pages' => 8,
                    'max_pages' => 25,
                    'checklist' => [
                        'Abstrak dalam Bahasa Indonesia dan Inggris (max 250 kata)',
                        'Kata kunci 3-5 kata',
                        'Pendahuluan dengan latar belakang jelas',
                        'Metode penelitian yang detail',
                        'Hasil dan pembahasan',
                        'Kesimpulan dan saran',
                        'Daftar pustaka minimal 15 referensi (max 10 tahun terakhir)'
                    ]
                ],
                'buku' => [
                    'name' => 'Buku',
                    'formats' => ['PDF', 'DOCX'],
                    'max_size' => '50 MB',
                    'min_pages' => 50,
                    'max_pages' => null,
                    'checklist' => [
                        'Cover depan dan belakang',
                        'Daftar isi lengkap',
                        'Kata pengantar',
                        'ISBN (jika ada)',
                        'Biodata penulis',
                        'Daftar pustaka'
                    ]
                ],
                'opini' => [
                    'name' => 'Opini/Artikel',
                    'formats' => ['PDF', 'DOCX'],
                    'max_size' => '5 MB',
                    'min_pages' => 3,
                    'max_pages' => 10,
                    'checklist' => [
                        'Judul yang menarik dan deskriptif',
                        'Pembukaan yang engaging',
                        'Argumen yang kuat dengan data pendukung',
                        'Referensi yang kredibel',
                        'Kesimpulan yang jelas'
                    ]
                ]
            ],

            'format_guidelines' => [
                'font' => 'Times New Roman atau Arial',
                'font_size' => '12pt untuk isi, 14pt untuk judul',
                'spacing' => '1.5 atau double space',
                'margins' => '2.5 cm semua sisi',
                'citation' => 'APA, IEEE, atau Harvard (konsisten)'
            ],

            'review_criteria' => [
                'Originalitas dan kebaruan penelitian/konten',
                'Kualitas metodologi (untuk penelitian)',
                'Kekuatan argumen dan analisis',
                'Kelengkapan referensi',
                'Keterbacaan dan struktur penulisan',
                'Kontribusi terhadap bidang ilmu'
            ],

            'tips' => [
                [
                    'icon' => '📝',
                    'title' => 'Persiapkan dengan Matang',
                    'desc' => 'Pastikan naskah sudah diproofread dan cek plagiarism sebelum submit'
                ],
                [
                    'icon' => '📚',
                    'title' => 'Referensi Terkini',
                    'desc' => 'Gunakan referensi relevan dan terbaru (maksimal 10 tahun terakhir)'
                ],
                [
                    'icon' => '💬',
                    'title' => 'Responsif Terhadap Feedback',
                    'desc' => 'Tanggapi feedback reviewer dengan cepat dan konstruktif'
                ],
                [
                    'icon' => '🎯',
                    'title' => 'Ikuti Format',
                    'desc' => 'Patuhi template dan guidelines yang diberikan'
                ]
            ]
        ];

        return view('pages.submission-guidelines', $data);
    }
}
