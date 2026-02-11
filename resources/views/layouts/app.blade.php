<!doctype html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'BHAYASCIENTIA') - Platform Publikasi Ilmiah Indonesia</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap">

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

    {{-- Scripts --}}
    @stack('scripts')
</body>

</html>
