@extends('adminlte::page')

@section('title', 'Roles')

@section('adminlte_css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
@stop

@section('content_header')
    <h1 class="m-0 text-white">
        <i class="fas fa-user-tag mr-2"></i> Roles
    </h1>
@endsection

@section('content')
    <livewire:admin.roles.show-roles />
@endsection
