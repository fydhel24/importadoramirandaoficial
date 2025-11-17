{{-- filepath: d:\Trabajo Miranda\20-03-2025\pro\resources\views\sales_report\month.blade.php --}}
@extends('adminlte::page')

@section('title', 'Reporte de Ventas - Por Mes')

@section('content_header')
    <h1>Reporte de Ventas - Por Mes</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header bg-gradient-blue text-white">
            <h3 class="card-title"><i class="fas fa-calendar-alt"></i> Seleccione una Sucursal y Mes</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="branchSelect">Sucursal:</label>
                        <select id="branchSelect" class="form-control">
                            <option value="">-- Seleccione una sucursal --</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="monthPicker">Mes:</label>
                        <input type="month" id="monthPicker" class="form-control" placeholder="Seleccione un mes">
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3 id="totalProductosVendidos">0</h3>
                            <p>Total Productos Vendidos</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-box"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3 id="totalGanancia">0</h3>
                            <p>Total Ganancia</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3 id="totalEfectivo">0</h3>
                            <p>Total en Efectivo</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3 id="totalQr">0</h3>
                            <p>Total en QR</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-qrcode"></i>
                        </div>
                    </div>
                </div>
            </div>

            <table id="salesTable" class="table table-bordered table-striped">
                <thead class="bg-gradient-blue text-white">
                    <tr>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Fecha</th>
                        <th>Tipo de Pago</th>
                        <th>Productos</th>
                        <th>Costo</th>
                        <th>Vendedor</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Los datos se cargarán aquí mediante AJAX -->
                </tbody>
            </table>

            <h4 class="mt-4">Ventas por Usuario</h4>
            <table id="userSalesTable" class="table table-bordered table-striped">
                <thead class="bg-gradient-blue text-white">
                    <tr>
                        <th>Usuario</th>
                        <th>Total Ventas</th>
                        <th>Productos Vendidos</th>
                        <th>Total en Efectivo</th>
                        <th>Total en QR</th>
                        <th>Total General</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Los datos se cargarán aquí mediante AJAX -->
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@stop

@section('js')
    <script>
        $(document).ready(function () {
            // Configurar el selector de meses
            $("#monthPicker").on("change", function () {
                salesTable.ajax.reload();
                userSalesTable.ajax.reload();
            });

            // Configurar DataTable para las ventas
            const salesTable = $('#salesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/sales-report/data',
                    data: function (d) {
                        d.branch_id = $('#branchSelect').val();
                        d.month = $('#monthPicker').val(); // Enviar el mes seleccionado
                        d.filter_type = 'month'; // Filtrar por mes
                    },
                    dataSrc: function (json) {
                        // Actualizar totales
                        $('#totalProductosVendidos').text(json.totalProductosVendidos);
                        $('#totalGanancia').text(json.totalGanancia);
                        $('#totalEfectivo').text(json.totalEfectivo);
                        $('#totalQr').text(json.totalQr);

                        return json.data;
                    }
                },
                columns: [
                    { data: 'cliente', name: 'cliente' },
                    { data: 'costo_total', name: 'costo_total' },
                    { data: 'fecha', name: 'fecha' },
                    { data: 'tipo_pago', name: 'tipo_pago' },
                    { data: 'productos', name: 'productos', orderable: false, searchable: false },
                    { data: 'costo', name: 'costo', orderable: false, searchable: false },
                    { data: 'vendedor', name: 'user.name' }
                ],
                language: {
                    decimal: ",",
                    thousands: ".",
                    processing: "Procesando...",
                    lengthMenu: "Mostrar _MENU_ registros",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    infoEmpty: "No hay registros disponibles",
                    infoFiltered: "(filtrado de _MAX_ registros)",
                    search: "Buscar:",
                    zeroRecords: "No se encontraron registros coincidentes",
                    emptyTable: "No hay datos disponibles en la tabla",
                    paginate: {
                        first: "Primero",
                        previous: "Anterior",
                        next: "Siguiente",
                        last: "Último"
                    },
                    aria: {
                        sortAscending: ": activar para ordenar la columna de manera ascendente",
                        sortDescending: ": activar para ordenar la columna de manera descendente"
                    }
                }
            });

            // Configurar DataTable para las ventas por usuario
            const userSalesTable = $('#userSalesTable').DataTable({
                searching: false,
                paging: false,
                info: false,
                ordering: false,
                ajax: {
                    url: '/sales-report/data',
                    data: function (d) {
                        d.branch_id = $('#branchSelect').val();
                        d.month = $('#monthPicker').val(); // Enviar el mes seleccionado
                        d.filter_type = 'month'; // Filtrar por mes
                    },
                    dataSrc: function (json) {
                        const userSales = json.ventasPorUsuario || [];
                        userSalesTable.clear();
                        userSales.forEach(function (user) {
                            userSalesTable.row.add([
                                user.usuario,
                                user.total_ventas,
                                user.productos_vendidos,
                                user.total_efectivo,
                                user.total_qr,
                                user.total_general
                            ]);
                        });
                        userSalesTable.draw();
                        return [];
                    }
                }
            });

            // Recargar las tablas al cambiar la sucursal
            $('#branchSelect').change(function () {
                salesTable.ajax.reload();
                userSalesTable.ajax.reload();
            });
        });
    </script>
@stop