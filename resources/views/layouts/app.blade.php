<!doctype html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'BHAYASCIENTIA') - Platform Publikasi Ilmiah Indonesia</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap">

    {{-- ✅ TAMBAHKAN Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @stack('styles')
</head>

<body class="m-0 bg-F8F9FC font-Poppins text-0B0B0B">
    {{-- ✅ Navbar: Conditional rendering untuk custom navbar --}}
    @hasSection('custom_navbar')
    {{-- Jika page define custom navbar, render dari section --}}
    @yield('custom_navbar')
    @else
    {{-- Default navbar untuk semua page lainnya --}}
    <x-navbar />
    @endif

    {{-- Main content --}}
    <main class="@yield('main_class', 'mt-10 sm:mt-14')">
        @yield('content')
    </main>

    {{-- Footer: Hidden jika ada section 'hide_footer' --}}
    @if (trim($__env->yieldContent('hide_footer')) !== 'true')
    <x-layouts.footer />
    @endif

    {{-- Bottom navigation stack (untuk mobile bottom nav di publikasi) --}}
    @stack('bottom_nav')

    {{-- Additional scripts --}}
    @stack('scripts')
</body>

</html>
