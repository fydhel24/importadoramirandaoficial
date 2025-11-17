@extends('adminlte::page')

@section('title', 'Crear Reporte de Caja')

@section('content')
    <div class="container mt-5">
        <div class="card shadow-sm border-light rounded">
            <div class="card-header bg-light text-dark text-center">
                <h2 class="card-title font-weight-bold">Crear Reporte de Caja - Importadora Miranda</h2>
            </div>
            <div class="card-body">
                <form action="{{ route('caja_sucursal.create') }}" method="GET" class="mb-4">
                    @csrf
                    <div class="row align-items-center">
                        <div class="col-md-5 col-12 mb-3">
                            <div class="form-group">
                                <label for="fecha_inicio" class="font-weight-bold">Fecha Inicio:</label>
                                <input type="datetime-local" id="fecha_inicio" name="fecha_inicio" class="form-control"
                                    value="{{ old('fecha_inicio', $fechaInicio) }}">
                            </div>
                        </div>
                        <div class="col-md-5 col-12 mb-3">
                            <div class="form-group">
                                <label for="fecha_fin" class="font-weight-bold">Fecha Fin:</label>
                                <input type="datetime-local" id="fecha_fin" name="fecha_fin" class="form-control"
                                    value="{{ old('fecha_fin', $fechaFin) }}">
                            </div>
                        </div>
                        <div class="col-md-2 col-12 mb-3">
                            <button type="submit" class="btn btn-primary btn-block py-2 font-weight-bold rounded-pill">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                        </div>
                    </div>
                </form>

                <h3 class="mt-4 font-weight-bold">Resultados Filtrados:</h3>
                <form action="{{ route('caja_sucursal.store') }}" method="POST">
                    @csrf
                    <table class="table table-striped table-sm table-responsive-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>Sucursales</th>
                                <th>Total Vendido</th>
                                <th>Total QR</th>
                                <th>Total Efectivo</th>
                                <th>QR Oficial</th>
                                <th>Efectivo Oficial</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sucursales as $sucursal)
                                <tr>
                                    <td>{{ $sucursal->nombre }}</td>
                                    <td>{{ number_format($totalesPorSucursal[$sucursal->id]['total_vendido'] ?? 0, 2) }}
                                    </td>
                                    <td>{{ number_format($totalesPorSucursal[$sucursal->id]['total_qr'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($totalesPorSucursal[$sucursal->id]['total_efectivo'] ?? 0, 2) }}
                                    </td>
                                    <td>
                                        <input type="text" name="sucursales[{{ $sucursal->id }}][qr_oficial]"
                                            class="form-control form-control-sm only-numbers-with-decimal"
                                            value="{{ old('sucursales.' . $sucursal->id . '.qr_oficial', 0) }}">
                                    </td>
                                    <td>
                                        <input type="text" name="sucursales[{{ $sucursal->id }}][efectivo_oficial]"
                                            class="form-control form-control-sm only-numbers-with-decimal"
                                            value="{{ old('sucursales.' . $sucursal->id . '.efectivo_oficial', 0) }}">
                                    </td>
                                    <input type="hidden" name="sucursales[{{ $sucursal->id }}][total_vendido]"
                                        value="{{ $totalesPorSucursal[$sucursal->id]['total_vendido'] ?? 0 }}">
                                    <input type="hidden" name="sucursales[{{ $sucursal->id }}][total_qr]"
                                        value="{{ $totalesPorSucursal[$sucursal->id]['total_qr'] ?? 0 }}">
                                    <input type="hidden" name="sucursales[{{ $sucursal->id }}][total_efectivo]"
                                        value="{{ $totalesPorSucursal[$sucursal->id]['total_efectivo'] ?? 0 }}">
                                </tr>
                            @endforeach

                            <!-- Fila para la sucursal "PEDIDOS" -->
                            <tr class="table-secondary">
                                <td><strong>PEDIDOS</strong></td>
                                <td>-</td>
                                <td>{{ number_format($totalDepositoPedidos, 2) }}</td>
                                <td>-</td>
                                <td>
                                    <input type="text" name="sucursales[PEDIDOS][qr_oficial]"
                                        class="form-control form-control-sm only-numbers-with-decimal"
                                        value="{{ old('sucursales.PEDIDOS.qr_oficial', 0) }}"
                                        autocomplete="off">
                                </td>
                                <td>
                                    <input type="text" name="sucursales[PEDIDOS][efectivo_oficial]"
                                        class="form-control form-control-sm only-numbers-with-decimal"
                                        value="{{ old('sucursales.PEDIDOS.efectivo_oficial', 0) }}"
                                        autocomplete="off">
                                </td>
                                <input type="hidden" name="sucursales[PEDIDOS][total_vendido]" value="0">
                                <input type="hidden" name="sucursales[PEDIDOS][total_qr]"
                                    value="{{ $totalDepositoPedidos }}">
                                <input type="hidden" name="sucursales[PEDIDOS][total_efectivo]" value="0">
                            </tr>
                            <tr class="table-info">
                                <td><strong>TOTALES</strong></td>
                                <td id="total-vendido">0.00</td>
                                <td id="total-qr">0.00</td>
                                <td id="total-efectivo">0.00</td>
                                <td id="total-qr-oficial">0.00</td>
                                <td id="total-efectivo-oficial">0.00</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Campo para ingresar detalles -->
                    <div class="form-group mt-4">
                        <label for="detalle" class="font-weight-bold">Detalle:</label>
                        <textarea id="detalle" name="detalle" class="form-control" rows="3" placeholder="Ingrese detalles aquí..."></textarea>
                    </div>

                    <!-- Campos ocultos para las fechas -->
                    <input type="hidden" name="fecha_inicio"
                        value="{{ \Carbon\Carbon::parse($fechaInicio)->toDateTimeString() }}">
                    <input type="hidden" name="fecha_fin"
                        value="{{ \Carbon\Carbon::parse($fechaFin)->toDateTimeString() }}">

                    <!-- Mostrar las fechas seleccionadas -->
                    <div class="alert alert-info mt-4">
                        <strong>Fecha Inicio:</strong> {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y H:i') }} <br>
                        <strong>Fecha Fin:</strong> {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y H:i') }}
                    </div>

                    <!-- Botón para guardar los datos -->
                    <button type="submit" class="btn btn-success btn-block py-2 font-weight-bold rounded-pill">
                        <i class="fas fa-save"></i> Guardar Reporte
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <style>
        /* Estilos globales */
        .card {
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            border-radius: 12px 12px 0 0;
        }

        .btn-block {
            width: 100%;
            padding: 10px;
            font-size: 16px;
        }

        .table td,
        .table th {
            vertical-align: middle;
            text-align: center;
        }

        .table-sm td,
        .table-sm th {
            font-size: 0.875rem;
        }

        .table-responsive-sm {
            max-width: 100%;
            overflow-x: auto;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .alert-info {
            font-size: 1rem;
            padding: 1rem;
            background: radial-gradient(circle, rgba(238, 174, 202, 1) 0%, rgba(148, 161, 233, 1) 100%);
        }

        /* Estilos para campos de entrada */
        .form-control-sm {
            font-size: 0.875rem;
        }

        /* Estilos para la alerta */
        .alert-info {
            font-size: 1rem;
        }
    </style>
@endpush

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputFields = document.querySelectorAll('.only-numbers-with-decimal');

            inputFields.forEach(input => {
                input.addEventListener('input', function() {
                    // Reemplazar la coma por punto automáticamente
                    input.value = input.value.replace(',', '.');

                    // Permitir solo números y un punto decimal
                    if (!/^[0-9]*\.?[0-9]*$/.test(input.value)) {
                        input.value = input.value.substring(0, input.value.length - 1);
                    }
                });
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
            function calcularTotales() {
                let totalVendido = 0;
                let totalQr = 0;
                let totalEfectivo = 0;
                let totalQrOficial = 0;
                let totalEfectivoOficial = 0;

                // Recorrer todas las filas de la tabla
                const rows = document.querySelectorAll('table tbody tr');

                rows.forEach(function(row) {
                    // Asegurarse de que las filas no sean de totales
                    if (row.classList.contains('table-info')) {
                        return; // Saltar filas de totales
                    }

                    // Si la fila es la de "PEDIDOS", la incluimos en el cálculo
                    const isPedidos = row.classList.contains(
                    'table-secondary'); // Especificar si es "PEDIDOS"
                    if (isPedidos) {
                        // Aquí puedes agregar directamente los valores de "PEDIDOS"
                        totalQr += parseFloat(row.cells[2].innerText.replace(',', '').replace(' ', '')) ||
                        0;
                        totalQrOficial += parseFloat(row.querySelector('input[name*="qr_oficial"]').value
                            .replace(',', '').replace(' ', '')) || 0;
                        totalEfectivoOficial += parseFloat(row.querySelector(
                            'input[name*="efectivo_oficial"]').value.replace(',', '').replace(' ',
                            '')) || 0;
                        return; // Continuar con el siguiente cálculo
                    }

                    // Obtener los valores de las celdas de cada fila
                    const vendido = parseFloat(row.cells[1].innerText.replace(',', '').replace(' ', '')) ||
                        0;
                    const qr = parseFloat(row.cells[2].innerText.replace(',', '').replace(' ', '')) || 0;
                    const efectivo = parseFloat(row.cells[3].innerText.replace(',', '').replace(' ', '')) ||
                        0;

                    // Obtener los valores de los inputs de QR y Efectivo Oficial
                    const qrOficial = parseFloat(row.querySelector('input[name*="qr_oficial"]')?.value
                        .replace(',', '').replace(' ', '')) || 0;
                    const efectivoOficial = parseFloat(row.querySelector('input[name*="efectivo_oficial"]')
                        ?.value.replace(',', '').replace(' ', '')) || 0;

                    // Acumular los totales de cada columna
                    totalVendido += vendido;
                    totalQr += qr;
                    totalEfectivo += efectivo;
                    totalQrOficial += qrOficial;
                    totalEfectivoOficial += efectivoOficial;
                });

                // Actualizar los totales en la fila correspondiente
                document.getElementById('total-vendido').innerText = totalVendido.toFixed(2);
                document.getElementById('total-qr').innerText = totalQr.toFixed(2);
                document.getElementById('total-efectivo').innerText = totalEfectivo.toFixed(2);
                document.getElementById('total-qr-oficial').innerText = totalQrOficial.toFixed(2);
                document.getElementById('total-efectivo-oficial').innerText = totalEfectivoOficial.toFixed(2);
            }
            // Calcular totales cuando se cargue la página
            calcularTotales();

            // Recargar totales cada vez que se cambie algún valor de QR o efectivo oficial
            const inputFields = document.querySelectorAll('.only-numbers-with-decimal');
            inputFields.forEach(input => {
                input.addEventListener('input', function() {
                    calcularTotales();
                });
            });
        });
    </script>
@endpush
