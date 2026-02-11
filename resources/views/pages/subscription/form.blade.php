{{-- resources/views/pages/subscription/form.blade.php --}}

<div class="max-w-5xl mx-auto">
    <div class="bg-white rounded-2xl border-2 border-[#EEF0F7] p-6 sm:p-8 md:p-10 animate-slide-up">

        {{-- Header --}}
        <div class="mb-6 text-center sm:mb-8">
            <h2 class="text-2xl sm:text-3xl font-black text-[#1A1A1A] mb-3">
                {{ $isEditing ? '⚙️ Kelola Langganan' : '🎯 Mulai Berlangganan' }}
            </h2>
            <p class="text-sm sm:text-base text-[#737373]">
                {{ $isEditing ? 'Ubah preferensi langganan Anda kapan saja' : 'Pilih preferensi Anda dan mulai terima
                pembaruan publikasi terbaru' }}
            </p>
        </div>

        {{-- Selection Counter (Sticky) --}}
        <div id="selection-counter"
            class="hidden p-4 mb-6 border-2 border-blue-200 selection-counter bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl">
        </div>

        <form action="{{ $isEditing ? route('subscription.update') : route('subscription.store') }}" method="POST"
            id="subscription-form" class="space-y-6 sm:space-y-8">
            @csrf
            @if($isEditing)
            @method('PUT')
            @endif

            {{-- ✅ STEP 1: Publication Types (Max 3) --}}
            <div>
                <div class="flex items-center justify-between mb-4">
                    <label class="block text-base sm:text-lg font-bold text-[#1A1A1A]">
                        <span class="mr-2 text-2xl">📚</span>
                        Pilih Jenis Publikasi
                    </label>
                    <span class="px-3 py-1 text-xs font-semibold text-orange-700 bg-orange-100 rounded-full sm:text-sm">
                        Maksimal 3 jenis
                    </span>
                </div>

                <p class="text-xs sm:text-sm text-[#737373] mb-4">
                    💡 <strong>Tips:</strong> Pilih lebih sedikit untuk konten yang lebih fokus dan berkualitas
                </p>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4">
                    @foreach($publicationTypes as $slug => $type)
                    <label
                        class="type-card cursor-pointer p-4 border-2 border-[#EEF0F7] rounded-xl hover:border-[#FF6B18] transition-all {{ $isEditing && in_array($slug, $subscription->types ?? []) ? 'selected' : '' }}">
                        <input type="checkbox" name="types[]" value="{{ $slug }}" class="hidden" {{ $isEditing &&
                            in_array($slug, $subscription->types ?? []) ? 'checked' : '' }}>
                        <div class="mb-2 text-3xl">{{ $type['emoji'] }}</div>
                        <h4 class="font-bold text-sm sm:text-base text-[#1A1A1A] mb-1">{{ $type['label'] }}</h4>
                        <p class="text-xs text-[#737373] mb-2">{{ $type['description'] }}</p>
                        <div class="flex items-center justify-between">
                            <div class="text-xs text-[#FF6B18] font-semibold">{{ $type['count'] }} tersedia</div>
                            <div class="text-[10px] text-[#A3A6AE]">{{ count($type['available_category_ids']) }}
                                kategori</div>
                        </div>
                    </label>
                    @endforeach
                </div>

                @error('types')
                <div class="flex items-start gap-2 p-3 mt-3 border border-red-200 rounded-lg bg-red-50">
                    <svg class="w-4 h-4 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                    <p class="text-xs text-red-700 sm:text-sm">{{ $message }}</p>
                </div>
                @enderror
            </div>

            {{-- ✅ STEP 2: Categories (Max 10, Dynamic Filtering) --}}
            <div>
                <div class="flex items-center justify-between mb-4">
                    <label class="block text-base sm:text-lg font-bold text-[#1A1A1A]">
                        <span class="mr-2 text-2xl">🏷️</span>
                        Pilih Kategori Minat
                    </label>
                    <span class="px-3 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full sm:text-sm">
                        Maksimal 10 kategori
                    </span>
                </div>

                <div class="p-3 mb-4 border border-blue-200 rounded-lg bg-blue-50">
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="text-xs text-blue-800 sm:text-sm">
                                <strong>📌 Kategori akan otomatis menyesuaikan</strong> dengan jenis publikasi yang Anda
                                pilih
                            </p>
                            <p class="mt-1 text-xs text-blue-600">Pilih jenis publikasi terlebih dahulu untuk melihat
                                kategori yang tersedia</p>
                        </div>
                    </div>
                </div>

                <div id="categories-container" class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach($categories as $category)
                    <label
                        class="category-card cursor-pointer p-3 border-2 border-[#EEF0F7] rounded-xl hover:border-[#FF6B18] transition-all {{ $isEditing && in_array($category['id'], $subscription->categories ?? []) ? 'selected' : '' }}"
                        data-category-id="{{ $category['id'] }}"
                        data-available-types="{{ json_encode($category['available_in_types']) }}">
                        <div class="flex items-start gap-2">
                            <input type="checkbox" name="categories[]" value="{{ $category['id'] }}"
                                class="mt-1 category-checkbox" {{ $isEditing && in_array($category['id'],
                                $subscription->categories ?? []) ? 'checked' : '' }}>
                            <div class="flex-1 min-w-0">
                                <span class="font-semibold text-sm text-[#1A1A1A] block mb-1">{{ $category['name']
                                    }}</span>
                                <span class="count-badge text-xs text-[#737373]">{{ $category['total_count'] }}
                                    publikasi</span>

                                {{-- Type badges --}}
                                <div class="flex flex-wrap gap-1 mt-2">
                                    @foreach($category['available_in_types'] as $typeSlug)
                                    @php
                                    $typeEmoji = $publicationTypes[$typeSlug]['emoji'] ?? '📄';
                                    @endphp
                                    <span class="type-badge text-[10px] px-1.5 py-0.5 bg-gray-100 rounded"
                                        title="{{ $publicationTypes[$typeSlug]['label'] ?? $typeSlug }}">
                                        {{ $typeEmoji }}
                                    </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </label>
                    @endforeach
                </div>

                <div id="no-categories-message"
                    class="hidden p-4 mt-4 text-center border border-yellow-200 rounded-lg bg-yellow-50">
                    <svg class="w-12 h-12 mx-auto mb-2 text-yellow-500" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <p class="mb-1 text-sm font-semibold text-yellow-800">Tidak ada kategori tersedia</p>
                    <p class="text-xs text-yellow-700">Silakan pilih jenis publikasi terlebih dahulu</p>
                </div>

                @error('categories')
                <div class="flex items-start gap-2 p-3 mt-3 border border-red-200 rounded-lg bg-red-50">
                    <svg class="w-4 h-4 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                    <p class="text-xs text-red-700 sm:text-sm">{{ $message }}</p>
                </div>
                @enderror
            </div>

            {{-- ✅ STEP 3: Notification Frequency --}}
            <div>
                <label class="block text-base sm:text-lg font-bold text-[#1A1A1A] mb-4">
                    <span class="mr-2 text-2xl">🔔</span>
                    Pilih Frekuensi Notifikasi
                </label>

                <div class="p-3 mb-4 border border-purple-200 rounded-lg bg-purple-50">
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-purple-600 flex-shrink-0 mt-0.5" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                        </svg>
                        <p class="text-xs text-purple-800 sm:text-sm">
                            <strong>🛡️ Perlindungan Anti-Spam:</strong> Kami membatasi jumlah email untuk menjaga inbox
                            Anda tetap bersih
                        </p>
                    </div>
                </div>

                <div class="space-y-3">
                    @foreach($notificationTypes as $key => $type)
                    <div>
                        <input type="radio" name="notification_type" value="{{ $key }}"
                            id="{{ $isEditing ? 'edit_' : '' }}{{ $key }}" class="notification-radio" {{ ($isEditing &&
                            $subscription->notification_type === $key) || (!$isEditing && $type['recommended']) ?
                        'checked' : '' }}
                        required>
                        <label for="{{ $isEditing ? 'edit_' : '' }}{{ $key }}"
                            class="notification-card block p-4 border-2 border-[#EEF0F7] rounded-xl">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 text-2xl">{{ $type['icon'] }}</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-center gap-2 mb-1">
                                        <h4 class="font-bold text-sm sm:text-base text-[#1A1A1A]">{{ $type['label'] }}
                                        </h4>
                                        @if($type['recommended'])
                                        <span
                                            class="badge px-2 py-0.5 text-[10px] font-bold rounded-full bg-green-100 text-green-700">✨
                                            Rekomendasi</span>
                                        @endif
                                        @if(isset($type['spam_risk']))
                                        <span
                                            class="badge px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-100 text-blue-700">🛡️
                                            Dilindungi</span>
                                        @endif
                                    </div>
                                    <p class="text-xs sm:text-sm text-[#737373] mb-1">{{ $type['description'] }}</p>
                                    <p class="text-xs text-[#A3A6AE] mb-2">{{ $type['detail'] }}</p>
                                    <div class="flex items-center gap-4 text-xs">
                                        <span class="font-semibold text-[#FF6B18]">📨 {{ $type['estimated'] }}</span>
                                        <span class="text-[#A3A6AE]">⏰ {{ $type['frequency'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                    @endforeach
                </div>

                @error('notification_type')
                <div class="flex items-start gap-2 p-3 mt-3 border border-red-200 rounded-lg bg-red-50">
                    <svg class="w-4 h-4 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                    <p class="text-xs text-red-700 sm:text-sm">{{ $message }}</p>
                </div>
                @enderror
            </div>

            {{-- ✅ Submit Button --}}
            <div class="pt-4 space-y-3">
                <button type="submit"
                    class="group w-full px-6 py-4 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white text-sm sm:text-base font-bold rounded-xl hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 flex items-center justify-center gap-2 pulse-glow">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if($isEditing)
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        @endif
                    </svg>
                    <span>{{ $isEditing ? 'Simpan Perubahan' : 'Mulai Berlangganan Gratis' }}</span>
                </button>

                <p class="text-xs sm:text-sm text-[#737373] text-center">
                    📧 Email akan dikirim ke: <strong class="text-[#FF6B18]">{{ auth()->user()->email }}</strong>
                </p>
            </div>
        </form>

        {{-- ✅ Benefits Section (Only for new subscription) --}}
        @if(!$isEditing)
        <div class="mt-8 pt-8 border-t-2 border-[#EEF0F7]">
            <h4 class="font-bold text-[#1A1A1A] mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-[#FF6B18]" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                Keuntungan Berlangganan
            </h4>
            <ul class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm text-[#737373]">
                <li class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-[#FF6B18] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    <span>Notifikasi publikasi sesuai minat</span>
                </li>
                <li class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-[#FF6B18] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    <span>Konten berkualitas & terkurasi</span>
                </li>
                <li class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-[#FF6B18] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    <span>100% gratis, tanpa biaya</span>
                </li>
                <li class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-[#FF6B18] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    <span>Berhenti kapan saja, tanpa ribet</span>
                </li>
                <li class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-[#FF6B18] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    <span>Perlindungan anti-spam otomatis</span>
                </li>
                <li class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-[#FF6B18] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    <span>Update ter-personalisasi</span>
                </li>
            </ul>
        </div>
        @endif

        {{-- ✅ Unsubscribe Section (Only for editing) --}}
        @if($isEditing)
        <div class="mt-8 pt-8 border-t-2 border-[#EEF0F7]">
            <div class="p-5 border-2 border-red-200 bg-red-50 rounded-xl">
                <h4 class="flex items-center gap-2 mb-2 font-bold text-red-900">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    ⚠️ Zona Berbahaya
                </h4>
                <p class="mb-4 text-sm text-red-700">Batal langganan akan menghentikan semua email pembaruan dari kami.
                    Anda akan kehilangan akses ke update publikasi terbaru.</p>
                <form action="{{ route('subscription.destroy') }}" method="POST"
                    onsubmit="return confirm('⚠️ Yakin ingin berhenti berlangganan?\n\n✖️ Anda tidak akan menerima email lagi\n✖️ Preferensi yang sudah dipilih akan hilang\n\n💡 Anda bisa berlangganan lagi kapan saja!');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-bold rounded-lg transition-all hover:shadow-lg">
                        🚫 Batal Langganan
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>