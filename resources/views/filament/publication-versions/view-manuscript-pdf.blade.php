<x-filament-panels::page>
    @php
    /** @var \App\Models\PublicationVersion $version */
    $version = $this->getRecord();

    $pdfUrl = route('manuscripts.view', $version);
    $downloadUrl = route('manuscripts.download', $version);

    $title = $version->publication?->title ?? 'Publication';
    $versionNumber = $version->version_number ?? '-';
    @endphp

    <div class="pvx">
        <div class="pvx-header">
            <div>
                <div class="pvx-kicker">PDF Preview</div>
                <div class="pvx-title">{{ $title }}</div>
                <div class="pvx-subtitle">Version: {{ $versionNumber }}</div>
            </div>

            <div class="pvx-actions">
                <a class="pvx-btn pvx-btn--soft" href="{{ url()->previous() }}">
                    Kembali
                </a>

                <a class="pvx-btn" href="{{ $downloadUrl }}" target="_blank" rel="noopener">
                    Download PDF
                </a>
            </div>
        </div>

        <div class="pvx-frame">
            <iframe src="{{ $pdfUrl }}" title="Manuscript PDF" loading="lazy"></iframe>
        </div>
    </div>

    <style>
        .pvx {
            display: grid;
            gap: 1rem;
        }

        .pvx-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 1rem;
            padding: 1rem 1.25rem;
            border-radius: 18px;
            background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
            color: white;
            box-shadow: 0 14px 35px rgba(249, 115, 22, 0.18);
        }

        .pvx-kicker {
            font-size: 0.75rem;
            font-weight: 900;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            opacity: 0.95;
        }

        .pvx-title {
            margin-top: 0.25rem;
            font-size: 1.15rem;
            font-weight: 900;
            line-height: 1.25;
        }

        .pvx-subtitle {
            margin-top: 0.25rem;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .pvx-actions {
            display: flex;
            gap: 0.6rem;
            flex-wrap: wrap;
        }

        .pvx-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.7rem 1rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 900;
            color: #111827;
            background: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.25);
            transition: transform 0.15s ease;
        }

        .pvx-btn:hover {
            transform: translateY(-1px);
        }

        .pvx-btn--soft {
            background: rgba(255, 255, 255, 0.16);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
        }

        /* PDF area scroll */
        .pvx-frame {
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 12px 30px rgba(17, 24, 39, 0.06);
        }

        .pvx-frame iframe {
            width: 100%;
            height: 75vh;
            border: 0;
        }
    </style>
</x-filament-panels::page>