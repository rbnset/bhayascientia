{{-- PDF.js CDN (UMD) --}}
<script src="https://cdn.jsdelivr.net/npm/pdfjs-dist@4.10.38/build/pdf.min.js"></script>

@php
/** @var \Filament\Forms\Components\Component $component */
$statePath = $getStatePath();
$url = $fileUrl instanceof Closure ? $fileUrl($get) : $fileUrl;
@endphp

<div x-data="pdfAnnotatorField({
        state: $wire.{{ $applyStateBindingModifiers(" \$entangle('{$statePath}')") }}, fileUrl: @js($url), })"
    x-init="init()" class="space-y-3">
    <div class="flex flex-wrap gap-2">
        <button type="button" class="fi-btn fi-btn-size-sm" @click="setTool('pan')">Pan</button>
        <button type="button" class="fi-btn fi-btn-size-sm" @click="setTool('highlight')">Highlight</button>
        <button type="button" class="fi-btn fi-btn-size-sm" @click="setTool('note')">Note</button>
        <button type="button" class="fi-btn fi-btn-size-sm" @click="undo()">Undo</button>
        <button type="button" class="fi-btn fi-btn-size-sm" @click="clearAll()">Clear page</button>

        <div class="text-sm text-gray-500 ms-auto">
            Page: <span x-text="page"></span> / <span x-text="pageCount"></span>
        </div>
    </div>

    <div class="flex gap-2">
        <button type="button" class="fi-btn fi-btn-size-sm" @click="prevPage()">Prev</button>
        <button type="button" class="fi-btn fi-btn-size-sm" @click="nextPage()">Next</button>
    </div>

    <template x-if="!fileUrl">
        <div class="text-sm text-gray-500">
            Pilih Publication Version dulu untuk menampilkan PDF.
        </div>
    </template>

    <div class="relative w-full overflow-auto bg-white border rounded" style="height: 70vh;">
        <div class="relative mx-auto" :style="`width:${viewportWidth}px; height:${viewportHeight}px;`">
            <canvas x-ref="canvas" class="absolute inset-0"></canvas>
            <div x-ref="overlay" class="absolute inset-0"></div>
        </div>
    </div>

    <p class="text-xs text-gray-500">
        Highlight versi ini berbasis drag kotak (bukan text selection).
    </p>
</div>