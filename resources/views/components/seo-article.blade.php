@props([ 'publication' , 'authors'=> [],
'coverUrl' => null,
'keywords' => [],
])

@php
$authorList = collect($authors)->map(fn($a) => [
'@type' => 'Person',
'name' => $a['name'] ?? '',
])->values()->toArray();

$schema = json_encode([
'@context' => 'https://schema.org',
'@type' => 'ScholarlyArticle',
'headline' => $publication->title,
'description' => Str::limit(strip_tags($publication->abstract ?? ''), 155),
'image' => $coverUrl,
'datePublished' => $publication->published_at?->toISOString(),
'dateModified' => $publication->updated_at?->toISOString(),
'url' => route('publikasi.show', $publication->slug),
'keywords' => implode(', ', $keywords),
'publisher' => [
'@type' => 'Organization',
'name' => 'DABRAKA',
'logo' => [
'@type' => 'ImageObject',
'url' => config('app.url') . '/assets/images/logos/logo.png',
],
],
'author' => $authorList,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
@endphp

<script type="application/ld+json">
    {!! $schema !!}
</script>