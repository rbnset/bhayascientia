{{-- resources/views/components/publication-navbar.blade.php --}}
<div class="border-b border-[#EEF0F7] bg-white sticky top-[88px] z-40">
    <div class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px]">
        <div class="flex items-center justify-between py-3">
            {{-- Left: Navigation Tabs --}}
            <nav class="flex items-center gap-2" aria-label="Publikasi navigation">
                <a href="{{ route('publikasi.index') }}"
                    class="px-4 py-2 text-sm font-semibold rounded-lg transition-all {{ request()->routeIs('publikasi.index') ? 'bg-[#FFF7F2] text-[#FF6B18]' : 'text-[#737373] hover:bg-[#F8F9FC]' }}">
                    Browse
                </a>
                <a href="{{ route('publikasi.categories') }}"
                    class="px-4 py-2 text-sm font-semibold rounded-lg transition-all {{ request()->routeIs('publikasi.categories') ? 'bg-[#FFF7F2] text-[#FF6B18]' : 'text-[#737373] hover:bg-[#F8F9FC]' }}">
                    Categories
                </a>
                <a href="{{ route('publikasi.trending') }}"
                    class="px-4 py-2 text-sm font-semibold rounded-lg transition-all {{ request()->routeIs('publikasi.trending') ? 'bg-[#FFF7F2] text-[#FF6B18]' : 'text-[#737373] hover:bg-[#F8F9FC]' }}">
                    Trending
                </a>
                @auth
                <a href="{{ route('publikasi.library') }}"
                    class="px-4 py-2 text-sm font-semibold rounded-lg transition-all {{ request()->routeIs('publikasi.library') ? 'bg-[#FFF7F2] text-[#FF6B18]' : 'text-[#737373] hover:bg-[#F8F9FC]' }}">
                    My Library
                </a>
                @endauth
            </nav>

            {{-- Right: Actions --}}
            <div class="flex items-center gap-3">
                @auth
                {{-- Subscription Bell --}}
                <button onclick="openSubscriptionModal()"
                    class="relative p-2 rounded-lg hover:bg-[#F8F9FC] transition-all group"
                    title="Berlangganan Newsletter">
                    <svg class="w-5 h-5 text-[#737373] group-hover:text-[#FF6B18] transition-colors" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    @if(auth()->user()->has_active_subscription)
                    <span class="absolute top-1 right-1 w-2 h-2 bg-[#FF6B18] rounded-full animate-pulse"></span>
                    @endif
                </button>

                {{-- User Menu --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                        class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-[#F8F9FC] transition-all">
                        <img src="{{ auth()->user()->avatar }}" alt="User" class="rounded-full w-7 h-7">
                        <span class="text-sm font-semibold text-[#1A1A1A] hidden sm:block">{{ auth()->user()->name
                            }}</span>
                    </button>

                    {{-- Dropdown --}}
                    <div x-show="open" @click.away="open = false"
                        class="absolute right-0 mt-2 w-56 bg-white rounded-xl border border-[#EEF0F7] shadow-xl py-2">
                        <a href="{{ route('profile') }}" class="flex items-center gap-3 px-4 py-2 hover:bg-[#F8F9FC]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span class="text-sm">Profil Saya</span>
                        </a>
                        <a href="{{ route('subscription.manage') }}"
                            class="flex items-center gap-3 px-4 py-2 hover:bg-[#F8F9FC]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span class="text-sm">Kelola Berlangganan</span>
                        </a>
                        <hr class="my-2 border-[#EEF0F7]">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="flex items-center gap-3 px-4 py-2 hover:bg-[#FFF7F2] text-[#FF6B18] w-full text-left">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span class="text-sm font-semibold">Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
                @else
                <button onclick="openAuthModal('login')"
                    class="px-4 py-2 text-sm font-semibold text-[#737373] hover:text-[#FF6B18] transition-colors">
                    Login
                </button>
                <button onclick="openAuthModal('register')"
                    class="px-4 py-2 text-sm font-bold text-white bg-gradient-to-r from-[#FF6B18] to-[#E64627] rounded-lg hover:shadow-[0_6px_12px_0_#FF6B1860] transition-all">
                    Daftar Gratis
                </button>
                @endauth
            </div>
        </div>
    </div>
</div>
