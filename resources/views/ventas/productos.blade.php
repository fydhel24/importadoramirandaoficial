@extends('adminlte::page')

@section('title', "Productos - {$sucursal->nombre}")

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>Productos en {{ $sucursal->nombre }}</h1>
    <div class="position-relative" x-data="{ carrito: $store.carrito }">
        <div class="d-flex align-items-center text-white bg-primary rounded-pill px-3 py-1 shadow-sm">
            <i class="fas fa-shopping-cart me-2"></i>
            <span class="fw-bold" x-text="carrito.totalItems || 0"></span>
        </div>
    </div>
</div>
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