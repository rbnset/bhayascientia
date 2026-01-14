@extends('layouts.app')

@section('title', 'Publikasi')
@section('main_class', 'mt-0 pb-[120px] sm:pb-0')
@section('hide_footer', 'true')

@section('content')
<x-publication.navigation :subItems="[
            ['label' => 'Browse', 'icon' => 'assets/images/icons/heart.svg', 'href' => url('/publikasi'), 'active' => request()->is('publikasi')],
            ['label' => 'Filter', 'icon' => 'assets/images/icons/status-up.svg', 'href' => url('/publikasi/filter'), 'active' => request()->is('publikasi/filter')],
            ['label' => 'My Favorite', 'icon' => 'assets/images/icons/car.svg', 'href' => url('/publikasi/favorite'), 'active' => request()->is('publikasi/favorite')],
            ['label' => 'Profile', 'icon' => 'assets/images/icons/coffee.svg', 'href' => url('/profile'), 'active' => request()->is('profile*')],
        ]" :bottomItems="[
            ['label' => 'Browse', 'href' => url('/publikasi'), 'active' => request()->is('publikasi'), 'icon' => 'assets/images/icons/heart.svg', 'iconActive' => 'assets/images/icons/heart.svg', 'badge' => 0],
            ['label' => 'Filter', 'href' => url('/publikasi/filter'), 'active' => request()->is('publikasi/filter'), 'icon' => 'assets/images/icons/status-up.svg', 'iconActive' => 'assets/images/icons/status-up.svg', 'badge' => 0],
            ['label' => 'My Favorite', 'href' => url('/publikasi/favorite'), 'active' => request()->is('publikasi/favorite'), 'icon' => 'assets/images/icons/car.svg', 'iconActive' => 'assets/images/icons/car.svg', 'badge' => 0],
            ['label' => 'Profile', 'href' => url('/profile'), 'active' => request()->is('profile*'), 'icon' => 'assets/images/icons/coffee.svg', 'iconActive' => 'assets/images/icons/coffee.svg', 'badge' => auth()->check() ? auth()->user()->unreadNotifications()->count() : 0],
        ]" />

<x-hero.publication />
@endsection