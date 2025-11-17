@extends('adminlte::page')

@php
    use Illuminate\Support\Str;
@endphp

@section('title', 'Lista de Productos')

@section('content')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/tablas.css') }}">

    <div class="container">
        <h1 class="mb-4">Lista de Productos</h1>

        <div class="btn-group" role="group" aria-label="Basic example">
            @can('productos.create')
                <a href="{{ route('productos.create') }}" class="btn btn-gradient-primary btn-lg">
                    <i class="fas fa-plus-circle"></i> Crear Nuevo Producto
                </a>
            @endcan

            @can('reporte.index')
                <a href="{{ route('productos.report') }}" class="btn btn-gradient-danger btn-lg">
                    <i class="fas fa-file-alt"></i> Generar Reporte
                </a>
            @endcan
        </div>

        <!-- Formulario de filtros -->
        <form action="{{ route('productos.generarReporte') }}" method="GET" class="mt-4">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="id_categoria">Categoría</label>
                    <select name="id_categoria" id="id_categoria" class="form-control">
                        <option value="">Seleccionar categoría</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}">{{ $categoria->categoria }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="id_marca">Marca</label>
                    <select name="id_marca" id="id_marca" class="form-control">
                        <option value="">Seleccionar marca</option>
                        @foreach ($marcas as $marca)
                            <option value="{{ $marca->id }}">{{ $marca->marca }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-gradient-success btn-lg">
                        <i class="fas fa-filter"></i> Generar Reporte Filtrado
                    </button>
                </div>
            </div>
        </form>


        <!-- Tabla de productos -->
        <div class="card shadow-lg border-0 mt-4" style="border-radius: 15px;">
            <div class="card-header bg-gradient-blue text-white"
                style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h3 class="card-title"><i class="fas fa-box-open"></i> Productos Registrados</h3>
            </div>
            <div class="card-body" style="background: #f8f9fa;">
                <!-- Buscador -->
                <div class="mb-3">
                    <input type="text" id="search" class="form-control" placeholder="Buscar productos...">
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="productosTable">
                        <thead class="linear-gradient">
                            <tr>
                                <th>CODIGO</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                                <th>Fotos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Los productos se cargarán aquí dinámicamente -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Éxito!',
                    text: '{{ session('success') }}',
                    icon: 'success',
                    confirmButtonText: 'Aceptar'
                });
            });
        </script>
    @endif
@endsection

@section('js')
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS & CSS -->
    <!-- DataTables JS & CSS (CDN) -->
    <link href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.4.0/css/responsive.dataTables.min.css" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.0/js/dataTables.responsive.min.js"></script>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.all.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inicializar DataTable
            var table = $('#productosTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('productos.index') }}',
                    data: function(d) {
                        d.search = $('#search').val(); // Pasar el término de búsqueda al servidor
                    }
                },
                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'nombre'
                    },
                    {
                        data: 'descripcion',
                        render: function(data, type, row) {
                            var descripcionCorta = data.length > 80 ? data.substr(0, 80) + '...' :
                                data;
                            var descripcionCompleta = data; // Descripción completa para expandir

                            return `
                                <span class="descripcion-corta">${descripcionCorta}</span>
                                <span class="descripcion-completa d-none">${descripcionCompleta}</span>
                                <button class="btn btn-link ver-mas">Ver más</button>
                            `;
                        }
                    },
                    {
                        data: 'precio'
                    },
                    {
                        data: 'stock'
                    },
                    {
                        data: 'estado',
                        render: function(data, type, row) {
                            return data ? '<span class="badge badge-success">Destacado</span>' :
                                '<span class="badge badge-danger">No Destacado</span>';
                        }
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            var actions = '';

                            // Agregar botón de editar si el usuario tiene permiso
                            @can('productos.edit')
                                actions += `
                                    <a href="/productos/${row.id}/edit" class="btn btn-gradient-success btn-sm">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                `;
                            @endcan

                            // Agregar formulario para eliminar si el usuario tiene permiso
                            @can('productos.destroy')
                                actions += `
                                    <form action="/productos/${row.id}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-gradient-danger btn-sm">
                                            <i class="fas fa-power-off"></i> Desactivar
                                        </button>
                                    </form>
                                `;
                            @endcan

                            return actions;
                        }
                    },
                    {
                        data: 'fotos',
                        render: function(data, type, row) {
                            var fotosHtml = '';
                            row.fotos.forEach(function(foto) {
                                fotosHtml +=
                                    `<img src="{{ asset('storage/') }}/${foto.foto}" alt="Foto" class="img-thumbnail" style="width: 50px; margin-right: 5px;" loading="lazy">`;
                            });
                            return fotosHtml;
                        }
                    },
                ],
                
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-MX.json" // Traducción al español
                },
                
                dom: 'lrtip', // Esto muestra solo la tabla y la paginación
            });

            // Búsqueda en tiempo real
            $('#search').on('keyup', function() {
                table.draw(); // Redibuja la tabla cada vez que se ingresa texto en el buscador
            });

            // Funcionalidad "Ver más / Ver menos" para la columna descripción
            $('#productosTable').on('click', '.ver-mas', function() {
                var btn = $(this);
                var descripcionCorta = btn.prev('.descripcion-corta');
                var descripcionCompleta = btn.prevAll('.descripcion-completa');

                if (btn.text() === 'Ver más') {
                    descripcionCorta.addClass('d-none');
                    descripcionCompleta.removeClass('d-none');
                    btn.text('Ver menos');
                } else {
                    descripcionCompleta.addClass('d-none');
                    descripcionCorta.removeClass('d-none');
                    btn.text('Ver más');
                }
            });
        });
    </script>
@endsection
