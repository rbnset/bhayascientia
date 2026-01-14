<!doctype html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'BHAYASCIENTIA')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap">

    @stack('styles')
</head>

<body class="m-0 bg-F8F9FC font-Poppins text-0B0B0B">
    <x-navbar />

    {{-- TIDAK ADA pb global --}}
    <main class="@yield('main_class', 'mt-10 sm:mt-14')">
        @yield('content')
    </main>

    @if (trim($__env->yieldContent('hide_footer')) !== 'true')
    <x-layouts.footer />
    @endif

    {{-- Slot optional: hanya muncul jika halaman melakukan @push('bottom_nav') --}}
    @stack('bottom_nav')

    @stack('scripts')
</body>

</html>