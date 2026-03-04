@extends('layouts.app')

@section('title', $publication->title . ' - Read')
@section('main_class', 'mt-0 pb-0 bg-[#1A1A1A]')

@push('styles')
<style>
    /* ═══════════════════════════════════════
   BASE RESET
═══════════════════════════════════════ */
    *,
    *::before,
    *::after {
        box-sizing: border-box;
    }

    #pdf-viewer-container {
        height: calc(100vh - 56px);
        background: #2D2D2D;
        position: relative;
        overflow: hidden;
        transition: background 0.3s ease;
        user-select: none;
    }

    /* ═══════════════════════════════════════
   READING MODES
═══════════════════════════════════════ */
    body.read-mode-sepia #pdf-viewer-container {
        background: #f4ecd8;
    }

    body.read-mode-night #pdf-viewer-container {
        background: #111;
    }

    body.read-mode-sepia #pdf-canvas {
        filter: sepia(0.6) brightness(0.92);
    }

    body.read-mode-night #pdf-canvas {
        filter: invert(1) hue-rotate(180deg) brightness(0.85);
    }

    body.read-mode-sepia .pdf-controls {
        background: linear-gradient(135deg, #3b2f1e, #5c4a32) !important;
    }

    body.read-mode-night .pdf-controls {
        background: linear-gradient(135deg, #0a0a0a, #1a1a1a) !important;
    }

    /* ═══════════════════════════════════════
   PROGRESS BAR
═══════════════════════════════════════ */
    .progress-track {
        height: 3px;
        background: #3D3D3D;
        flex-shrink: 0;
    }

    .progress-fill {
        height: 100%;
        background: #FF6B18;
        transition: width 0.4s ease;
        border-radius: 0 2px 2px 0;
    }

    /* ═══════════════════════════════════════
   LOADING
═══════════════════════════════════════ */
    #pdf-loading {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 1rem;
        background: inherit;
        z-index: 10;
    }

    #pdf-loading.hidden {
        display: none !important;
    }

    /* ═══════════════════════════════════════
   CANVAS WRAPPER
═══════════════════════════════════════ */
    #pdf-canvas-wrapper {
        position: absolute;
        inset: 0;
        overflow: auto;
        -webkit-overflow-scrolling: touch;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        padding: 0.5rem;
    }

    #pdf-canvas-wrapper.hidden {
        display: none !important;
    }

    /* ═══════════════════════════════════════
   CANVAS STAGE (canvas + overlays together)
═══════════════════════════════════════ */
    #pdf-stage {
        position: relative;
        display: inline-block;
        flex-shrink: 0;
        box-shadow: 0 4px 32px rgba(0, 0, 0, 0.6);
    }

    #pdf-canvas {
        display: block;
        transition: filter 0.3s ease;
    }

    /* ═══════════════════════════════════════
   TEXT LAYER (for selection & search highlight)
═══════════════════════════════════════ */
    #text-layer {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        line-height: 1;
        pointer-events: auto;
        user-select: text;
        -webkit-user-select: text;
    }

    #text-layer span {
        color: transparent;
        position: absolute;
        white-space: pre;
        cursor: text;
        transform-origin: 0% 0%;
    }

    #text-layer span::selection {
        background: rgba(66, 133, 244, 0.35);
    }

    /* ═══════════════════════════════════════
   ANNOTATION LAYER (highlights, comments)
═══════════════════════════════════════ */
    #annotation-layer {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        overflow: hidden;
    }

    .annot-highlight {
        position: absolute;
        border-radius: 2px;
        pointer-events: auto;
        cursor: pointer;
        opacity: 0.4;
        transition: opacity 0.15s;
        z-index: 5;
    }

    .annot-highlight:hover {
        opacity: 0.7;
    }

    .annot-highlight.color-yellow {
        background: #FFD700;
    }

    .annot-highlight.color-green {
        background: #4ADE80;
    }

    .annot-highlight.color-pink {
        background: #F472B6;
    }

    .annot-highlight.color-blue {
        background: #60A5FA;
    }

    .annot-highlight.color-orange {
        background: #FF6B18;
    }

    /* Search highlight */
    .search-highlight {
        position: absolute;
        background: rgba(255, 200, 0, 0.45);
        border-radius: 2px;
        pointer-events: none;
        z-index: 6;
    }

    .search-highlight.active-match {
        background: rgba(255, 107, 24, 0.65);
        border: 1.5px solid #FF6B18;
        border-radius: 3px;
    }

    /* ═══════════════════════════════════════
   ANNOTATION TOOLTIP (comment popup)
═══════════════════════════════════════ */
    #annot-tooltip {
        position: fixed;
        background: #1A1A1A;
        border: 1.5px solid #FF6B18;
        border-radius: 12px;
        padding: 0.75rem;
        max-width: 260px;
        z-index: 20000;
        display: none;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
        font-size: 13px;
        color: white;
    }

    #annot-tooltip.show {
        display: block;
    }

    #annot-tooltip .at-text {
        color: #ddd;
        margin-bottom: 0.5rem;
        font-size: 12px;
        word-break: break-word;
    }

    #annot-tooltip .at-actions {
        display: flex;
        gap: 0.4rem;
    }

    #annot-tooltip .at-btn {
        flex: 1;
        padding: 0.35rem;
        border-radius: 7px;
        border: none;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s;
    }

    #annot-tooltip .at-btn.del {
        background: rgba(239, 68, 68, 0.2);
        color: #f87171;
    }

    #annot-tooltip .at-btn.del:hover {
        background: rgba(239, 68, 68, 0.4);
    }

    #annot-tooltip .at-btn.close {
        background: #2D2D2D;
        color: #aaa;
    }

    #annot-tooltip .at-btn.close:hover {
        background: #3D3D3D;
    }

    /* ═══════════════════════════════════════
   ANNOTATION TOOLBAR (floating, on text select)
═══════════════════════════════════════ */
    #annot-toolbar {
        position: fixed;
        background: #1A1A1A;
        border: 1.5px solid #3D3D3D;
        border-radius: 12px;
        padding: 0.4rem 0.5rem;
        z-index: 19000;
        display: none;
        align-items: center;
        gap: 0.35rem;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5);
    }

    #annot-toolbar.show {
        display: flex;
    }

    #annot-toolbar .at-sep {
        width: 1px;
        height: 20px;
        background: #3D3D3D;
    }

    .annot-tool-btn {
        width: 30px;
        height: 30px;
        border-radius: 7px;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        transition: all 0.15s;
        background: transparent;
        position: relative;
    }

    .annot-tool-btn:hover {
        background: rgba(255, 107, 24, 0.2);
        transform: scale(1.1);
    }

    .annot-tool-btn.active-color {
        box-shadow: 0 0 0 2px white;
    }

    .color-swatch {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, 0.3);
    }

    .cs-yellow {
        background: #FFD700;
    }

    .cs-green {
        background: #4ADE80;
    }

    .cs-pink {
        background: #F472B6;
    }

    .cs-blue {
        background: #60A5FA;
    }

    .cs-orange {
        background: #FF6B18;
    }

    /* ═══════════════════════════════════════
   COMMENT INPUT POPUP
═══════════════════════════════════════ */
    #comment-popup {
        position: fixed;
        background: #1A1A1A;
        border: 2px solid #FF6B18;
        border-radius: 14px;
        padding: 0.875rem;
        width: 280px;
        z-index: 20001;
        display: none;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.6);
    }

    #comment-popup.show {
        display: block;
    }

    #comment-popup .cp-title {
        font-size: 12px;
        font-weight: 700;
        color: #FF6B18;
        margin-bottom: 0.5rem;
    }

    #comment-popup textarea {
        width: 100%;
        background: #2D2D2D;
        border: 1.5px solid #3D3D3D;
        color: white;
        border-radius: 8px;
        padding: 0.5rem;
        font-size: 13px;
        resize: none;
        outline: none;
        height: 72px;
    }

    #comment-popup textarea:focus {
        border-color: #FF6B18;
    }

    #comment-popup .cp-actions {
        display: flex;
        gap: 0.4rem;
        margin-top: 0.5rem;
    }

    #comment-popup .cp-save {
        flex: 1;
        padding: 0.45rem;
        background: #FF6B18;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
    }

    #comment-popup .cp-cancel {
        padding: 0.45rem 0.75rem;
        background: #2D2D2D;
        color: #aaa;
        border: none;
        border-radius: 8px;
        font-size: 12px;
        cursor: pointer;
    }

    /* ═══════════════════════════════════════
   IFRAME FALLBACK
═══════════════════════════════════════ */
    #pdf-iframe {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        border: none;
        display: none;
        z-index: 4;
    }

    /* ═══════════════════════════════════════
   FULLSCREEN
═══════════════════════════════════════ */
    #pdf-viewer-container.fullscreen-mode {
        position: fixed !important;
        inset: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 9999 !important;
    }

    #pdf-viewer-container.fullscreen-mode #pdf-canvas-wrapper {
        top: 52px;
    }

    #pdf-viewer-container.fullscreen-mode #pdf-loading {
        top: 52px;
    }

    /* ═══════════════════════════════════════
   FULLSCREEN TOOLBAR
═══════════════════════════════════════ */
    #pdf-fullscreen-toolbar {
        display: none;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        z-index: 10001;
        background: linear-gradient(135deg, #1A1A1A, #2D2D2D);
        border-bottom: 2px solid #FF6B18;
        padding: 0.4rem 0.75rem;
        align-items: center;
        gap: 0.5rem;
        transition: opacity 0.3s, transform 0.3s;
        min-height: 52px;
    }

    #pdf-viewer-container.fullscreen-mode #pdf-fullscreen-toolbar {
        display: flex !important;
    }

    #pdf-fullscreen-toolbar.toolbar-hidden {
        opacity: 0;
        transform: translateY(-100%);
        pointer-events: none;
    }

    /* ═══════════════════════════════════════
   MOBILE TAP OVERLAY
═══════════════════════════════════════ */
    #mobile-tap-overlay {
        position: absolute;
        inset: 0;
        z-index: 10000;
        display: none;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.65rem;
        background: rgba(0, 0, 0, 0.78);
        padding: 1.25rem;
        overflow-y: auto;
    }

    #mobile-tap-overlay.show {
        display: flex;
    }

    .tap-nav-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        width: 100%;
        max-width: 300px;
    }

    .tap-nav-btn {
        width: 52px;
        height: 52px;
        flex-shrink: 0;
        background: #2D2D2D;
        border: 2px solid #FF6B18;
        border-radius: 14px;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.15s;
        border: none;
    }

    .tap-nav-btn:active {
        background: #FF6B18;
    }

    .tap-nav-center {
        flex: 1;
        text-align: center;
        background: #2D2D2D;
        border-radius: 10px;
        padding: 0.5rem;
    }

    .tap-nav-center strong {
        font-size: 18px;
        font-weight: 700;
        color: white;
        display: block;
        line-height: 1.2;
    }

    .tap-nav-center small {
        font-size: 11px;
        color: #aaa;
    }

    .tap-mode-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.45rem;
        width: 100%;
        max-width: 300px;
    }

    .tap-mode-card {
        background: #2D2D2D;
        border: 2px solid transparent;
        border-radius: 10px;
        padding: 0.5rem 0.4rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.25rem;
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
        color: #ccc;
    }

    .tap-mode-card.active {
        border-color: #FF6B18;
        background: rgba(255, 107, 24, 0.12);
        color: #FF6B18;
    }

    .tap-mode-card .tmc-ic {
        font-size: 1.2rem;
    }

    .tap-zoom-row {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        width: 100%;
        max-width: 300px;
    }

    .tap-zoom-btn {
        width: 42px;
        height: 42px;
        flex-shrink: 0;
        background: #2D2D2D;
        border-radius: 10px;
        border: 1px solid #3D3D3D;
        color: white;
        font-size: 20px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .tap-zoom-btn:active {
        background: #FF6B18;
    }

    .tap-zoom-track {
        flex: 1;
        height: 6px;
        background: #3D3D3D;
        border-radius: 99px;
        overflow: hidden;
    }

    .tap-zoom-fill {
        height: 100%;
        background: #FF6B18;
        transition: width 0.3s;
        border-radius: 99px;
    }

    .tap-zoom-val {
        min-width: 40px;
        text-align: center;
        font-size: 12px;
        font-weight: 700;
        color: white;
    }

    .tap-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.45rem;
        width: 100%;
        max-width: 300px;
    }

    .tap-action-btn {
        background: #2D2D2D;
        border: 1px solid #3D3D3D;
        border-radius: 12px;
        padding: 0.6rem 0.4rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.3rem;
        color: white;
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
    }

    .tap-action-btn:active {
        background: #3D3D3D;
    }

    .tap-action-btn.danger {
        border-color: rgba(239, 68, 68, 0.5);
    }

    .tap-action-btn.danger span {
        color: #f87171;
    }

    .tap-action-btn.bookmarked {
        background: rgba(255, 107, 24, 0.15);
        border-color: #FF6B18;
    }

    .tap-action-btn.bookmarked span {
        color: #FF6B18;
    }

    .tap-action-btn svg {
        width: 20px;
        height: 20px;
    }

    .tap-hint-tips {
        display: flex;
        gap: 0.5rem;
        width: 100%;
        max-width: 300px;
    }

    .tip-badge {
        flex: 1;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 107, 24, 0.2);
        border-radius: 8px;
        padding: 0.4rem;
        text-align: center;
        font-size: 10px;
        color: #aaa;
    }

    .tip-badge .tip-ic {
        font-size: 1.1rem;
        display: block;
    }

    .tap-close-btn {
        width: 100%;
        max-width: 300px;
        padding: 0.6rem;
        background: #3D3D3D;
        border: none;
        color: #aaa;
        font-size: 13px;
        font-weight: 600;
        border-radius: 10px;
        cursor: pointer;
    }

    /* ═══════════════════════════════════════
   SEARCH OVERLAY (improved)
═══════════════════════════════════════ */
    #search-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.65);
        z-index: 20002;
        display: none;
        align-items: flex-start;
        justify-content: center;
        padding-top: 4.5rem;
    }

    #search-overlay.show {
        display: flex;
    }

    #search-box {
        background: #1A1A1A;
        border: 2px solid #FF6B18;
        border-radius: 16px;
        padding: 1rem;
        width: 100%;
        max-width: 500px;
        margin: 0 1rem;
        box-shadow: 0 16px 48px rgba(0, 0, 0, 0.6);
    }

    .search-input-row {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .search-input-row input {
        flex: 1;
        background: #2D2D2D;
        border: 2px solid #3D3D3D;
        color: white;
        border-radius: 10px;
        padding: 0.6rem 0.75rem;
        font-size: 14px;
        outline: none;
    }

    .search-input-row input:focus {
        border-color: #FF6B18;
    }

    .search-input-row input::placeholder {
        color: #666;
    }

    .snav-btn {
        width: 36px;
        height: 36px;
        background: #2D2D2D;
        border: 1px solid #3D3D3D;
        border-radius: 8px;
        color: white;
        cursor: pointer;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        transition: background 0.15s;
    }

    .snav-btn:hover {
        background: #FF6B18;
        border-color: #FF6B18;
    }

    #search-status {
        font-size: 12px;
        color: #aaa;
        margin-top: 0.5rem;
        min-height: 18px;
    }

    #search-match-info {
        font-size: 12px;
        color: #FF6B18;
        margin-top: 2px;
        font-weight: 600;
        min-height: 16px;
    }

    #search-results-list {
        margin-top: 0.6rem;
        max-height: 200px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
    }

    .sri {
        background: #2D2D2D;
        border-radius: 8px;
        padding: 0.45rem 0.65rem;
        cursor: pointer;
        font-size: 12px;
        color: #ccc;
        transition: background 0.15s;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        border: 1px solid transparent;
    }

    .sri:hover {
        background: rgba(255, 107, 24, 0.15);
        color: white;
    }

    .sri.active-sri {
        border-color: #FF6B18;
        background: rgba(255, 107, 24, 0.12);
    }

    .sri .pg {
        font-size: 10px;
        font-weight: 700;
        color: #FF6B18;
        background: rgba(255, 107, 24, 0.15);
        padding: 1px 6px;
        border-radius: 4px;
        flex-shrink: 0;
    }

    .sri .ex {
        flex: 1;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    mark {
        background: rgba(255, 200, 0, 0.4);
        color: white;
        border-radius: 2px;
        padding: 0 2px;
    }

    mark.active-mark {
        background: rgba(255, 107, 24, 0.6);
    }

    /* ═══════════════════════════════════════
   BOTTOM SHEET
═══════════════════════════════════════ */
    #sheet-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1001;
        display: none;
        opacity: 0;
        transition: opacity 0.25s;
    }

    #sheet-backdrop.show {
        display: block;
        opacity: 1;
    }

    #bottom-sheet {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: #1A1A1A;
        border-top: 2px solid #FF6B18;
        border-radius: 20px 20px 0 0;
        z-index: 1002;
        padding: 0 1rem 2.5rem;
        transform: translateY(100%);
        transition: transform 0.32s cubic-bezier(0.34, 1.1, 0.64, 1);
        max-height: 85vh;
        overflow-y: auto;
        overscroll-behavior: contain;
    }

    #bottom-sheet.show {
        transform: translateY(0);
    }

    .sheet-handle {
        width: 40px;
        height: 4px;
        background: #3D3D3D;
        border-radius: 99px;
        margin: 0.75rem auto 1rem;
    }

    .sheet-lbl {
        font-size: 11px;
        font-weight: 700;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 0.5rem;
        display: block;
    }

    .sheet-sec {
        margin-bottom: 1.25rem;
    }

    .sheet-page-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .sheet-page-btn {
        width: 48px;
        height: 48px;
        background: #2D2D2D;
        border-radius: 12px;
        border: none;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        flex-shrink: 0;
        transition: background 0.15s;
    }

    .sheet-page-btn:active {
        background: #FF6B18;
    }

    .sheet-page-btn:disabled {
        opacity: 0.35;
    }

    .sheet-page-display {
        flex: 1;
        text-align: center;
        background: #2D2D2D;
        border-radius: 10px;
        padding: 0.55rem;
    }

    .sheet-page-display strong {
        font-size: 20px;
        font-weight: 700;
        color: white;
        display: block;
        line-height: 1.2;
    }

    .sheet-page-display small {
        font-size: 11px;
        color: #aaa;
    }

    .sheet-jump-row {
        display: flex;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }

    .sheet-jump-input {
        flex: 1;
        background: #2D2D2D;
        border: 2px solid #3D3D3D;
        color: white;
        border-radius: 10px;
        padding: 0.5rem 0.75rem;
        font-size: 14px;
        font-weight: 600;
        outline: none;
        text-align: center;
    }

    .sheet-jump-input:focus {
        border-color: #FF6B18;
    }

    .sheet-jump-go {
        padding: 0.5rem 1rem;
        background: #FF6B18;
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
    }

    .sheet-zoom-row {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .sheet-zoom-btn {
        width: 44px;
        height: 44px;
        background: #2D2D2D;
        border-radius: 10px;
        border: 1px solid #3D3D3D;
        color: white;
        font-size: 22px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .sheet-zoom-btn:active {
        background: #FF6B18;
    }

    .sheet-zoom-track {
        flex: 1;
        height: 6px;
        background: #3D3D3D;
        border-radius: 99px;
        overflow: hidden;
    }

    .sheet-zoom-fill {
        height: 100%;
        background: #FF6B18;
        transition: width 0.3s;
        border-radius: 99px;
    }

    .sheet-zoom-val {
        min-width: 42px;
        text-align: center;
        font-size: 13px;
        font-weight: 700;
        color: white;
    }

    .sheet-mode-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.5rem;
    }

    .sheet-mode-card {
        background: #2D2D2D;
        border: 2px solid transparent;
        border-radius: 12px;
        padding: 0.65rem 0.4rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.3rem;
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
        color: #ccc;
        text-align: center;
    }

    .sheet-mode-card .smc-ic {
        font-size: 1.5rem;
    }

    .sheet-mode-card.active {
        border-color: #FF6B18;
        background: rgba(255, 107, 24, 0.12);
        color: #FF6B18;
    }

    .sheet-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
    }

    .sheet-act-btn {
        background: #2D2D2D;
        border: 1px solid #3D3D3D;
        border-radius: 12px;
        padding: 0.65rem 0.5rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.3rem;
        color: white;
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
    }

    .sheet-act-btn svg {
        width: 22px;
        height: 22px;
    }

    .sheet-act-btn:active {
        background: #3D3D3D;
    }

    .sheet-act-btn.bookmarked {
        background: rgba(255, 107, 24, 0.15);
        border-color: #FF6B18;
    }

    .sheet-act-btn.bookmarked span {
        color: #FF6B18;
    }

    .sheet-close {
        width: 100%;
        padding: 0.65rem;
        background: #2D2D2D;
        border: none;
        color: #aaa;
        font-size: 13px;
        font-weight: 600;
        border-radius: 12px;
        cursor: pointer;
        margin-top: 0.25rem;
    }

    /* ═══════════════════════════════════════
   MOBILE FAB
═══════════════════════════════════════ */
    #mobile-fab {
        position: fixed;
        bottom: 1.25rem;
        right: 1.25rem;
        z-index: 900;
        display: none;
    }

    #mobile-fab-btn {
        width: 52px;
        height: 52px;
        background: #FF6B18;
        border-radius: 50%;
        border: none;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 20px rgba(255, 107, 24, 0.5);
        cursor: pointer;
        transition: transform 0.2s;
    }

    #mobile-fab-btn:active {
        transform: scale(0.9);
    }

    /* ═══════════════════════════════════════
   RESUME TOAST
═══════════════════════════════════════ */
    #resume-toast {
        position: fixed;
        bottom: 1.25rem;
        left: 50%;
        transform: translateX(-50%) translateY(80px);
        background: #1A1A1A;
        border: 1px solid #FF6B18;
        color: white;
        padding: 0.65rem 0.875rem;
        border-radius: 14px;
        font-size: 13px;
        z-index: 99999;
        display: flex;
        align-items: center;
        gap: 0.6rem;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
        opacity: 0;
        max-width: calc(100vw - 2rem);
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    #resume-toast.show {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
    }

    /* ═══════════════════════════════════════
   TOOLBAR NORMAL
═══════════════════════════════════════ */
    .pdf-controls {
        background: linear-gradient(135deg, #1A1A1A, #2D2D2D);
        border-bottom: 2px solid #FF6B18;
        transition: background 0.3s;
    }

    .pcb {
        transition: all 0.2s;
        cursor: pointer;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border: none;
    }

    .pcb:hover:not(:disabled) {
        background: #FF6B18 !important;
        box-shadow: 0 4px 12px rgba(255, 107, 24, 0.35);
    }

    .pcb:disabled {
        opacity: 0.35;
        cursor: not-allowed;
    }

    .pcb.is-bkmk {
        background: #FF6B18 !important;
    }

    .page-input {
        background: #3D3D3D;
        border: 2px solid #4D4D4D;
        color: white;
        outline: none;
        border-radius: 6px;
    }

    .page-input:focus {
        border-color: #FF6B18;
        box-shadow: 0 0 0 3px rgba(255, 107, 24, 0.15);
    }

    #mode-dropdown {
        position: absolute;
        top: calc(100% + 6px);
        right: 0;
        background: #1A1A1A;
        border: 1px solid #3D3D3D;
        border-radius: 10px;
        overflow: hidden;
        z-index: 200;
        min-width: 130px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5);
        display: none;
    }

    #mode-dropdown.open {
        display: block;
    }

    .mode-opt {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.6rem 0.875rem;
        cursor: pointer;
        font-size: 13px;
        color: #ccc;
        transition: background 0.15s;
    }

    .mode-opt:hover {
        background: #2D2D2D;
        color: white;
    }

    .mode-opt.active {
        color: #FF6B18;
        font-weight: 700;
    }

    .spinner {
        border: 4px solid #3D3D3D;
        border-top-color: #FF6B18;
        border-radius: 50%;
        width: 48px;
        height: 48px;
        animation: spin 0.9s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    #desktop-hint {
        position: absolute;
        bottom: 1rem;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.7);
        color: #ccc;
        font-size: 11px;
        padding: 5px 14px;
        border-radius: 99px;
        z-index: 10002;
        pointer-events: none;
        opacity: 1;
        transition: opacity 0.5s;
        white-space: nowrap;
    }

    #desktop-hint.hidden {
        display: none;
    }

    #desktop-hint.fade-out {
        opacity: 0;
    }

    /* ═══════════════════════════════════════
   RESPONSIVE
═══════════════════════════════════════ */
    @media (max-width: 767px) {
        #mobile-fab {
            display: block;
        }

        .desktop-only {
            display: none !important;
        }

        #pdf-viewer-container {
            height: calc(100vh - 52px);
        }
    }

    @media (min-width: 768px) {
        #mobile-fab {
            display: none !important;
        }

        .mobile-only {
            display: none !important;
        }
    }
</style>
@endpush

@section('content')

{{-- ══════════ FULLSCREEN TOOLBAR ══════════ --}}
<div id="pdf-fullscreen-toolbar">
    <span class="flex-1 hidden min-w-0 text-xs font-bold text-white truncate sm:block">{{
        Str::limit($publication->title, 38) }}</span>
    <div class="flex items-center gap-1 bg-[#3D3D3D] rounded-lg px-2 py-1 flex-shrink-0">
        <button id="fs-prev" class="pcb p-1.5 bg-[#4D4D4D] text-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <span class="px-1 text-xs font-semibold text-white whitespace-nowrap"><span id="fs-page-num">1</span>/<span
                id="fs-page-count">-</span></span>
        <button id="fs-next" class="pcb p-1.5 bg-[#4D4D4D] text-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>
    <div class="flex items-center flex-shrink-0 gap-1 desktop-only">
        <button id="fs-zoom-out" class="pcb p-1.5 bg-[#3D3D3D] text-white"><svg class="w-4 h-4" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7" />
            </svg></button>
        <span id="fs-zoom-level" class="text-xs font-semibold text-center text-white w-9">100%</span>
        <button id="fs-zoom-in" class="pcb p-1.5 bg-[#3D3D3D] text-white"><svg class="w-4 h-4" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
            </svg></button>
        <button id="fs-bookmark-btn" class="pcb p-1.5 bg-[#3D3D3D] text-white"><svg id="fs-bkmk-icon" class="w-4 h-4"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
            </svg></button>
    </div>
    <span class="mobile-only text-[11px] text-gray-400 flex-shrink-0">Tap layar = menu</span>
    <button id="exit-fs-btn"
        class="pcb flex items-center gap-1 px-2.5 py-1.5 bg-red-600 hover:!bg-red-700 text-white text-xs font-bold flex-shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
        <span class="hidden sm:inline">Keluar</span>
    </button>
    <div class="absolute bottom-0 left-0 right-0 progress-track">
        <div id="fs-progress-bar" class="progress-fill" style="width:0%"></div>
    </div>
</div>

{{-- ══════════ NORMAL TOOLBAR ══════════ --}}
<div id="pdf-toolbar" class="sticky top-0 z-50 shadow-lg pdf-controls">
    <div class="px-2 sm:px-4 lg:px-8 mx-auto max-w-[1400px] py-2">
        <div class="flex items-center gap-1.5 sm:gap-2">
            <a href="{{ route('publikasi.show', $publication->slug) }}"
                class="pcb p-2 bg-[#3D3D3D] text-white flex items-center gap-1.5 flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span class="hidden text-xs font-semibold sm:inline">Kembali</span>
            </a>
            <div class="flex-1 hidden min-w-0 sm:block">
                <p class="text-xs font-bold text-white truncate">{{ $publication->title }}</p>
                <p id="progress-text" class="text-gray-400 text-[10px] mt-0.5"></p>
            </div>
            <div class="flex items-center gap-1 bg-[#3D3D3D] rounded-lg px-2 py-1.5 flex-shrink-0">
                <button id="prev-page" class="pcb p-1 bg-[#4D4D4D] text-white"><svg class="w-3.5 h-3.5" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg></button>
                <div class="flex items-center gap-1">
                    <input type="number" id="page-num-input"
                        class="page-input w-9 sm:w-11 text-center px-0.5 py-0.5 font-semibold text-xs" value="1"
                        min="1">
                    <span class="text-xs text-gray-400">/</span>
                    <span id="page-count" class="text-xs font-semibold text-white">-</span>
                </div>
                <button id="next-page" class="pcb p-1 bg-[#4D4D4D] text-white"><svg class="w-3.5 h-3.5" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg></button>
            </div>
            <div class="desktop-only flex items-center gap-1.5">
                <button id="zoom-out" class="pcb p-2 bg-[#3D3D3D] text-white"><svg class="w-4 h-4" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7" />
                    </svg></button>
                <span id="zoom-level" class="text-xs font-semibold text-center text-white w-9">100%</span>
                <button id="zoom-in" class="pcb p-2 bg-[#3D3D3D] text-white"><svg class="w-4 h-4" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                    </svg></button>
                <button id="bookmark-btn" class="pcb p-2 bg-[#3D3D3D] text-white" title="Tandai (B)"><svg id="bkmk-icon"
                        class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                    </svg></button>
                <button id="search-btn" class="pcb p-2 bg-[#3D3D3D] text-white" title="Cari (Ctrl+F)"><svg
                        class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg></button>
                <div class="relative">
                    <button id="mode-btn" class="pcb p-2 bg-[#3D3D3D] text-white" title="Mode Baca"><svg class="w-4 h-4"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                        </svg></button>
                    <div id="mode-dropdown">
                        <div class="mode-opt active" data-mode="normal">☀️ Normal</div>
                        <div class="mode-opt" data-mode="sepia">📜 Sepia</div>
                        <div class="mode-opt" data-mode="night">🌙 Night</div>
                    </div>
                </div>
                <button id="fullscreen-btn" class="pcb p-2 bg-[#3D3D3D] text-white" title="Layar Penuh (F)"><svg
                        class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                    </svg></button>
            </div>
            <a href="{{ route('publikasi.download', $publication->slug) }}"
                class="pcb p-2 sm:px-3 bg-[#FF6B18] hover:!bg-[#E64627] text-white flex items-center gap-1.5 flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span class="hidden text-xs font-semibold sm:inline">Download</span>
            </a>
        </div>
    </div>
    <div class="progress-track">
        <div id="reading-progress-bar" class="progress-fill" style="width:0%"></div>
    </div>
</div>

{{-- ══════════ PDF VIEWER ══════════ --}}
<div id="pdf-viewer-container">
    <div id="pdf-loading">
        <div class="spinner"></div>
        <p class="text-sm font-semibold text-white">Memuat dokumen...</p>
        <p class="text-xs text-gray-400">Harap tunggu sebentar</p>
    </div>
    <div id="pdf-canvas-wrapper" class="hidden">
        <div id="pdf-stage">
            <canvas id="pdf-canvas"></canvas>
            <div id="text-layer"></div>
            <div id="annotation-layer"></div>
        </div>
    </div>
    <iframe id="pdf-iframe" title="PDF Viewer"></iframe>
    <div id="desktop-hint" class="hidden">← → halaman &nbsp;·&nbsp; ↑↓ scroll &nbsp;·&nbsp; +/− zoom &nbsp;·&nbsp; B
        tandai &nbsp;·&nbsp; Ctrl+F cari &nbsp;·&nbsp; Esc keluar</div>

    {{-- Mobile Tap Overlay --}}
    <div id="mobile-tap-overlay">
        <p class="w-full max-w-xs text-sm font-bold text-center text-white truncate">{{ Str::limit($publication->title,
            34) }}</p>
        <div class="tap-nav-row">
            <button class="tap-nav-btn" id="tap-prev"><svg class="w-6 h-6" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg></button>
            <div class="tap-nav-center"><strong id="tap-page-num">1</strong><small>dari <span
                        id="tap-page-total">-</span></small></div>
            <button class="tap-nav-btn" id="tap-next"><svg class="w-6 h-6" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                </svg></button>
        </div>
        <div class="tap-zoom-row">
            <button class="tap-zoom-btn" id="tap-zoom-out">−</button>
            <div class="tap-zoom-track">
                <div id="tap-zoom-fill" class="tap-zoom-fill" style="width:25%"></div>
            </div>
            <span id="tap-zoom-val" class="tap-zoom-val">100%</span>
            <button class="tap-zoom-btn" id="tap-zoom-in">+</button>
        </div>
        <div class="tap-mode-row">
            <div class="tap-mode-card active" data-tap-mode="normal">
                <div class="tmc-ic">☀️</div>Normal
            </div>
            <div class="tap-mode-card" data-tap-mode="sepia">
                <div class="tmc-ic">📜</div>Sepia
            </div>
            <div class="tap-mode-card" data-tap-mode="night">
                <div class="tmc-ic">🌙</div>Night
            </div>
        </div>
        <div class="tap-actions">
            <button id="tap-bookmark-btn" class="tap-action-btn">
                <svg id="tap-bkmk-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                </svg>
                <span id="tap-bkmk-label">Tandai Halaman</span>
            </button>
            <button id="tap-exit-btn" class="tap-action-btn danger">
                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span>Keluar Fullscreen</span>
            </button>
        </div>
        <div class="tap-hint-tips">
            <div class="tip-badge"><span class="tip-ic">👉</span>Swipe halaman</div>
            <div class="tip-badge"><span class="tip-ic">🤏</span>Pinch zoom</div>
            <div class="tip-badge"><span class="tip-ic">👆</span>Tap menu</div>
        </div>
        <button class="tap-close-btn" id="tap-close-overlay">Tutup & Lanjut Baca</button>
    </div>
</div>

{{-- ══════════ ANNOTATION TOOLBAR ══════════ --}}
<div id="annot-toolbar">
    <span class="text-[11px] text-gray-400 px-1">Stabilo:</span>
    <button class="annot-tool-btn" data-color="yellow" title="Kuning">
        <div class="color-swatch cs-yellow"></div>
    </button>
    <button class="annot-tool-btn" data-color="green" title="Hijau">
        <div class="color-swatch cs-green"></div>
    </button>
    <button class="annot-tool-btn" data-color="pink" title="Pink">
        <div class="color-swatch cs-pink"></div>
    </button>
    <button class="annot-tool-btn" data-color="blue" title="Biru">
        <div class="color-swatch cs-blue"></div>
    </button>
    <button class="annot-tool-btn" data-color="orange" title="Oranye">
        <div class="color-swatch cs-orange"></div>
    </button>
    <div class="at-sep"></div>
    <button class="annot-tool-btn" id="add-comment-btn" title="Tambah komentar">
        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
        </svg>
    </button>
    <div class="at-sep"></div>
    <button class="annot-tool-btn" id="annot-close-btn" title="Tutup">
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>

{{-- ══════════ COMMENT POPUP ══════════ --}}
<div id="comment-popup">
    <p class="cp-title">💬 Tambah Komentar</p>
    <textarea id="comment-text" placeholder="Tulis komentar untuk teks ini..."></textarea>
    <div class="cp-actions">
        <button class="cp-save" id="comment-save">Simpan</button>
        <button class="cp-cancel" id="comment-cancel">Batal</button>
    </div>
</div>

{{-- ══════════ ANNOTATION TOOLTIP ══════════ --}}
<div id="annot-tooltip">
    <div class="at-text" id="annot-tooltip-text"></div>
    <div class="at-actions">
        <button class="at-btn del" id="annot-tooltip-del">🗑 Hapus</button>
        <button class="at-btn close" id="annot-tooltip-close">✕ Tutup</button>
    </div>
</div>

{{-- ══════════ BOTTOM SHEET ══════════ --}}
<div id="sheet-backdrop"></div>
<div id="bottom-sheet">
    <div class="sheet-handle"></div>
    <p class="mb-3 text-sm font-bold text-white truncate">{{ Str::limit($publication->title, 42) }}</p>
    <div class="sheet-sec">
        <span class="sheet-lbl">Navigasi Halaman</span>
        <div class="sheet-page-row">
            <button class="sheet-page-btn" id="sheet-prev"><svg class="w-5 h-5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg></button>
            <div class="sheet-page-display"><strong id="sheet-page-num">1</strong><small>dari <span
                        id="sheet-total">-</span> halaman</small></div>
            <button class="sheet-page-btn" id="sheet-next"><svg class="w-5 h-5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                </svg></button>
        </div>
        <div class="sheet-jump-row">
            <input type="number" id="sheet-jump" class="sheet-jump-input" placeholder="Lompat ke halaman..." min="1">
            <button id="sheet-jump-go" class="sheet-jump-go">Go</button>
        </div>
    </div>
    <div class="sheet-sec">
        <span class="sheet-lbl">Zoom</span>
        <div class="sheet-zoom-row">
            <button class="sheet-zoom-btn" id="sheet-zoom-out">−</button>
            <div class="sheet-zoom-track">
                <div id="sheet-zoom-fill" class="sheet-zoom-fill" style="width:25%"></div>
            </div>
            <span id="sheet-zoom-val" class="sheet-zoom-val">100%</span>
            <button class="sheet-zoom-btn" id="sheet-zoom-in">+</button>
        </div>
    </div>
    <div class="sheet-sec">
        <span class="sheet-lbl">Mode Baca</span>
        <div class="sheet-mode-row">
            <div class="sheet-mode-card active" data-sheet-mode="normal">
                <div class="smc-ic">☀️</div><span>Normal</span>
            </div>
            <div class="sheet-mode-card" data-sheet-mode="sepia">
                <div class="smc-ic">📜</div><span>Sepia</span>
            </div>
            <div class="sheet-mode-card" data-sheet-mode="night">
                <div class="smc-ic">🌙</div><span>Night</span>
            </div>
        </div>
    </div>
    <div class="sheet-sec">
        <span class="sheet-lbl">Aksi</span>
        <div class="sheet-actions">
            <button id="sheet-bookmark-btn" class="sheet-act-btn">
                <svg id="sheet-bkmk-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                </svg>
                <span id="sheet-bkmk-label">Tandai Halaman</span>
            </button>
            <button id="sheet-fs-btn" class="sheet-act-btn">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                </svg>
                <span>Layar Penuh</span>
            </button>
            <button id="sheet-search-btn" class="sheet-act-btn">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <span>Cari Kata</span>
            </button>
            <a href="{{ route('publikasi.download', $publication->slug) }}" class="sheet-act-btn">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Download</span>
            </a>
        </div>
    </div>
    <button id="sheet-close" class="sheet-close">Tutup</button>
</div>

{{-- ══════════ MOBILE FAB ══════════ --}}
<div id="mobile-fab">
    <button id="mobile-fab-btn" aria-label="Menu">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
        </svg>
    </button>
</div>

{{-- ══════════ SEARCH OVERLAY ══════════ --}}
<div id="search-overlay">
    <div id="search-box">
        <div class="search-input-row">
            <input type="text" id="search-input" placeholder="Cari kata atau kalimat..." autocomplete="off">
            <button class="snav-btn" id="search-prev-btn" title="Sebelumnya">↑</button>
            <button class="snav-btn" id="search-next-btn" title="Berikutnya">↓</button>
            <button class="snav-btn" id="search-close-btn">✕</button>
        </div>
        <div id="search-status">Ketik untuk mencari...</div>
        <div id="search-match-info"></div>
        <div id="search-results-list"></div>
    </div>
</div>

{{-- ══════════ RESUME TOAST ══════════ --}}
<div id="resume-toast">
    <span class="flex-shrink-0 text-xl">🔖</span>
    <div class="min-w-0">
        <p class="text-xs font-bold">Lanjut membaca?</p>
        <p class="text-gray-400 text-[11px]" id="resume-text">Terakhir di halaman —</p>
    </div>
    <button id="resume-yes"
        class="px-3 py-1.5 bg-[#FF6B18] text-white text-xs font-bold rounded-lg flex-shrink-0">Lanjut</button>
    <button id="resume-no"
        class="px-2.5 py-1.5 bg-[#3D3D3D] text-gray-300 text-xs rounded-lg flex-shrink-0">Awal</button>
</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
pdfjsLib.verbosity = 0;

const pdfUrl = @json($pdfUrl);
const slug   = @json($publication->slug);
const SK = { page:`bp_${slug}`, zoom:`bz_${slug}`, mode:`bm_${slug}`, bkmk:`bb_${slug}`, annot:`ba_${slug}` };

// ── State ───────────────────────────────────────────────────────
let pdfDoc         = null;
let pageNum        = 1;
let pageRendering  = false;
let pageNumPending = null;
let baseScale      = 1.0;
// ✅ Zoom minimum 1.0 (100%) agar tidak terlalu kecil
const ZOOM_MIN     = 1.0;
const ZOOM_MAX     = 4.0;
const ZOOM_STEP    = 0.25;
let zoomFactor     = Math.max(ZOOM_MIN, parseFloat(localStorage.getItem(SK.zoom)) || 1.0);
let isFullscreen   = false;
let currentMode    = localStorage.getItem(SK.mode) || 'normal';
let bookmarkedPage = parseInt(localStorage.getItem(SK.bkmk)) || null;
let savedPage      = parseInt(localStorage.getItem(SK.page)) || 1;
let toolbarTimer   = null;
let tapOverlayOpen = false;
let sheetIsOpen    = false;
let searchResults  = [];
let searchIndex    = -1;
let searchHighlightEls = [];
let pendingHighlightColor = null;
let pendingSelectionRange = null;
let annotations    = JSON.parse(localStorage.getItem(SK.annot) || '[]');
let activeAnnotId  = null;
const isMobile     = () => window.innerWidth < 768;

// ── DOM ─────────────────────────────────────────────────────────
const canvas      = document.getElementById('pdf-canvas');
const ctx         = canvas.getContext('2d');
const stage       = document.getElementById('pdf-stage');
const textLayer   = document.getElementById('text-layer');
const annotLayer  = document.getElementById('annotation-layer');
const loadingEl   = document.getElementById('pdf-loading');
const canvasWrap  = document.getElementById('pdf-canvas-wrapper');
const viewerEl    = document.getElementById('pdf-viewer-container');
const iframeEl    = document.getElementById('pdf-iframe');
const fsTb        = document.getElementById('pdf-fullscreen-toolbar');
const deskHint    = document.getElementById('desktop-hint');
const annotTb     = document.getElementById('annot-toolbar');
const commentPop  = document.getElementById('comment-popup');
const annotTip    = document.getElementById('annot-tooltip');
const tapOverlay  = document.getElementById('mobile-tap-overlay');

// ── Helpers ──────────────────────────────────────────────────────
const hideLoading = () => loadingEl.style.display = 'none';
const showCanvas  = () => { canvasWrap.style.display='flex'; canvasWrap.classList.remove('hidden'); };

function snack(msg, color='#FF6B18') {
    const el = Object.assign(document.createElement('div'),{textContent:msg});
    el.style.cssText=`position:fixed;top:1rem;left:50%;transform:translateX(-50%);background:#1A1A1A;border:1px solid ${color};color:#fff;padding:.45rem 1rem;border-radius:99px;font-size:13px;font-weight:600;z-index:99999;transition:opacity .4s;pointer-events:none;white-space:nowrap;`;
    document.body.appendChild(el);
    setTimeout(()=>{el.style.opacity=0;setTimeout(()=>el.remove(),400);},2200);
}

// ── Progress ─────────────────────────────────────────────────────
function updateProgress() {
    if (!pdfDoc) return;
    const pct = (pageNum / pdfDoc.numPages) * 100;
    ['reading-progress-bar','fs-progress-bar'].forEach(id=>{const e=document.getElementById(id);if(e)e.style.width=pct+'%';});
    const est = Math.ceil((pdfDoc.numPages-pageNum)*1.5);
    const pt  = document.getElementById('progress-text');
    if(pt) pt.textContent=`Hal. ${pageNum}/${pdfDoc.numPages} · ${Math.round(pct)}%`+(est>0?` · ~${est} mnt`:'');
    ['sheet-page-num','tap-page-num'].forEach(id=>{const e=document.getElementById(id);if(e)e.textContent=pageNum;});
}

// ── Zoom display ─────────────────────────────────────────────────
function updateZoomDisplay() {
    const label = Math.round(zoomFactor * 100) + '%';
    // Map 1.0–4.0 to 0–100%
    const barPct = Math.round(((zoomFactor - ZOOM_MIN) / (ZOOM_MAX - ZOOM_MIN)) * 100);
    ['zoom-level','fs-zoom-level'].forEach(id=>{const e=document.getElementById(id);if(e)e.textContent=label;});
    ['sheet-zoom-val','tap-zoom-val'].forEach(id=>{const e=document.getElementById(id);if(e)e.textContent=label;});
    ['sheet-zoom-fill','tap-zoom-fill'].forEach(id=>{const e=document.getElementById(id);if(e)e.style.width=Math.max(4,barPct)+'%';});
}

// ── Bookmark ─────────────────────────────────────────────────────
function updateBookmarkUI() {
    const on = bookmarkedPage === pageNum;
    ['bkmk-icon','fs-bkmk-icon','sheet-bkmk-icon','tap-bkmk-icon'].forEach(id=>{
        const ic=document.getElementById(id);
        if(ic){ic.setAttribute('fill',on?'#FF6B18':'none');ic.setAttribute('stroke',on?'#FF6B18':'currentColor');}
    });
    ['bookmark-btn','fs-bookmark-btn'].forEach(id=>{const b=document.getElementById(id);if(b)b.classList.toggle('is-bkmk',on);});
    const sbtn=document.getElementById('sheet-bookmark-btn'); if(sbtn)sbtn.classList.toggle('bookmarked',on);
    const slbl=document.getElementById('sheet-bkmk-label');   if(slbl)slbl.textContent=on?'✓ Ditandai':'Tandai Halaman';
    const tbtn=document.getElementById('tap-bookmark-btn');    if(tbtn)tbtn.classList.toggle('bookmarked',on);
    const tlbl=document.getElementById('tap-bkmk-label');      if(tlbl)tlbl.textContent=on?'✓ Ditandai':'Tandai Halaman';
}
function toggleBookmark() {
    if(bookmarkedPage===pageNum){bookmarkedPage=null;localStorage.removeItem(SK.bkmk);snack('Bookmark dihapus');}
    else{bookmarkedPage=pageNum;localStorage.setItem(SK.bkmk,pageNum);snack('🔖 Halaman '+pageNum+' ditandai!');}
    updateBookmarkUI();
}

// ── Reading Mode ──────────────────────────────────────────────────
function applyMode(mode){
    document.body.classList.remove('read-mode-sepia','read-mode-night');
    if(mode!=='normal') document.body.classList.add('read-mode-'+mode);
    currentMode=mode; localStorage.setItem(SK.mode,mode);
    document.querySelectorAll('.mode-opt').forEach(e=>e.classList.toggle('active',e.dataset.mode===mode));
    document.querySelectorAll('[data-sheet-mode]').forEach(e=>e.classList.toggle('active',e.dataset.sheetMode===mode));
    document.querySelectorAll('[data-tap-mode]').forEach(e=>e.classList.toggle('active',e.dataset.tapMode===mode));
}
applyMode(currentMode);

// ── Scale ─────────────────────────────────────────────────────────
const getScale = () => baseScale * zoomFactor;
function computeBase(page) {
    const w = viewerEl.clientWidth || window.innerWidth;
    // ✅ Base scale dibuat agar 100% zoom = halaman terbaca jelas (fit width)
    baseScale = Math.max(0.6, Math.min((w - 16) / page.getViewport({scale:1}).width, 2.5));
}

// ── Text Layer ────────────────────────────────────────────────────
async function renderTextLayer(page, viewport) {
    textLayer.innerHTML = '';
    textLayer.style.width  = viewport.width + 'px';
    textLayer.style.height = viewport.height + 'px';

    const content = await page.getTextContent();
    const scale   = viewport.scale;

    content.items.forEach(item => {
        if (!item.str.trim()) return;
        const tx = pdfjsLib.Util.transform(viewport.transform, item.transform);
        const span = document.createElement('span');
        const fontHeight = Math.sqrt(tx[2]*tx[2] + tx[3]*tx[3]);
        const angle = Math.atan2(tx[1], tx[0]);

        span.textContent = item.str;
        span.style.fontSize = fontHeight + 'px';
        span.style.left  = tx[4] + 'px';
        span.style.top   = (tx[5] - fontHeight) + 'px';
        if (angle !== 0) span.style.transform = `rotate(${-angle}rad)`;
        // Scale text width to match PDF width
        const actualWidth = item.width * scale;
        const measuredWidth = fontHeight * item.str.length * 0.55;
        if (measuredWidth > 0 && actualWidth > 0)
            span.style.transform = (span.style.transform || '') + ` scaleX(${actualWidth / measuredWidth})`;

        textLayer.appendChild(span);
    });
}

// ── Render ────────────────────────────────────────────────────────
function renderPage(num) {
    pageRendering = true;
    hideLoading(); showCanvas();
    pdfDoc.getPage(num).then(async page => {
        if (baseScale === 1.0) computeBase(page);
        const vp = page.getViewport({ scale: getScale() });
        canvas.height = vp.height; canvas.width = vp.width;
        stage.style.width  = vp.width  + 'px';
        stage.style.height = vp.height + 'px';

        await page.render({ canvasContext: ctx, viewport: vp }).promise.catch(e=>console.warn(e.message));
        pageRendering = false;
        if (pageNumPending !== null) { const p=pageNumPending; pageNumPending=null; renderPage(p); return; }

        await renderTextLayer(page, vp);
        renderAnnotationsOnLayer();

        localStorage.setItem(SK.page, num);
        localStorage.setItem(SK.zoom, zoomFactor);
        document.getElementById('page-num-input').value    = num;
        document.getElementById('fs-page-num').textContent = num;
        updateNavButtons(); updateZoomDisplay(); updateProgress(); updateBookmarkUI();
        canvasWrap.scrollTo({ top: 0, behavior: 'smooth' });
        // Re-apply search highlights if search is active
        if (searchResults.length > 0) applySearchHighlights();
    }).catch(e=>{console.error(e.message);pageRendering=false;hideLoading();showCanvas();});
}
function queueRender(n){if(pageRendering)pageNumPending=n;else renderPage(n);}

// ── Navigation ────────────────────────────────────────────────────
function prevPage(){if(pageNum>1){pageNum--;queueRender(pageNum);}}
function nextPage(){if(pageNum<pdfDoc.numPages){pageNum++;queueRender(pageNum);}}
function goTo(n){if(pdfDoc&&n>=1&&n<=pdfDoc.numPages){pageNum=n;queueRender(n);}}

function updateNavButtons(){
    ['prev-page','fs-prev','sheet-prev','tap-prev'].forEach(id=>{const e=document.getElementById(id);if(e)e.disabled=pageNum<=1;});
    ['next-page','fs-next','sheet-next','tap-next'].forEach(id=>{const e=document.getElementById(id);if(e)e.disabled=pageNum>=pdfDoc.numPages;});
}

// ── Zoom ──────────────────────────────────────────────────────────
function zoomIn()  { zoomFactor=Math.min(zoomFactor+ZOOM_STEP,ZOOM_MAX); queueRender(pageNum); }
// ✅ Zoom tidak bisa di bawah ZOOM_MIN = 1.0 (100%)
function zoomOut() { zoomFactor=Math.max(zoomFactor-ZOOM_STEP,ZOOM_MIN); queueRender(pageNum); }

// ══════════════════════════════════════════════════════════════════
//  ANNOTATION SYSTEM
// ══════════════════════════════════════════════════════════════════
function saveAnnotations() {
    localStorage.setItem(SK.annot, JSON.stringify(annotations));
}

// ── Render stored annotations on layer ───────────────────────────
function renderAnnotationsOnLayer() {
    annotLayer.innerHTML = '';
    const pageAnnots = annotations.filter(a => a.page === pageNum);
    const scale      = getScale();

    pageAnnots.forEach(annot => {
        const el = document.createElement('div');
        el.className  = `annot-highlight color-${annot.color}`;
        el.style.left   = (annot.rect.x * scale) + 'px';
        el.style.top    = (annot.rect.y * scale) + 'px';
        el.style.width  = (annot.rect.w * scale) + 'px';
        el.style.height = (annot.rect.h * scale) + 'px';
        el.dataset.id   = annot.id;

        el.addEventListener('click', e => {
            e.stopPropagation();
            showAnnotTooltip(annot, e.clientX, e.clientY);
        });
        annotLayer.appendChild(el);
    });
}

// ── Show annotation tooltip ───────────────────────────────────────
function showAnnotTooltip(annot, x, y) {
    activeAnnotId = annot.id;
    document.getElementById('annot-tooltip-text').textContent =
        annot.comment ? `💬 ${annot.comment}` : `Stabilo ${annot.color} — "${annot.selectedText?.substring(0,60)}..."`;
    annotTip.classList.add('show');
    // Position smartly
    const vw = window.innerWidth, vh = window.innerHeight;
    const tw = 260, th = 100;
    annotTip.style.left = Math.min(x, vw - tw - 12) + 'px';
    annotTip.style.top  = (y + 12 + th > vh ? y - th - 8 : y + 12) + 'px';
}

document.getElementById('annot-tooltip-close').addEventListener('click', () => {
    annotTip.classList.remove('show'); activeAnnotId = null;
});
document.getElementById('annot-tooltip-del').addEventListener('click', () => {
    if (!activeAnnotId) return;
    annotations = annotations.filter(a => a.id !== activeAnnotId);
    saveAnnotations(); renderAnnotationsOnLayer();
    annotTip.classList.remove('show'); activeAnnotId = null;
    snack('Anotasi dihapus');
});

// ── Get selected text rect ────────────────────────────────────────
function getSelectionRect() {
    const sel = window.getSelection();
    if (!sel || sel.isCollapsed || !sel.rangeCount) return null;
    const range   = sel.getRangeAt(0);
    const stRect  = stage.getBoundingClientRect();
    const rects   = Array.from(range.getClientRects());
    if (!rects.length) return null;

    // Merge all rects into one bounding box
    const left   = Math.min(...rects.map(r=>r.left));
    const top    = Math.min(...rects.map(r=>r.top));
    const right  = Math.max(...rects.map(r=>r.right));
    const bottom = Math.max(...rects.map(r=>r.bottom));
    const scale  = getScale();

    return {
        x: (left  - stRect.left) / scale,
        y: (top   - stRect.top)  / scale,
        w: (right - left)        / scale,
        h: (bottom - top)        / scale,
        rects: rects.map(r => ({
            x: (r.left - stRect.left) / scale,
            y: (r.top  - stRect.top)  / scale,
            w: r.width  / scale,
            h: r.height / scale,
        }))
    };
}

// ── Annotation toolbar on text select ────────────────────────────
function positionAnnotToolbar(x, y) {
    const vw = window.innerWidth, vh = window.innerHeight;
    const tbW = 300, tbH = 52;
    let tx = Math.min(x - tbW/2, vw - tbW - 8);
    let ty = y - tbH - 12;
    if (ty < 8) ty = y + 20;
    annotTb.style.left = Math.max(8, tx) + 'px';
    annotTb.style.top  = ty + 'px';
}

function showAnnotToolbar(range) {
    const sel = window.getSelection();
    if (!sel || sel.isCollapsed) return;
    const rect = sel.getRangeAt(0).getBoundingClientRect();
    pendingSelectionRange = range;
    positionAnnotToolbar(rect.left + rect.width/2, rect.top);
    annotTb.classList.add('show');
}
function hideAnnotToolbar() {
    annotTb.classList.remove('show');
    pendingSelectionRange = null;
    pendingHighlightColor = null;
}

// Detect text selection end
document.addEventListener('mouseup', e => {
    if (e.target.closest('#annot-toolbar, #comment-popup, #annot-tooltip')) return;
    setTimeout(() => {
        const sel = window.getSelection();
        if (sel && !sel.isCollapsed && sel.rangeCount > 0) {
            const range = sel.getRangeAt(0);
            // Only show toolbar if selection is inside text layer
            if (textLayer.contains(range.commonAncestorContainer)) {
                showAnnotToolbar(range);
            }
        } else {
            hideAnnotToolbar();
        }
    }, 50);
});

document.addEventListener('touchend', e => {
    if (e.target.closest('#annot-toolbar, #comment-popup, #annot-tooltip, #mobile-tap-overlay')) return;
    setTimeout(() => {
        const sel = window.getSelection();
        if (sel && !sel.isCollapsed && sel.rangeCount > 0) {
            const range = sel.getRangeAt(0);
            if (textLayer.contains(range.commonAncestorContainer)) {
                showAnnotToolbar(range);
            }
        }
    }, 200);
});

// Color buttons
document.querySelectorAll('.annot-tool-btn[data-color]').forEach(btn => {
    btn.addEventListener('click', e => {
        e.stopPropagation();
        const color = btn.dataset.color;
        const rect  = getSelectionRect();
        const sel   = window.getSelection();
        if (!rect) { snack('Pilih teks dulu!'); return; }
        const selectedText = sel ? sel.toString() : '';
        const annot = { id: Date.now(), page: pageNum, color, rect, selectedText, comment: '' };
        annotations.push(annot);
        saveAnnotations();
        renderAnnotationsOnLayer();
        sel?.removeAllRanges();
        hideAnnotToolbar();
        snack(`✏️ Stabilo ${color} diterapkan!`);
    });
});

// Comment button
document.getElementById('add-comment-btn').addEventListener('click', e => {
    e.stopPropagation();
    const rect = getSelectionRect();
    if (!rect) { snack('Pilih teks dulu!'); return; }
    pendingHighlightColor = 'yellow';
    // Position comment popup
    const sel = window.getSelection();
    const br  = sel?.getRangeAt(0).getBoundingClientRect();
    const vw  = window.innerWidth, vh = window.innerHeight;
    commentPop.style.left = Math.min((br?.left||100)-140, vw-296) + 'px';
    commentPop.style.top  = ((br?.bottom||200) + 10 + 160 > vh ? (br?.top||200) - 170 : (br?.bottom||200) + 10) + 'px';
    commentPop.classList.add('show');
    document.getElementById('comment-text').value = '';
    document.getElementById('comment-text').focus();
});

document.getElementById('comment-save').addEventListener('click', () => {
    const rect    = getSelectionRect();
    const sel     = window.getSelection();
    const comment = document.getElementById('comment-text').value.trim();
    if (!rect || !comment) { snack('Tulis komentar dulu!'); return; }
    const selectedText = sel?.toString() || '';
    annotations.push({ id: Date.now(), page: pageNum, color: 'yellow', rect, selectedText, comment });
    saveAnnotations(); renderAnnotationsOnLayer();
    sel?.removeAllRanges();
    commentPop.classList.remove('show');
    hideAnnotToolbar();
    snack('💬 Komentar disimpan!');
});

document.getElementById('comment-cancel').addEventListener('click', () => {
    commentPop.classList.remove('show');
});

document.getElementById('annot-close-btn').addEventListener('click', () => {
    window.getSelection()?.removeAllRanges();
    hideAnnotToolbar();
});

// Click outside → close annotation toolbar
document.addEventListener('click', e => {
    if (!annotTb.contains(e.target) && !commentPop.contains(e.target) && !annotTip.contains(e.target)) {
        if (!e.target.closest('#text-layer')) hideAnnotToolbar();
        annotTip.classList.remove('show');
    }
});

// ══════════════════════════════════════════════════════════════════
//  SEARCH WITH TEXT HIGHLIGHT
// ══════════════════════════════════════════════════════════════════
let searchDebounce = null;
let currentSearchQuery = '';

function openSearch()  { document.getElementById('search-overlay').classList.add('show'); document.getElementById('search-input').focus(); }
function closeSearch() {
    document.getElementById('search-overlay').classList.remove('show');
    document.getElementById('search-results-list').innerHTML='';
    document.getElementById('search-status').textContent='Ketik untuk mencari...';
    document.getElementById('search-match-info').textContent='';
    document.getElementById('search-input').value='';
    searchResults=[]; searchIndex=-1; currentSearchQuery='';
    clearSearchHighlights();
}

function clearSearchHighlights() {
    document.querySelectorAll('.search-highlight').forEach(el=>el.remove());
    searchHighlightEls = [];
}

// ✅ Highlight kata di text layer berdasarkan posisi span
function applySearchHighlights() {
    clearSearchHighlights();
    if (!currentSearchQuery || !pdfDoc) return;
    const q    = currentSearchQuery.toLowerCase();
    const stRect = stage.getBoundingClientRect();
    const scale  = getScale();

    // Find matching spans in text layer
    const spans = Array.from(textLayer.querySelectorAll('span'));
    let matchIdx = 0;

    spans.forEach(span => {
        const txt = span.textContent.toLowerCase();
        let idx = txt.indexOf(q);
        while (idx !== -1) {
            // Get position of this span
            const sr = span.getBoundingClientRect();
            const charW = sr.width / span.textContent.length;
            const highlightEl = document.createElement('div');
            highlightEl.className = 'search-highlight';
            // Approximate position of the matched text within span
            const offsetX = charW * idx;
            const matchW  = charW * q.length;
            highlightEl.style.left   = ((sr.left - stRect.left + offsetX) + 'px');
            highlightEl.style.top    = ((sr.top  - stRect.top)  + 'px');
            highlightEl.style.width  = Math.min(matchW, sr.width) + 'px';
            highlightEl.style.height = sr.height + 'px';
            highlightEl.dataset.matchIdx = matchIdx;
            annotLayer.appendChild(highlightEl);
            searchHighlightEls.push(highlightEl);
            matchIdx++;
            idx = txt.indexOf(q, idx + 1);
        }
    });
    highlightActiveMatch();
}

function highlightActiveMatch() {
    searchHighlightEls.forEach((el, i) => {
        el.classList.toggle('active-match', i === searchIndex);
    });
    // Scroll active match into view
    if (searchHighlightEls[searchIndex]) {
        searchHighlightEls[searchIndex].scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    // Update result list active
    document.querySelectorAll('.sri').forEach((el,i) => el.classList.toggle('active-sri', i === searchIndex));
}

async function doSearch(query) {
    if (!pdfDoc || !query.trim()) {
        document.getElementById('search-status').textContent = 'Ketik untuk mencari...';
        document.getElementById('search-results-list').innerHTML = '';
        document.getElementById('search-match-info').textContent = '';
        clearSearchHighlights(); currentSearchQuery = ''; return;
    }
    document.getElementById('search-status').textContent = 'Mencari di semua halaman...';
    searchResults=[]; currentSearchQuery = query;
    const q = query.toLowerCase();

    for (let p=1; p<=pdfDoc.numPages; p++) {
        const page    = await pdfDoc.getPage(p);
        const content = await page.getTextContent();
        const text    = content.items.map(i=>i.str).join(' ');
        const lText   = text.toLowerCase();
        let idx = lText.indexOf(q);
        while (idx !== -1) {
            const start   = Math.max(0,idx-35);
            const excerpt = text.substring(start, idx+q.length+50).trim();
            searchResults.push({ page: p, excerpt, charIdx: idx });
            idx = lText.indexOf(q, idx+1);
        }
    }

    const list   = document.getElementById('search-results-list');
    const status = document.getElementById('search-status');
    list.innerHTML = '';

    if (!searchResults.length) {
        status.textContent = `Tidak ditemukan: "${query}"`;
        document.getElementById('search-match-info').textContent = '';
        clearSearchHighlights(); return;
    }

    status.textContent = `${searchResults.length} hasil ditemukan`;
    searchIndex = 0;

    searchResults.slice(0,40).forEach((r,i) => {
        const item = document.createElement('div');
        item.className = 'sri' + (i===0?' active-sri':'');
        const hl = r.excerpt.replace(
            new RegExp(query.replace(/[.*+?^${}()|[\]\\]/g,'\\$&'),'gi'),
            m => `<mark${i===0?' class="active-mark"':''}>${m}</mark>`
        );
        item.innerHTML = `<span class="pg">Hal.${r.page}</span><span class="ex">${hl}</span>`;
        item.addEventListener('click', () => {
            searchIndex = i;
            document.querySelectorAll('.sri').forEach((el,j)=>el.classList.toggle('active-sri',j===i));
            if (r.page !== pageNum) {
                goTo(r.page);
                // Highlights re-applied after render via renderPage
            } else {
                applySearchHighlights();
                highlightActiveMatch();
            }
            updateMatchInfo();
        });
        list.appendChild(item);
    });

    updateMatchInfo();
    // Apply highlights on current page
    if (searchResults[0].page === pageNum) {
        applySearchHighlights();
    } else {
        goTo(searchResults[0].page);
    }
}

function updateMatchInfo() {
    const el = document.getElementById('search-match-info');
    const onPage = searchResults.filter(r=>r.page===pageNum);
    if (searchResults.length)
        el.textContent = `${searchIndex+1}/${searchResults.length} hasil · ${onPage.length} di halaman ini`;
}

function searchNavNext() {
    if (!searchResults.length) return;
    searchIndex = (searchIndex+1) % searchResults.length;
    const r = searchResults[searchIndex];
    if (r.page !== pageNum) { goTo(r.page); }
    else { applySearchHighlights(); highlightActiveMatch(); }
    updateMatchInfo();
}
function searchNavPrev() {
    if (!searchResults.length) return;
    searchIndex = (searchIndex-1+searchResults.length) % searchResults.length;
    const r = searchResults[searchIndex];
    if (r.page !== pageNum) { goTo(r.page); }
    else { applySearchHighlights(); highlightActiveMatch(); }
    updateMatchInfo();
}

document.getElementById('search-input').addEventListener('input', function() {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(() => doSearch(this.value), 450);
});
document.getElementById('search-close-btn').addEventListener('click', closeSearch);
document.getElementById('search-prev-btn').addEventListener('click', searchNavPrev);
document.getElementById('search-next-btn').addEventListener('click', searchNavNext);
document.getElementById('search-overlay').addEventListener('click', e => {
    if (e.target===document.getElementById('search-overlay')) closeSearch();
});
document.getElementById('search-input').addEventListener('keydown', e => {
    if (e.key==='Enter') e.shiftKey ? searchNavPrev() : searchNavNext();
});

// ── Mobile Tap Overlay ────────────────────────────────────────────
function openTapOverlay()  { tapOverlayOpen=true;  tapOverlay.classList.add('show'); }
function closeTapOverlay() { tapOverlayOpen=false; tapOverlay.classList.remove('show'); }

document.getElementById('tap-close-overlay').addEventListener('click', closeTapOverlay);
document.getElementById('tap-prev').addEventListener('click', prevPage);
document.getElementById('tap-next').addEventListener('click', nextPage);
document.getElementById('tap-zoom-in').addEventListener('click', zoomIn);
document.getElementById('tap-zoom-out').addEventListener('click', zoomOut);
document.getElementById('tap-bookmark-btn').addEventListener('click', toggleBookmark);
document.getElementById('tap-exit-btn').addEventListener('click', () => { closeTapOverlay(); exitFullscreen(); });
document.querySelectorAll('[data-tap-mode]').forEach(el => {
    el.addEventListener('click', () => {
        applyMode(el.dataset.tapMode);
        snack({normal:'☀️ Normal',sepia:'📜 Sepia',night:'🌙 Night'}[el.dataset.tapMode]);
    });
});

// ── Bottom Sheet ──────────────────────────────────────────────────
function openSheet()  { sheetIsOpen=true;  document.getElementById('sheet-backdrop').classList.add('show');  document.getElementById('bottom-sheet').classList.add('show'); }
function closeSheet() { sheetIsOpen=false; document.getElementById('sheet-backdrop').classList.remove('show'); document.getElementById('bottom-sheet').classList.remove('show'); }

document.getElementById('sheet-backdrop').addEventListener('click', closeSheet);
document.getElementById('sheet-close').addEventListener('click', closeSheet);
document.getElementById('sheet-prev').addEventListener('click', prevPage);
document.getElementById('sheet-next').addEventListener('click', nextPage);
document.getElementById('sheet-zoom-in').addEventListener('click', zoomIn);
document.getElementById('sheet-zoom-out').addEventListener('click', zoomOut);
document.getElementById('sheet-bookmark-btn').addEventListener('click', toggleBookmark);
document.getElementById('sheet-fs-btn').addEventListener('click', ()=>{closeSheet();setTimeout(enterFullscreen,200);});
document.getElementById('sheet-search-btn').addEventListener('click', ()=>{closeSheet();setTimeout(openSearch,200);});
document.getElementById('sheet-jump-go').addEventListener('click', ()=>{const n=parseInt(document.getElementById('sheet-jump').value);if(n){goTo(n);closeSheet();}});
document.getElementById('sheet-jump').addEventListener('keydown', e=>{if(e.key==='Enter'){const n=parseInt(e.target.value);if(n){goTo(n);closeSheet();}}});
document.querySelectorAll('[data-sheet-mode]').forEach(el=>{
    el.addEventListener('click',()=>{applyMode(el.dataset.sheetMode);snack({normal:'☀️ Normal',sepia:'📜 Sepia',night:'🌙 Night'}[el.dataset.sheetMode]);});
});

// ── Mobile FAB ────────────────────────────────────────────────────
document.getElementById('mobile-fab-btn').addEventListener('click', e=>{e.stopPropagation();openSheet();});

// ── Fullscreen ────────────────────────────────────────────────────
function enterFullscreen() {
    isFullscreen=true;
    viewerEl.classList.add('fullscreen-mode');
    document.body.style.overflow='hidden';
    if (!isMobile()) {
        deskHint.classList.remove('hidden','fade-out');
        clearTimeout(toolbarTimer);
        toolbarTimer=setTimeout(()=>deskHint.classList.add('fade-out'),4500);
    }
    if (pdfDoc) pdfDoc.getPage(pageNum).then(p=>{baseScale=1.0;computeBase(p);queueRender(pageNum);});
}
function exitFullscreen() {
    isFullscreen=false;
    viewerEl.classList.remove('fullscreen-mode');
    document.body.style.overflow='';
    deskHint.classList.add('hidden');
    closeTapOverlay();
    if (pdfDoc) pdfDoc.getPage(pageNum).then(p=>{baseScale=1.0;computeBase(p);queueRender(pageNum);});
}

viewerEl.addEventListener('mousemove', ()=>{
    if (!isFullscreen||isMobile()) return;
    fsTb.classList.remove('toolbar-hidden');
    clearTimeout(toolbarTimer);
    toolbarTimer=setTimeout(()=>fsTb.classList.add('toolbar-hidden'),3000);
});

viewerEl.addEventListener('click', e=>{
    if (!isFullscreen||!isMobile()) return;
    if (e.target.closest('#pdf-fullscreen-toolbar,#mobile-tap-overlay,#bottom-sheet')) return;
    if (window.getSelection()?.toString()) return; // Don't open overlay on text select
    tapOverlayOpen ? closeTapOverlay() : openTapOverlay();
});

// ── Iframe Fallback ───────────────────────────────────────────────
function showFallback(){hideLoading();canvasWrap.style.display='none';iframeEl.style.display='block';iframeEl.src=pdfUrl;}

// ── Resume Toast ──────────────────────────────────────────────────
function showResumeToast(page){
    const t=document.getElementById('resume-toast');
    document.getElementById('resume-text').textContent=`Terakhir di halaman ${page}`;
    t.classList.add('show');
    document.getElementById('resume-yes').onclick=()=>{goTo(page);t.classList.remove('show');};
    document.getElementById('resume-no').onclick=()=>{goTo(1);t.classList.remove('show');};
    setTimeout(()=>t.classList.remove('show'),7000);
}

// ── Load PDF ──────────────────────────────────────────────────────
const fbTimer=setTimeout(()=>{if(!pdfDoc)showFallback();},8000);
pdfjsLib.getDocument({url:pdfUrl,withCredentials:false,verbosity:0})
    .promise.then(doc=>{
        clearTimeout(fbTimer); pdfDoc=doc;
        const total=doc.numPages;
        ['page-count','fs-page-count','sheet-total','tap-page-total'].forEach(id=>{const e=document.getElementById(id);if(e)e.textContent=total;});
        document.getElementById('page-num-input').max=total;
        document.getElementById('sheet-jump').max=total;
        renderPage(1);
        if(savedPage>1&&savedPage<=total) setTimeout(()=>showResumeToast(savedPage),900);
    })
    .catch(()=>{clearTimeout(fbTimer);showFallback();});

// ── Resize ────────────────────────────────────────────────────────
let lastW=viewerEl.clientWidth,rTimer=null;
window.addEventListener('resize',()=>{
    const w=viewerEl.clientWidth; if(Math.abs(w-lastW)<20) return; lastW=w;
    clearTimeout(rTimer);
    rTimer=setTimeout(()=>{
        if(!pdfDoc) return;
        pdfDoc.getPage(pageNum).then(p=>{baseScale=1.0;computeBase(p);queueRender(pageNum);});
    },250);
});

// ── Desktop Event Listeners ───────────────────────────────────────
document.getElementById('prev-page').addEventListener('click',prevPage);
document.getElementById('next-page').addEventListener('click',nextPage);
document.getElementById('fs-prev').addEventListener('click',prevPage);
document.getElementById('fs-next').addEventListener('click',nextPage);
document.getElementById('zoom-in').addEventListener('click',zoomIn);
document.getElementById('zoom-out').addEventListener('click',zoomOut);
document.getElementById('fs-zoom-in').addEventListener('click',zoomIn);
document.getElementById('fs-zoom-out').addEventListener('click',zoomOut);
document.getElementById('bookmark-btn').addEventListener('click',toggleBookmark);
document.getElementById('fs-bookmark-btn').addEventListener('click',toggleBookmark);
document.getElementById('fullscreen-btn').addEventListener('click',enterFullscreen);
document.getElementById('exit-fs-btn').addEventListener('click',exitFullscreen);
document.getElementById('search-btn').addEventListener('click',openSearch);
document.getElementById('mode-btn').addEventListener('click',e=>{e.stopPropagation();document.getElementById('mode-dropdown').classList.toggle('open');});
document.querySelectorAll('.mode-opt').forEach(el=>{el.addEventListener('click',()=>{applyMode(el.dataset.mode);document.getElementById('mode-dropdown').classList.remove('open');});});
document.addEventListener('click',()=>document.getElementById('mode-dropdown')?.classList.remove('open'));
document.getElementById('page-num-input').addEventListener('change',function(){const n=parseInt(this.value);if(pdfDoc&&n>=1&&n<=pdfDoc.numPages)goTo(n);else this.value=pageNum;});

// ── Keyboard ─────────────────────────────────────────────────────
document.addEventListener('keydown',e=>{
    if((e.ctrlKey||e.metaKey)&&e.key==='f'){e.preventDefault();openSearch();return;}
    if(['INPUT','TEXTAREA'].includes(e.target.tagName)) return;
    switch(e.key){
        case 'ArrowLeft':  prevPage(); break;
        case 'ArrowRight': nextPage(); break;
        case 'ArrowUp':    canvasWrap.scrollBy({top:-120,behavior:'smooth'}); break;
        case 'ArrowDown':  canvasWrap.scrollBy({top:120,behavior:'smooth'});  break;
        case '+':case '=': zoomIn();   break;
        case '-':          zoomOut();  break;
        case 'b':case 'B': toggleBookmark(); break;
        case 'f':case 'F': isFullscreen?exitFullscreen():enterFullscreen(); break;
        case 'Escape':
            if(document.getElementById('search-overlay').classList.contains('show'))closeSearch();
            else if(commentPop.classList.contains('show'))commentPop.classList.remove('show');
            else if(isFullscreen)exitFullscreen();
            break;
    }
});

// ── Touch: Swipe horizontal ganti halaman, vertikal scroll ───────
let tx=0,ty=0,pd=0,touchMoved=false,pinching=false;

viewerEl.addEventListener('touchstart',e=>{
    touchMoved=false; pinching=false;
    if(e.touches.length===1){tx=e.touches[0].clientX;ty=e.touches[0].clientY;}
    if(e.touches.length===2){
        pinching=true;
        pd=Math.hypot(e.touches[0].clientX-e.touches[1].clientX,e.touches[0].clientY-e.touches[1].clientY);
    }
},{passive:true});

viewerEl.addEventListener('touchmove',e=>{
    touchMoved=true;
    if(e.touches.length!==2) return;
    const d=Math.hypot(e.touches[0].clientX-e.touches[1].clientX,e.touches[0].clientY-e.touches[1].clientY);
    // ✅ Pinch: minimum zoom ZOOM_MIN, jadi tidak bisa zoom out terlalu kecil
    if(Math.abs(d-pd)>14){
        if(d>pd) zoomIn(); else zoomOut();
        pd=d;
    }
},{passive:true});

viewerEl.addEventListener('touchend',e=>{
    if(pinching||!touchMoved) return;
    const dx=tx-e.changedTouches[0].clientX;
    const dy=ty-e.changedTouches[0].clientY;
    // Hanya swipe horizontal dominan yg ganti halaman
    if(Math.abs(dx)>Math.abs(dy)*1.8&&Math.abs(dx)>65){
        if(tapOverlayOpen){closeTapOverlay();return;}
        if(window.getSelection()?.toString()) return; // jangan ganti halaman saat ada teks dipilih
        dx>0?nextPage():prevPage();
    }
},{passive:true});
</script>
@endpush