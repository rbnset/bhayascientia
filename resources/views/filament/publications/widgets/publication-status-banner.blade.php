@if ($show)
@php
// mapping warna sederhana (Tailwind-ish)
$styles = match ($variant) {
'info' => [
'wrap' => 'border-sky-200 bg-sky-50 dark:border-sky-900/40 dark:bg-sky-900/20',
'title' => 'text-sky-800 dark:text-sky-200',
'text' => 'text-sky-700 dark:text-sky-300',
'icon' => 'text-sky-600',
'iconName' => 'heroicon-o-information-circle',
],
'warning' => [
'wrap' => 'border-warning-200 bg-warning-50 dark:border-warning-900/40 dark:bg-warning-900/20',
'title' => 'text-warning-800 dark:text-warning-200',
'text' => 'text-warning-700 dark:text-warning-300',
'icon' => 'text-warning-600',
'iconName' => 'heroicon-o-clock',
],
'warning-strong' => [
'wrap' => 'border-warning-200 bg-warning-50 dark:border-warning-900/40 dark:bg-warning-900/20',
'title' => 'text-warning-800 dark:text-warning-200',
'text' => 'text-warning-700 dark:text-warning-300',
'icon' => 'text-warning-600',
'iconName' => 'heroicon-o-exclamation-triangle',
],
'success' => [
'wrap' => 'border-success-200 bg-success-50 dark:border-success-900/40 dark:bg-success-900/20',
'title' => 'text-success-800 dark:text-success-200',
'text' => 'text-success-700 dark:text-success-300',
'icon' => 'text-success-600',
'iconName' => 'heroicon-o-check-circle',
],
'success-strong' => [
'wrap' => 'border-success-200 bg-success-50 dark:border-success-900/40 dark:bg-success-900/20',
'title' => 'text-success-800 dark:text-success-200',
'text' => 'text-success-700 dark:text-success-300',
'icon' => 'text-success-600',
'iconName' => 'heroicon-o-check-badge',
],
'danger' => [
'wrap' => 'border-danger-200 bg-danger-50 dark:border-danger-900/40 dark:bg-danger-900/20',
'title' => 'text-danger-800 dark:text-danger-200',
'text' => 'text-danger-700 dark:text-danger-300',
'icon' => 'text-danger-600',
'iconName' => 'heroicon-o-x-circle',
],
default => [
'wrap' => 'border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-900/20',
'title' => 'text-gray-800 dark:text-gray-200',
'text' => 'text-gray-700 dark:text-gray-300',
'icon' => 'text-gray-600',
'iconName' => 'heroicon-o-document-text',
],
};
@endphp

<div class="fi-section-content-ctn">
    <div class="rounded-xl border p-4 {{ $styles['wrap'] }}">
        <div class="flex items-start gap-3">
            <div class="mt-0.5">
                <x-filament::icon :icon="$styles['iconName']" class="h-6 w-6 {{ $styles['icon'] }}" />
            </div>

            <div class="flex-1 min-w-0">
                <div class="text-sm font-semibold {{ $styles['title'] }}">
                    {{ $title }}
                </div>

                <div class="mt-1 text-sm {{ $styles['text'] }}">
                    {{ $message }}
                </div>

                {{-- Extra info dinamis --}}
                <div class="mt-2 text-sm {{ $styles['text'] }}">
                    <div>
                        Status: <span class="font-medium">{{ $status }}</span>
                    </div>

                    @if ($status === 'published' && $publishedAt)
                    <div>
                        Dipublikasikan pada: <span class="font-medium">{{ $publishedAt->format('d M Y H:i') }}</span>
                    </div>
                    @endif

                    @if (in_array($status, ['revision_required', 'accepted', 'rejected', 'in_review', 'submitted']) &&
                    $latestReviewerName)
                    <div>
                        Update terakhir oleh reviewer: <span class="font-medium">{{ $latestReviewerName }}</span>
                        @if ($latestReviewedAt)
                        <span class="opacity-80">({{ $latestReviewedAt->format('d M Y H:i') }})</span>
                        @endif
                        @if ($latestReviewDecision)
                        — keputusan: <span class="font-medium">{{ $latestReviewDecision }}</span>
                        @endif
                    </div>
                    @elseif (in_array($status, ['revision_required', 'accepted', 'rejected', 'in_review', 'submitted']))
                    <div>
                        Belum ada data reviewer terakhir yang bisa ditampilkan.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endif