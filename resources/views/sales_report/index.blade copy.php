{{-- filepath: d:\Trabajo Miranda\20-03-2025\pro\resources\views\sales_report\index.blade.php --}}
@extends('adminlte::page')

@section('title', 'Reporte de Ventas')

@section('content_header')
    <h1>Reporte de Ventas</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header bg-gradient-blue text-white">
            <h3 class="card-title"><i class="fas fa-chart-line"></i> Seleccione una Sucursal</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="branchSelect">Sucursal:</label>
                <select id="branchSelect" class="form-control">
                    <option value="">-- Seleccione una sucursal --</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->nombre }}</option>
                    @endforeach
                </select>
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
                        <th>Fecha y Hora</th>
                        <th>Tipo de Pago</th>
                        <th>Usuario</th>
                        <th>Productos Vendidos</th>
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

@section('js')
<script>
    $(document).ready(function () {
        const table = $('#salesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '/sales-report/data',
                data: function (d) {
                    d.branch_id = $('#branchSelect').val();
                },
                dataSrc: function (json) {
                    // Actualizar totales
                    $('#totalProductosVendidos').text(json.totalProductosVendidos);
                    $('#totalGanancia').text(json.totalGanancia);
                    $('#totalEfectivo').text(json.totalEfectivo);
                    $('#totalQr').text(json.totalQr);

                    // Actualizar tabla de ventas por usuario
                    const userSalesTable = $('#userSalesTable').DataTable();
                    userSalesTable.clear();
                    $.each(json.ventasPorUsuario, function (index, userSales) {
                        userSalesTable.row.add([
                            userSales.usuario,
                            userSales.total_ventas,
                            userSales.productos_vendidos,
                            userSales.total_efectivo,
                            userSales.total_qr,
                            userSales.total_general
                        ]);
                    });
                    userSalesTable.draw();

                    return json.data;
                }
            },
            columns: [
                { data: 'fecha', name: 'fecha' },
                { data: 'tipo_pago', name: 'tipo_pago' },
                { data: 'user.nombre', name: 'user.nombre' },
                { data: 'productos', name: 'productos', orderable: false, searchable: false }
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

        $('#branchSelect').change(function () {
            table.ajax.reload();
        });

        // Inicializar tabla de ventas por usuario
        $('#userSalesTable').DataTable({
            searching: false,
            paging: false,
            info: false,
            ordering: false,
            language: {
                emptyTable: "No hay datos disponibles"
            }
        });
    });
</script>
@stop