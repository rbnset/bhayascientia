{{--
resources/views/filament/reviews/pdf-viewer-missing.blade.php

FIX BUG 2: Ditampilkan di dalam modal ketika publicationVersion null
agar tidak crash saat route() dipanggil dengan null.
--}}
<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;
            gap:1rem;text-align:center;padding:3rem 2rem;min-height:300px;
            font-family:ui-sans-serif,system-ui,sans-serif;">
    <div style="font-size:3rem;">⚠️</div>
    <p style="color:#1F2937;font-weight:700;font-size:1rem;margin:0;">
        File PDF tidak ditemukan
    </p>
    <p style="color:#6B7280;font-size:.875rem;max-width:380px;line-height:1.5;margin:0;">
        Versi publikasi tidak terhubung ke review ini, atau file PDF belum tersedia.
        Silakan hubungi administrator jika ini tidak seharusnya terjadi.
    </p>
</div>