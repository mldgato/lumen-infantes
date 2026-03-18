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
    @else
        <livewire:profesor.dashboard />
    @endcan
@endsection

@section('footer')
    <div class="float-right d-none d-sm-block">
        <b>Version</b> 1.4.0
    </div>
    <strong>&copy; {{ date('Y') }} <a href="mailto:manueldardon@hotmail.com">Manuel Dardón</a>.</strong> Todos los derechos reservados.
@endsection