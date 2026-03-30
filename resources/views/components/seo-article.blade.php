@props([
'publication',
'authors' => [],
'coverUrl' => null,
'date' => null,
'keywords' => [],
])

<script type="application/ld+json">
    {
    "@context": "https://schema.org",
    "@type": "ScholarlyArticle",
    "headline": "{{ addslashes($publication->title) }}",
    "description": "{{ addslashes(Str::limit(strip_tags($publication->abstract ?? ''), 155)) }}",
    "image": "{{ $coverUrl }}",
    "datePublished": "{{ $publication->published_at?->toISOString() }}",
    "dateModified": "{{ $publication->updated_at?->toISOString() }}",
    "url": "{{ route('publikasi.show', $publication->slug) }}",
    "keywords": "{{ implode(', ', $keywords) }}",
    "publisher": {
        "@type": "Organization",
        "name": "DABRAKA",
        "logo": {
            "@type": "ImageObject",
            "url": "{{ config('app.url') }}/assets/images/logos/logo.png"
        }
    },
    "author": [
        @foreach($authors as $index => $author)
        {
            "@type": "Person",
            "name": "{{ addslashes($author['name']) }}"
        }{{ !$loop->last ? ',' : '' }}
        @endforeach
    ]
}
</script>