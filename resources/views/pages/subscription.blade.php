{{-- resources/views/pages/subscription.blade.php (ENHANCED UX VERSION) --}}
@extends('layouts.app')

@section('title', 'Langganan Newsletter - BHAYASCIENTIA')
@section('main_class', 'pb-16')

@push('styles')
<style>
    /* Publication Type Card */
    .type-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .type-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(255, 107, 24, 0.15);
    }

    .type-card.selected {
        border-color: #FF6B18;
        background: linear-gradient(135deg, #FFF7F2 0%, #FFE8DC 100%);
    }

    .type-card.selected::before {
        content: '✓';
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        width: 1.5rem;
        height: 1.5rem;
        background: linear-gradient(135deg, #FF6B18 0%, #E64627 100%);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.875rem;
    }

    .type-card.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        pointer-events: none;
    }

    /* Category Checkbox */
    .category-checkbox {
        appearance: none;
        width: 1.25rem;
        height: 1.25rem;
        border: 2px solid #EEF0F7;
        border-radius: 0.375rem;
        cursor: pointer;
        position: relative;
        transition: all 0.2s ease;
    }

    .category-checkbox:checked {
        background: linear-gradient(135deg, #FF6B18 0%, #E64627 100%);
        border-color: #FF6B18;
    }

    .category-checkbox:checked::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0.5rem;
        height: 0.75rem;
        border: solid white;
        border-width: 0 2px 2px 0;
        transform: translate(-50%, -60%) rotate(45deg);
    }

    .category-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .category-card:hover {
        transform: translateY(-2px);
        border-color: #FF6B18;
    }

    .category-card.selected {
        border-color: #FF6B18;
        background: #FFF7F2;
    }

    .category-card.hidden {
        display: none;
    }

    .category-card.disabled {
        opacity: 0.4;
        pointer-events: none;
    }

    /* Notification Type Radio */
    .notification-radio {
        appearance: none;
    }

    .notification-card {
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .notification-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(255, 107, 24, 0.1);
    }

    .notification-radio:checked+.notification-card {
        background: linear-gradient(135deg, #FFF7F2 0%, #FFE8DC 100%);
        border-color: #FF6B18;
        box-shadow: 0 8px 16px rgba(255, 107, 24, 0.2);
    }

    .notification-radio:checked+.notification-card .badge {
        background: linear-gradient(135deg, #FF6B18 0%, #E64627 100%);
        color: white;
    }

    /* Loading State */
    .loading-overlay {
        position: relative;
    }

    .loading-overlay::after {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(2px);
        z-index: 10;
    }

    /* Selection Counter */
    .selection-counter {
        position: sticky;
        top: 1rem;
        z-index: 40;
        animation: slideInDown 0.3s ease-out;
    }

    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Animation */
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-slide-up {
        animation: slideInUp 0.6s ease-out forwards;
    }

    @keyframes pulse-glow {

        0%,
        100% {
            box-shadow: 0 0 0 0 rgba(255, 107, 24, 0.4);
        }

        50% {
            box-shadow: 0 0 0 8px rgba(255, 107, 24, 0);
        }
    }

    .pulse-glow {
        animation: pulse-glow 2s infinite;
    }
</style>
@endpush

@section('content')

{{-- Hero Section --}}
<section
    class="bg-gradient-to-br from-[#FF6B18] via-[#E64627] to-[#D63A25] relative overflow-hidden rounded-2xl sm:rounded-[28px]">
    <div class="absolute inset-0 opacity-10">
        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="subscription-grid" width="40" height="40" patternUnits="userSpaceOnUse">
                    <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="1" />
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#subscription-grid)" />
        </svg>
    </div>

    <div class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] py-12 sm:py-16 md:py-20 lg:py-24 relative z-10">
        <nav class="flex items-center gap-2 mb-6 text-xs sm:text-sm text-white/80 sm:mb-8" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="transition-colors hover:text-white">Beranda</a>
            <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="font-bold text-white">Langganan</span>
        </nav>

        <div class="max-w-3xl mx-auto text-center text-white">
            <div
                class="inline-flex items-center gap-2 px-3 py-1.5 sm:px-4 sm:py-2 mb-4 sm:mb-6 text-[10px] sm:text-xs font-bold rounded-full bg-white/20 backdrop-blur-sm border border-white/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                100% Gratis · {{ $stats['subscribers_count'] }}+ Pelanggan
            </div>

            <h1 class="mb-4 text-3xl font-black leading-tight sm:text-4xl md:text-5xl lg:text-6xl sm:mb-6">
                📬 Langganan Newsletter
            </h1>
            <p class="mb-4 text-base leading-relaxed sm:text-xl md:text-2xl text-white/90">
                Dapatkan pembaruan publikasi terbaru langsung ke email Anda
            </p>

            {{-- Quick Stats --}}
            <div class="flex items-center justify-center gap-4 mt-6 text-sm sm:gap-6 sm:text-base">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z" />
                    </svg>
                    <span class="font-semibold">{{ number_format($stats['total_publications']) }} Publikasi</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                            clip-rule="evenodd" />
                    </svg>
                    <span class="font-semibold">{{ $stats['this_week'] }} Minggu Ini</span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Alert Messages --}}
@if(session('success'))
<div class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-6 sm:mt-8">
    <div
        class="flex items-start gap-3 p-4 border-2 border-green-500 bg-green-50 rounded-xl sm:rounded-2xl sm:p-5 sm:gap-4 animate-slide-up">
        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="flex-1 min-w-0">
            <p class="text-xs text-green-700 sm:text-sm">{{ session('success') }}</p>
        </div>
        <button onclick="this.parentElement.remove()"
            class="flex-shrink-0 text-green-600 transition-colors hover:text-green-800">
            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</div>
@endif

@if(session('error'))
<div class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-6 sm:mt-8">
    <div
        class="flex items-start gap-3 p-4 border-2 border-red-500 bg-red-50 rounded-xl sm:rounded-2xl sm:p-5 sm:gap-4 animate-slide-up">
        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="flex-1 min-w-0">
            <p class="text-xs text-red-700 sm:text-sm">{{ session('error') }}</p>
        </div>
        <button onclick="this.parentElement.remove()"
            class="flex-shrink-0 text-red-600 transition-colors hover:text-red-800">
            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</div>
@endif

{{-- Main Content --}}
<section class="px-4 sm:px-6 lg:px-8 mx-auto max-w-[1130px] mt-8 sm:mt-10 md:mt-12 mb-12 sm:mb-16">

    @if($subscription && $subscription->is_active)
    {{-- ACTIVE SUBSCRIPTION - Same as before but with enhanced info --}}
    <div class="mb-8 sm:mb-12 animate-slide-up">
        <div class="p-6 border-2 border-green-200 bg-gradient-to-br from-green-50 to-emerald-50 rounded-2xl sm:p-8">
            <div class="flex items-start gap-4">
                <div class="flex items-center justify-center flex-shrink-0 bg-green-500 w-14 h-14 rounded-2xl">
                    <svg class="text-white w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="mb-2 text-xl font-black text-green-900 sm:text-2xl">✅ Langganan Aktif</h3>
                    <p class="mb-4 text-sm text-green-700 sm:text-base">
                        Berlangganan sejak {{ $subscription->subscribed_at->format('d M Y') }}
                    </p>
                    <div class="grid grid-cols-1 gap-3 text-xs sm:grid-cols-3 sm:text-sm">
                        <div class="flex items-center gap-2 text-green-800">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span><strong>Frekuensi:</strong> {{
                                $notificationTypes[$subscription->notification_type]['frequency'] ?? 'N/A' }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-green-800">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                <path fill-rule="evenodd"
                                    d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span><strong>Kategori:</strong> {{ count($subscription->categories ?? []) }} pilihan</span>
                        </div>
                        <div class="flex items-center gap-2 text-green-800">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z" />
                            </svg>
                            <span><strong>Jenis:</strong> {{ count($subscription->types ?? []) }} tipe</span>
                        </div>
                    </div>

                    {{-- Anti-Spam Info --}}
                    @if($subscription->notification_type === 'instant')
                    <div class="p-3 mt-4 border border-yellow-200 rounded-lg bg-yellow-50">
                        <div class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-yellow-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                            <p class="text-xs text-yellow-800">
                                <strong>Perlindungan Spam:</strong> Maksimal {{ $subscription->max_emails_per_day }}
                                email/hari untuk menjaga kualitas inbox Anda
                            </p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Form - Will use same enhanced form as new subscription --}}
    @php $isEditing = true; @endphp
    @include('pages.subscription.form', ['isEditing' => true])

    @elseif($subscription && !$subscription->is_active)
    {{-- Reactivate - Same as before --}}
    <div class="max-w-2xl mx-auto text-center animate-slide-up">
        <div class="p-8 border-2 border-yellow-200 bg-gradient-to-br from-yellow-50 to-orange-50 rounded-2xl sm:p-12">
            <div class="flex items-center justify-center w-20 h-20 mx-auto mb-6 bg-yellow-500 rounded-full">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="mb-3 text-2xl font-black text-yellow-900 sm:text-3xl">Langganan Tidak Aktif</h3>
            <p class="mb-6 text-base text-yellow-700 sm:text-lg">
                Anda membatalkan langganan pada {{ $subscription->unsubscribed_at->format('d M Y') }}. Ingin
                berlangganan lagi?
            </p>
            <form action="{{ route('subscription.reactivate') }}" method="POST">
                @csrf
                <button type="submit"
                    class="group px-8 py-4 bg-gradient-to-r from-[#FF6B18] to-[#E64627] text-white text-base font-bold rounded-xl hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Aktifkan Kembali Langganan</span>
                </button>
            </form>
        </div>
    </div>

    @else
    {{-- New Subscription Form --}}
    @php $isEditing = false; @endphp
    @include('pages.subscription.form', ['isEditing' => false])
    @endif

</section>

@endsection

@push('scripts')
<script>
    // ✅ Global data from backend
const publicationTypesData = @json($publicationTypes);
const categoriesData = @json($categories);
const csrfToken = '{{ csrf_token() }}';

// ✅ State management
let selectedTypes = [];
let selectedCategories = [];
let availableCategories = [];

document.addEventListener('DOMContentLoaded', function() {
    initSubscriptionForm();
});

function initSubscriptionForm() {
    // Initialize type selection
    const typeCheckboxes = document.querySelectorAll('input[name="types[]"]');
    typeCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', handleTypeChange);

        // Set initial state for edit mode
        if (checkbox.checked) {
            selectedTypes.push(checkbox.value);
        }
    });

    // Initialize category selection
    const categoryCheckboxes = document.querySelectorAll('input[name="categories[]"]');
    categoryCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', handleCategoryChange);

        // Set initial state for edit mode
        if (checkbox.checked) {
            selectedCategories.push(parseInt(checkbox.value));
        }
    });

    // Initial filter if types are already selected
    if (selectedTypes.length > 0) {
        filterCategories();
    }

    updateSelectionCounter();
}

function handleTypeChange(e) {
    const value = e.target.value;
    const card = e.target.closest('.type-card');

    if (e.target.checked) {
        // Check limit
        if (selectedTypes.length >= 3) {
            e.target.checked = false;
            showNotification('Maksimal 3 jenis publikasi untuk menghindari spam', 'warning');
            return;
        }

        selectedTypes.push(value);
        card.classList.add('selected');
    } else {
        selectedTypes = selectedTypes.filter(t => t !== value);
        card.classList.remove('selected');
    }

    filterCategories();
    updateSelectionCounter();
}

function handleCategoryChange(e) {
    const value = parseInt(e.target.value);
    const card = e.target.closest('.category-card');

    if (e.target.checked) {
        // Check limit
        if (selectedCategories.length >= 10) {
            e.target.checked = false;
            showNotification('Maksimal 10 kategori untuk kualitas konten yang lebih fokus', 'warning');
            return;
        }

        selectedCategories.push(value);
        card.classList.add('selected');
    } else {
        selectedCategories = selectedCategories.filter(c => c !== value);
        card.classList.remove('selected');
    }

    updateSelectionCounter();
}

function filterCategories() {
    const categoryCards = document.querySelectorAll('.category-card');

    if (selectedTypes.length === 0) {
        // Show all categories
        categoryCards.forEach(card => {
            card.classList.remove('hidden', 'disabled');
        });
        return;
    }

    // Determine which categories are available for selected types
    availableCategories = categoriesData.filter(cat => {
        return selectedTypes.some(type => cat.available_in_types.includes(type));
    }).map(cat => cat.id);

    // Update UI
    categoryCards.forEach(card => {
        const checkbox = card.querySelector('input[type="checkbox"]');
        const categoryId = parseInt(checkbox.value);
        const countBadge = card.querySelector('.count-badge');

        if (availableCategories.includes(categoryId)) {
            card.classList.remove('hidden', 'disabled');

            // Update count based on selected types
            const category = categoriesData.find(c => c.id === categoryId);
            let totalCount = 0;
            selectedTypes.forEach(type => {
                totalCount += category.count_per_type[type] || 0;
            });

            if (countBadge) {
                countBadge.textContent = `${totalCount} publikasi`;
            }
        } else {
            card.classList.add('disabled');

            // Uncheck if selected but not available
            if (checkbox.checked) {
                checkbox.checked = false;
                selectedCategories = selectedCategories.filter(c => c !== categoryId);
                card.classList.remove('selected');
            }
        }
    });

    // Show empty state if no categories available
    const visibleCategories = Array.from(categoryCards).filter(card =>
        !card.classList.contains('hidden') && !card.classList.contains('disabled')
    );

    if (visibleCategories.length === 0) {
        showNotification('Tidak ada kategori tersedia untuk jenis publikasi yang dipilih', 'info');
    }
}

function updateSelectionCounter() {
    const counterEl = document.getElementById('selection-counter');
    if (!counterEl) return;

    const typeCount = selectedTypes.length;
    const categoryCount = selectedCategories.length;

    if (typeCount === 0 && categoryCount === 0) {
        counterEl.classList.add('hidden');
        return;
    }

    counterEl.classList.remove('hidden');
    counterEl.innerHTML = `
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-[#737373]">Tipe:</span>
                <span class="px-2 py-1 text-xs font-bold rounded-full ${typeCount >= 3 ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700'}">${typeCount}/3</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-[#737373]">Kategori:</span>
                <span class="px-2 py-1 text-xs font-bold rounded-full ${categoryCount >= 10 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'}">${categoryCount}/10</span>
            </div>
        </div>
    `;
}

function showNotification(message, type = 'info') {
    const colors = {
        info: 'bg-blue-50 border-blue-500 text-blue-700',
        warning: 'bg-yellow-50 border-yellow-500 text-yellow-700',
        error: 'bg-red-50 border-red-500 text-red-700',
        success: 'bg-green-50 border-green-500 text-green-700'
    };

    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 ${colors[type]} border-2 rounded-xl p-4 shadow-lg animate-slide-up max-w-md`;
    notification.innerHTML = `
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <p class="flex-1 text-sm">${message}</p>
        </div>
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Auto-dismiss alerts
setTimeout(() => {
    document.querySelectorAll('.animate-slide-up').forEach(alert => {
        if (alert.querySelector('button[onclick*="remove"]')) {
            alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-20px)';
            setTimeout(() => alert.remove(), 500);
        }
    });
}, 5000);
</script>
@endpush