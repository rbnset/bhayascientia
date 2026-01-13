@extends('layouts.app')

@section('title', 'Publikasi')

{{-- override jarak main khusus halaman ini --}}
@section('main_class', 'mt-0')

@section('content')
<x-hero.publication />
@endsection
