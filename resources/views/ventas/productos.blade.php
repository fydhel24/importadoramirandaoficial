@extends('adminlte::page')

@section('title', "Productos - {$sucursal->nombre}")

@section('content_header')
    <h1>Productos en {{ $sucursal->nombre }}</h1>
@stop

@section('content')
<div class="container-fluid" x-data="productosApp({{ $sucursalId }})">
    <div class="row mb-3">
        <div class="col-md-6">
            <a href="{{ route('ventas.index') }}" class="btn btn-secondary">← Volver</a>
        </div>
        <div class="col-md-6 text-right">
            <div class="position-relative">
                <input 
                    type="text"
                    x-model="query"
                    @focus="inputFocused = true"
                    @blur="setTimeout(() => inputFocused = false, 150)"
                    class="form-control"
                    placeholder="Buscar productos..."
                    autocomplete="off"
                >
                <!-- Sugerencias SIN imágenes, solo texto, y solo si el input está enfocado -->
                <div 
                    x-show="sugerencias.length > 0 && inputFocused"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="position-absolute bg-white border rounded shadow w-100 mt-1"
                    style="max-height: 250px; overflow-y: auto; z-index: 1050;">
                    <template x-for="item in sugerencias" :key="item.id">
                        <div 
                            class="d-flex align-items-center p-2 border-bottom cursor-pointer"
                            style="height: 44px;"
                            @click="selectSuggestion(item)">
                            <span class="text-truncate" x-text="item.nombre"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <div id="productos-list" class="row" x-cloak>
        <!-- Productos inyectados vía AJAX -->
    </div>

    <div class="d-flex justify-content-center mt-4" id="pagination-links"></div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/axios@1.6.7/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.10/dist/cdn.min.js" defer></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('productosApp', (sucursalId) => ({
            query: '',
            sugerencias: [],
            inputFocused: false,
            debounceTimer: null,

            init() {
                this.loadProductos();
                this.$watch('query', () => {
                    if (this.query.length < 2) {
                        this.sugerencias = [];
                    }
                    clearTimeout(this.debounceTimer);
                    this.debounceTimer = setTimeout(() => {
                        this.loadProductos(1);
                        if (this.query.length >= 2) {
                            this.buscarSugerencias();
                        }
                    }, 350);
                });
            },

            async loadProductos(page = 1) {
                const url = new URL(`/ventas/productos/${sucursalId}`, window.location.origin);
                if (this.query.length >= 2) {
                    url.searchParams.set('search', this.query);
                }
                url.searchParams.set('page', page);

                try {
                    const response = await axios.get(url.toString(), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    document.getElementById('productos-list').innerHTML = response.data.html;
                    document.getElementById('pagination-links').innerHTML = response.data.pagination;
                } catch (err) {
                    console.error('Error al cargar productos:', err);
                }
            },

            async buscarSugerencias() {
                try {
                    const res = await axios.get(`/ventas/sugerencias/${sucursalId}`, {
                        params: { q: this.query }
                    });
                    this.sugerencias = res.data;
                } catch (err) {
                    console.error('Error en sugerencias:', err);
                }
            },

            selectSuggestion(item) {
                this.query = item.nombre;
                this.sugerencias = [];
                this.inputFocused = false;
            }
        }));
    });

    // Soporte para paginación AJAX
    document.addEventListener('click', (e) => {
        const link = e.target.closest('.pagination a');
        if (!link) return;
        e.preventDefault();
        const url = new URL(link.href);
        const page = url.searchParams.get('page') || 1;
        const alpineEl = document.querySelector('[x-data]');
        alpineEl?.__x?.loadProductos(parseInt(page));
    });
</script>

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