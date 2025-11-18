@extends('adminlte::page')

@section('title', "Productos - {$sucursal->nombre}")

@section('content_header')
    <h1>Productos en {{ $sucursal->nombre }}</h1>
@stop

@section('content')
<div class="container-fluid" x-data="productosApp({{ $sucursalId }})">
    @include('ventas.partials.search-box')

    <div id="productos-list" class="row" x-cloak>
        {{-- Inyectado vía AJAX --}}
    </div>

    <div class="d-flex justify-content-center mt-4" id="pagination-links"></div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/axios@1.6.7/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.10/dist/cdn.min.js" defer></script>
<script src="{{ asset('js/ventas/productos-alpine.js') }}"></script>
<style>
    .cursor-pointer { cursor: pointer; }
    .text-truncate {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    [x-cloak] { display: none !important; }
</style>
@endsection