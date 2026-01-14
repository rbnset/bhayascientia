@extends('layouts.app')

@section('title', 'Publikasi')
@section('main_class', 'mt-0 pb-[120px] sm:pb-0')
@section('hide_footer', 'true')

@section('content')
<x-publication.navigation :subItems="[
            ['label' => 'Browse', 'icon' => 'assets/images/icons/3dcube.svg', 'href' => url('/publikasi'), 'active' => request()->is('publikasi')],
            ['label' => 'Filter', 'icon' => 'assets/images/icons/calendar-date-range-dark.svg', 'href' => url('/publikasi/filter'), 'active' => request()->is('publikasi/filter')],
            ['label' => 'My Favorite', 'icon' => 'assets/images/icons/star-dark.svg', 'href' => url('/publikasi/favorite'), 'active' => request()->is('publikasi/favorite')],
            ['label' => 'Profile', 'icon' => 'assets/images/icons/user-dark.svg', 'href' => url('/profile'), 'active' => request()->is('profile*')],
        ]" :bottomItems="[
            ['label' => 'Browse', 'href' => url('/publikasi'), 'active' => request()->is('publikasi'), 'icon' => 'assets/images/icons/3dcube-white.svg', 'iconActive' => 'assets/images/icons/3dcube.svg', 'badge' => 0],
            ['label' => 'Filter', 'href' => url('/publikasi/filter'), 'active' => request()->is('publikasi/filter'), 'icon' => 'assets/images/icons/calendar-date-range-light.svg', 'iconActive' => 'assets/images/icons/calendar-date-range-dark.svg', 'badge' => 0],
            ['label' => 'My Favorite', 'href' => url('/publikasi/favorite'), 'active' => request()->is('publikasi/favorite'), 'icon' => 'assets/images/icons/star-white.svg', 'iconActive' => 'assets/images/icons/start-dark.svg', 'badge' => 0],
            ['label' => 'Profile', 'href' => url('/profile'), 'active' => request()->is('profile*'), 'icon' => 'assets/images/icons/user-white.svg', 'iconActive' => 'assets/images/icons/user-dark.svg', 'badge' => auth()->check() ? auth()->user()->unreadNotifications()->count() : 0],
        ]" />

<x-hero.publication />
@endsection
