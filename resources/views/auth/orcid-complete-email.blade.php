@extends('layouts.app')

@section('title', 'Lengkapi Akun ORCID')

@section('content')
<div class="flex items-center justify-center min-h-[70vh] px-4">
    <div class="w-full max-w-md p-6 bg-white shadow rounded-xl">

        {{-- Logo ORCID --}}
        <div class="flex items-center gap-2 mb-6">
            <img src="https://orcid.org/sites/default/files/images/orcid_16x16.png" alt="ORCID" class="w-6 h-6">
            <span class="text-sm text-gray-500">Terhubung via ORCID</span>
        </div>

        <h1 class="mb-1 text-xl font-bold text-gray-800">
            Satu langkah lagi!
        </h1>
        <p class="mb-6 text-sm text-gray-500">
            ORCID tidak membagikan email Anda. Masukkan email untuk melengkapi akun.
        </p>

        @if ($errors->any())
        <div class="p-3 mb-4 text-sm text-red-600 rounded-lg bg-red-50">
            {{ $errors->first() }}
        </div>
        @endif

        @if (session('info'))
        <div class="p-3 mb-4 text-sm text-blue-600 rounded-lg bg-blue-50">
            {{ session('info') }}
        </div>
        @endif

        <form action="{{ route('orcid.complete-email.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block mb-1 text-sm font-medium text-gray-700">
                    Alamat Email
                </label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com"
                    class="w-full px-3 py-2 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required autofocus>
            </div>

            <button type="submit"
                class="w-full py-2 text-sm font-medium text-white transition bg-blue-600 rounded-lg hover:bg-blue-700">
                Simpan & Lanjutkan
            </button>
        </form>

        <form action="{{ route('logout') }}" method="POST" class="mt-4 text-center">
            @csrf
            <button type="submit" class="text-xs text-gray-400 hover:text-gray-600">
                Batal & Keluar
            </button>
        </form>

    </div>
</div>
@endsection