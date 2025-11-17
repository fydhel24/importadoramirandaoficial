@extends('adminlte::page')

@section('title', 'Cancelar Ventas de la Última Semana')

@section('content')
<div class="container-fluid">
    <h1 class="page-title">Cancelar Ventas de la Última Semana</h1>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-table"></i> Ventas Registradas en la Última Semana
        </div>
        <div class="card-body">
            <!-- Formulario de búsqueda -->
            <div class="mb-3">
                <input type="text" id="search" class="form-control " placeholder="Buscar venta..." />
            </div>

            <form id="reporte-form" action="{{ route('cancelarventa.nota') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-file-alt"></i> Generar Reporte
                </button>
                

                <div class="table-responsive mt-4">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th>ID Venta</th>
                                <th>Fecha de Venta</th>
                                <th>Vendedor</th>
                                <th>Cliente</th>
                                <th>CI</th>
                                <th>Producto</th>
                                <th>Cantidad Vendida</th>
                                <th>Precio Unitario</th>
                                <th>Stock en Almacen</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="ventasTableBody">
                            <!-- Los productos se cargarán dinámicamente aquí -->
                        </tbody>
                    </table>
                </div>
            </form>

            <!-- Paginación -->
            <div id="pagination-links" class="pagination-gutter">
                <!-- Los links de paginación se cargarán aquí dinámicamente -->
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 para éxito -->
@if (session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: '¡Éxito!',
                text: '{{ session('success') }}',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            });
        });
    </script>
@endif

@push('js')
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.all.min.js"></script>

    <script>
        $(document).ready(function() {
            // Función para cargar las ventas con AJAX y paginación
            function fetchVentas(page = 1, search = '') {
                $.ajax({
                    url: '{{ route('cancelarventa.ultimasemana') }}',
                    method: 'GET',
                    data: {
                        page: page,
                        search: search,
                    },
                    success: function(response) {
                        var ventas = response.data;
                        var html = '';
                        ventas.forEach(function(ventaProducto) {
                            html += `
                                <tr>
                                    <td>
                                        <input type="checkbox" name="venta_ids[]" value="${ventaProducto.id_venta}">
                                    </td>
                                    
                                    <td>${ventaProducto.id_venta}</td>
                                    <td>${ventaProducto.venta.fecha}</td>
                                    <td>${ventaProducto.venta.user.name}</td>
                                    <td>${ventaProducto.venta.nombre_cliente}</td>
                                    <td>${ventaProducto.venta.ci}</td>
                                    <td>${ventaProducto.producto.nombre}</td>
                                    <td>${ventaProducto.cantidad}</td>
                                    <td>${ventaProducto.precio_unitario}</td>
                                    <td>${ventaProducto.producto.stock}</td>
                                    <td>
                                        <form action="{{ route('cancelarventa.reportesemana', '') }}/${ventaProducto.id}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-info btn-sm">
                                                <i class="fas fa-file-alt"></i> Reporte
                                            </button>
                                        </form>
                                        <form action="{{ route('cancelarventa.revertirsemana', '') }}/${ventaProducto.id}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-undo-alt"></i> Revertir
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            `;
                        });
                        $('#ventasTableBody').html(html);

                        // Paginación
                        var paginationHtml = '';
                        var currentPage = response.current_page;
                        var lastPage = response.last_page;
                        var range = 5;

                        if (currentPage > 1) {
                            paginationHtml += `<li class="page-item page-indicator">
                                <a class="page-link" href="javascript:void(0)" data-page="${currentPage - 1}">
                                    <i class="la la-angle-left"></i>
                                </a>
                            </li>`;
                        }

                        var startPage = Math.max(1, currentPage - range);
                        var endPage = Math.min(lastPage, currentPage + range);

                        for (var i = startPage; i <= endPage; i++) {
                            paginationHtml += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                                <a class="page-link" href="javascript:void(0)" data-page="${i}">${i}</a>
                            </li>`;
                        }

                        if (currentPage < lastPage) {
                            paginationHtml += `<li class="page-item page-indicator">
                                <a class="page-link" href="javascript:void(0)" data-page="${currentPage + 1}">
                                    <i class="la la-angle-right"></i>
                                </a>
                            </li>`;
                        }

                        $('#pagination-links').html(`<nav><ul class="pagination pagination-gutter">${paginationHtml}</ul></nav>`);
                    }
                });
            }

            // Cargar la primera página de ventas
            fetchVentas();

            // Búsqueda en tiempo real
            $('#search').on('keyup', function() {
                var search = $(this).val();
                fetchVentas(1, search);
            });

            // Paginación
            $(document).on('click', '.page-link', function(e) {
                e.preventDefault();
                var page = $(this).data('page');
                var search = $('#search').val();
                fetchVentas(page, search);
            });

            // Seleccionar todos los checkboxes
            document.getElementById('select-all').addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('input[name="venta_ids[]"]');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });

            // Validar el formulario antes de enviarlo
            $('#reporte-form').on('submit', function() {
                const checkboxes = $('input[name="venta_ids[]"]:checked');
                if (checkboxes.length === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Debes seleccionar al menos una venta.',
                    });
                    return false;
                }
            });
        });
    </script>
@endpush
@endsection

