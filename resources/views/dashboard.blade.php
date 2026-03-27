@extends('adminlte::page')

@section('title', 'Dashboard')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="m-0 text-white">
        <i class="fas fa-tachometer-alt mr-2"></i>
        @can('admin.grade-books.index')
            Panel de Administración
        @else
            Mi Panel
        @endcan
    </h1>
@endsection

@section('content')
    @can('admin.grade-books.index')
        <livewire:admin.dashboard />
    @endcan
    @can('profesor.menu.cuadros')
        <livewire:profesor.dashboard />
    @endcan
    @can('admin.secretary')
        
    @endcan
@endsection
