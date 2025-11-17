@extends('adminlte::page')

@section('title', 'Planilla de Pagos por Mes')

@section('content')
    <div class="container mt-4">
        <h1 class="mb-4">Planilla de Pagos - Año {{ date('Y') }}</h1>

        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('pagos.generateAllPdf') }}" class="btn btn-danger" target="_blank">
                Generar PDF de Planilla Completa 📄
            </a>
        </div>


        <table id="pagos-table" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Usuario</th>
                    @for ($i = 1; $i <= 12; $i++)
                        <th>{{ strtoupper(\Carbon\Carbon::create()->month($i)->format('M')) }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @php
                    $mesActual = (int) date('n');
                    $anioActual = (int) date('Y');
                @endphp

                @foreach ($usuarios as $usuario)
                    <tr>
                        <td>{{ $usuario->name }}</td>

                        @for ($mes = 1; $mes <= 12; $mes++)
                            @php
                                $clave = $usuario->id . '-' . str_pad($mes, 2, '0', STR_PAD_LEFT);
                                $pagoUser = $pagos->get($clave)?->first();

                                $ultimoPago = $pagos
                                    ->filter(function ($value, $key) use ($usuario, $mes) {
                                        [$uid, $m] = explode('-', $key);
                                        return $uid == $usuario->id && (int) $m < $mes;
                                    })
                                    ->sortByDesc(function ($value, $key) {
                                        return explode('-', $key)[1];
                                    })
                                    ->first()
                                    ?->first();

                                $pagoMesActual = $pagos
                                    ->get($usuario->id . '-' . str_pad($mesActual, 2, '0', STR_PAD_LEFT))
                                    ?->first();

                                $anioCreacion = (int) $usuario->created_at->format('Y');
                                $mesCreacion = (int) $usuario->created_at->format('n');

                                $mostrarBoton = false;

                                if (
                                    $anioCreacion < $anioActual ||
                                    ($anioCreacion == $anioActual && $mes >= $mesCreacion)
                                ) {
                                    if ($mes <= $mesActual && !$pagoUser) {
                                        $mostrarBoton = true;
                                    } elseif ($mes == $mesActual + 1 && $pagoMesActual) {
                                        $mostrarBoton = true;
                                    }
                                }
                            @endphp

                            <td>
                                @if ($pagoUser)
                                    <div><strong>Bs/ {{ number_format($pagoUser->pagoEmpleado->monto, 2) }}</strong></div>
                                    <div><small>{{ ucfirst($pagoUser->estado) }}</small></div>
                                    <button class="btn btn-sm btn-outline-info mt-1" data-toggle="modal"
                                        data-target="#modalVerPago" data-monto="{{ $pagoUser->pagoEmpleado->monto }}"
                                        data-bono="{{ $pagoUser->pagoEmpleado->bono_extra }}"
                                        data-descuento="{{ $pagoUser->pagoEmpleado->descuento }}"
                                        data-total="{{ $pagoUser->pagoEmpleado->total }}"
                                        data-estado="{{ $pagoUser->estado }}" data-fecha="{{ $pagoUser->fecha_pago }}"
                                        data-descripcion="{{ $pagoUser->pagoEmpleado->descripcion }}">
                                        Ver
                                    </button>
                                    <a href="{{ route('pagos.generatePdf', ['user' => $usuario->id, 'mes' => $mes]) }}"
                                        class="btn btn-sm btn-outline-danger mt-1" target="_blank">PDF</a>
                                @elseif ($mostrarBoton)
                                    <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#modalPago"
                                        data-user="{{ $usuario->id }}" data-mes="{{ $mes }}"
                                        data-monto="{{ $ultimoPago?->pagoEmpleado?->monto ?? '' }}"
                                        data-bono="{{ $ultimoPago?->pagoEmpleado?->bono_extra ?? '' }}"
                                        data-descuento="{{ $ultimoPago?->pagoEmpleado?->descuento ?? '' }}">
                                        Pagar
                                    </button>
                                @else
                                    <span class="text-muted"></span>
                                @endif
                            </td>
                        @endfor
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="modal fade" id="modalPago" tabindex="-1" role="dialog" aria-labelledby="modalPagoLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form method="POST" action="{{ route('pagos.realizar') }}">
                    @csrf
                    <input type="hidden" name="user_id" id="modalUserId">
                    <input type="hidden" name="mes" id="modalMes">

                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Registrar Pago</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <div class="form-group">
                                <label for="fecha_pago">Fecha de Pago</label>
                                <input type="date" name="fecha_pago" class="form-control"
                                    value="{{ now()->format('Y-m-d') }}" required>
                            </div>

                            <div class="form-group">
                                <label>Monto</label>
                                <input type="number" step="0.01" name="monto" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Bono Extra</label>
                                <input type="number" step="0.01" name="bono_extra" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Descuento</label>
                                <input type="number" step="0.01" name="descuento" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Descripción</label>
                                <textarea name="descripcion" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Total Calculado</label>
                                <input type="text" id="totalCalculado" class="form-control bg-light" readonly>
                            </div>

                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Registrar Pago</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal fade" id="modalVerPago" tabindex="-1" role="dialog" aria-labelledby="modalVerPagoLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Detalle del Pago</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <p><strong>Monto:</strong> Bs/ <span id="verMonto"></span></p>
                        <p><strong>Bono Extra:</strong> Bs/ <span id="verBono"></span></p>
                        <p><strong>Descuento:</strong> Bs/ <span id="verDescuento"></span></p>
                        <p><strong>Total:</strong> Bs/ <span id="verTotal"></span></p>
                        <p><strong>Estado:</strong> <span id="verEstado"></span></p>
                        <p><strong>Fecha de Pago:</strong> <span id="verFecha"></span></p>
                        <p><strong>Descripción:</strong> <span id="verDescripcion"></span></p>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>

                </div>
            </div>
        </div>

    </div>

@section('js')
    <script>
        $(document).ready(function() {
            $('#pagos-table').DataTable({
                "paging": true,
                "lengthChange": false,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json"
                },
                "columnDefs": [{
                    "orderable": false,
                    "targets": '_all' // Deshabilita la ordenación para todas las columnas 
                }]
            });
        });
    </script>
    <script>
        function calcularTotal() {
            let monto = parseFloat($('input[name="monto"]').val()) || 0;
            let bono = parseFloat($('input[name="bono_extra"]').val()) || 0;
            let descuento = parseFloat($('input[name="descuento"]').val()) || 0;
            let total = monto + bono - descuento;

            $('#totalCalculado').val('Bs/ ' + total.toFixed(2));
        }

        $('#modalPago').on('show.bs.modal', function(event) {
            let button = $(event.relatedTarget);

            let userId = button.data('user');
            let mes = button.data('mes');
            let monto = button.data('monto');

            // Convertir mes numérico a nombre
            let nombreMes = new Date(0, mes - 1).toLocaleString('es-ES', {
                month: 'long'
            }).toUpperCase();

            // Rellenar campos
            $('#modalUserId').val(userId);
            $('#modalMes').val(mes);
            $('input[name="fecha_pago"]').val('{{ now()->format('Y-m-d') }}');
            $('input[name="monto"]').val(monto ?? '');
            $('textarea[name="descripcion"]').val('POR PAGO DE ' + nombreMes);

            // Calcular total inicial
            setTimeout(calcularTotal, 100);
        });

        // Escuchar cambios para recalcular
        $('input[name="monto"], input[name="bono_extra"], input[name="descuento"]').on('input', calcularTotal);
    </script>
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: '¡Pago exitoso!',
                text: '{{ session('success') }}',
                confirmButtonText: 'OK'
            });
        </script>
    @endif

    <script>
        $('#modalVerPago').on('show.bs.modal', function(event) {
            let button = $(event.relatedTarget);

            $('#verMonto').text(parseFloat(button.data('monto')).toFixed(2));
            $('#verBono').text(parseFloat(button.data('bono')).toFixed(2));
            $('#verDescuento').text(parseFloat(button.data('descuento')).toFixed(2));
            $('#verTotal').text(parseFloat(button.data('total')).toFixed(2));
            $('#verEstado').text(button.data('estado'));
            $('#verFecha').text(button.data('fecha'));
            $('#verDescripcion').text(button.data('descripcion'));
        });
    </script>

@endsection

@endsection
