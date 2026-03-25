<!doctype html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- ✅ Title --}}
    <title>@yield('title', 'DABARKA') - Platform Publikasi Ilmiah Indonesia</title>

    {{-- ✅ SEO Basic --}}
    <meta name="description" content="@yield('description', 'Platform Publikasi Ilmiah Indonesia Dabarka')">
    {{-- ✅ Open Graph FIX --}}
    <meta property="og:title" content="Selamat Datang - DABARKA">
    <meta property="og:description" content="Platform Publikasi Ilmiah Indonesia Dabarka">
    <meta property="og:image" content="https://dabraka.org/assets/images/logos/logo-brand.png">
    <meta property="og:url" content="https://dabraka.org">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="DABARKA">

    {{-- ✅ WA tambahan penting --}}
    <meta property="og:image:secure_url" content="https://dabraka.org/assets/images/logos/logo-brand.png">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    {{-- ✅ Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Selamat Datang - DABARKA">
    <meta name="twitter:description" content="Platform Publikasi Ilmiah Indonesia Dabarka">
    <meta name="twitter:image" content="https://dabraka.org/assets/images/logos/logo-brand.png">

    {{-- ✅ Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap">

    {{-- ✅ Flickity CSS for Carousel --}}
    <link rel="stylesheet" href="https://unpkg.com/flickity@2/dist/flickity.min.css">

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- ✨ BASE STYLES untuk prevent horizontal scroll --}}
    <style>
        /* Prevent horizontal scroll */
        html,
        body {
            max-width: 100vw;
            overflow-x: hidden;
        }

        body {
            position: relative;
        }

        /* Smooth scroll */
        html {
            scroll-behavior: smooth;
        }

        /* Fix untuk element yang keluar viewport */
        * {
            box-sizing: border-box;
        }

        /* Container default max width */
        .container-safe {
            max-width: 100%;
            overflow-x: hidden;
        }

        /* ✅ Flickity carousel fixes */
        .flickity-enabled {
            position: relative;
        }

        .flickity-enabled:focus {
            outline: none;
        }

        .flickity-viewport {
            overflow: hidden;
            position: relative;
            height: 100%;
        }

        .flickity-slider {
            position: absolute;
            width: 100%;
            height: 100%;
        }

        /* Fix untuk gambar carousel tidak muncul */
        .main-carousel .featured-news-card {
            width: 100%;
            min-height: 420px;
        }

        .main-carousel img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Custom flickity button styles */
        .flickity-button {
            background: transparent;
            border: none;
            color: white;
        }

        .flickity-button:hover {
            background: transparent;
        }

        .flickity-button:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .flickity-prev-next-button {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
        }

        .flickity-prev-next-button:hover {
            background: rgba(255, 107, 24, 0.9);
        }

        .flickity-prev-next-button .flickity-button-icon {
            position: absolute;
            left: 20%;
            top: 20%;
            width: 60%;
            height: 60%;
        }
    </style>

    @stack('styles')
</head>

<body class="m-0 antialiased bg-F8F9FC font-Poppins text-0B0B0B">
    {{-- Wrapper untuk prevent overflow --}}
    <div class="flex flex-col min-h-screen overflow-x-hidden">

        {{-- Navbar --}}
        @hasSection('custom_navbar')
        @yield('custom_navbar')
        @else
        <x-navbar />
        @endif

        {{-- Main content --}}
        <main class="flex-1 @yield('main_class', 'mt-10 sm:mt-14') overflow-x-hidden">
            @yield('content')
        </main>

        {{-- Footer --}}
        @if (trim($__env->yieldContent('hide_footer')) !== 'true')
        <x-layouts.footer />
        @endif
    </div>

    {{-- Bottom navigation stack --}}
    @stack('bottom_nav')

    {{-- ✅ Flickity JS for Carousel (before custom scripts) --}}
    <script src="https://unpkg.com/flickity@2/dist/flickity.pkgd.min.js"></script>

    {{-- Scripts --}}
    @stack('scripts')
</body>

</html>