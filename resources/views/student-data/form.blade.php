@extends('layouts.public')

@section('title', 'Actualizar Datos')

@section('content')
    <livewire:student-data-update-form :token="$token" />
@endsection
