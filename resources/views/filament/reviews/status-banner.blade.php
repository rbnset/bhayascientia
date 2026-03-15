{{--
resources/views/filament/reviews/status-banner.blade.php

FIX BUG 10: Wrapper view untuk status banner.
Diperlukan karena \Filament\Forms\Components\Placeholder adalah
Forms component dan tidak bisa dipakai di dalam infolist Schema.
View::make() dengan viewData() adalah cara yang benar di Filament v3 infolist.
--}}
{!! $bannerHtml !!}