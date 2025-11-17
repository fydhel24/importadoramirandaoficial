@extends('adminlte::page')

@section('title', 'Lista de recojos')

@section('content_header')
    <h1 class="text-center">Lista de recojos</h1>
@stop

@section('content')
    <div class="container">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>CI</th>
                    <th>Costo Total</th>
                    <th>Descuento</th>
                    <th>Tipo de Pago</th>
                    <th>Estado</th>
                    <th>Sucursal</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ventas as $venta)
                    <tr>
                        <td>{{ $venta->id }}</td>
                        <td>{{ $venta->fecha }}</td>
                        <td>{{ $venta->nombre_cliente }}</td>
                        <td>{{ $venta->ci }}</td>
                        <td>{{ $venta->costo_total }}</td>
                        <td>{{ $venta->descuento }}</td>
                        <td>{{ $venta->tipo_pago }}</td>
                        <td>{{ $venta->estado }}</td>
                        <td>{{ $venta->sucursal->nombre ?? 'N/A' }}</td>
                        <td>
                            <!-- Botón que abre el modal -->
                            <button type="button" class="btn btn-sm btn-primary" data-toggle="modal"
                                data-target="#modalEditar{{ $venta->id }}">
                                EDITAR
                            </button>

                            <!-- Modal -->
                            <div class="modal fade" id="modalEditar{{ $venta->id }}" tabindex="-1" role="dialog"
                                aria-labelledby="modalLabel{{ $venta->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-xl" role="document">
                                    <form action="{{ route('ventas.update', $venta->id) }}" method="POST" target="_blank">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="modalLabel{{ $venta->id }}">Editar Venta
                                                    #{{ $venta->id }}</h5>
                                                <button type="button" class="close" data-dismiss="modal"
                                                    aria-label="Cerrar">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">

                                                <div class="form-row">
                                                    <div class="form-group col-md-4">
                                                        <label for="nombre_cliente{{ $venta->id }}">Cliente</label>
                                                        <input type="text" name="nombre_cliente" class="form-control"
                                                            id="nombre_cliente{{ $venta->id }}"
                                                            value="{{ $venta->nombre_cliente }}">
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                        <label for="ci{{ $venta->id }}">CI</label>
                                                        <input type="text" name="ci" class="form-control"
                                                            id="ci{{ $venta->id }}" value="{{ $venta->ci }}">
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                        <label for="costo_total{{ $venta->id }}">Costo Total</label>
                                                        <input type="number" step="0.01" name="costo_total"
                                                            class="form-control" id="costo_total{{ $venta->id }}"
                                                            value="{{ $venta->costo_total }}" readonly>

                                                    </div>
                                                </div>

                                                <div class="form-row">
                                                    <div class="form-group col-md-12">
                                                        <label>Tipo de Pago</label>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input tipo-pago-radio" type="radio"
                                                                name="tipo_pago" id="efectivo{{ $venta->id }}"
                                                                value="Efectivo"
                                                                {{ $venta->tipo_pago == 'Efectivo' ? 'checked' : '' }}>
                                                            <label class="form-check-label"
                                                                for="efectivo{{ $venta->id }}">Efectivo</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input tipo-pago-radio" type="radio"
                                                                name="tipo_pago" id="qr{{ $venta->id }}" value="QR"
                                                                {{ $venta->tipo_pago == 'QR' ? 'checked' : '' }}>
                                                            <label class="form-check-label"
                                                                for="qr{{ $venta->id }}">QR</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input tipo-pago-radio" type="radio"
                                                                name="tipo_pago" id="ambos{{ $venta->id }}"
                                                                value="Efectivo y QR"
                                                                {{ $venta->tipo_pago == 'Efectivo y QR' ? 'checked' : '' }}>
                                                            <label class="form-check-label"
                                                                for="ambos{{ $venta->id }}">Efectivo y QR</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-row">
                                                    <div class="form-group col-md-4">
                                                        <label for="efectivo{{ $venta->id }}">Efectivo</label>
                                                        <input type="number" step="0.01" name="efectivo"
                                                            class="form-control" id="efectivo{{ $venta->id }}"
                                                            value="{{ $venta->efectivo }}">
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                        <label for="qr{{ $venta->id }}">QR</label>
                                                        <input type="number" step="0.01" name="qr"
                                                            class="form-control" id="qr{{ $venta->id }}"
                                                            value="{{ $venta->qr }}">
                                                    </div>
                                                    <div class="form-group col-md-2">
                                                        <label for="pagado{{ $venta->id }}">Pagado</label>
                                                        <input type="number" step="0.01" name="pagado"
                                                            class="form-control" id="pagado{{ $venta->id }}"
                                                            value="{{ $venta->pagado }}" readonly>
                                                    </div>
                                                    <div class="form-group col-md-2">
                                                        <label for="cambio{{ $venta->id }}">Cambio</label>
                                                        <input type="number" step="0.01" class="form-control"
                                                            id="cambio{{ $venta->id }}" readonly>
                                                    </div>
                                                </div>

                                                <ul class="mt-2">
                                                    @foreach ($venta->ventaProductos as $vp)
                                                        <li>
                                                            {{ $vp->producto->nombre ?? 'Producto eliminado' }} -
                                                            Cantidad: {{ $vp->cantidad }},
                                                            Precio Unitario: {{ $vp->precio_unitario }},
                                                            Descuento: {{ $vp->descuento }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-success">Guardar Cambios</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>



@endsection


@push('js')
    <script>
        $(document).ready(function() {
            $('.modal').on('shown.bs.modal', function() {
                $(this).find('input:first').focus();
            });
        });
    </script>
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <script>
        $(document).ready(function() {
            $('.modal').on('shown.bs.modal', function() {
                $(this).find('input:first').focus();
                togglePagoFields($(this));
            });

            $('.tipo-pago-radio').on('change', function() {
                const modal = $(this).closest('.modal');
                togglePagoFields(modal);
            });

            function togglePagoFields(modal) {
                const tipoPago = modal.find('.tipo-pago-radio:checked').val();
                const efectivoInput = modal.find('input[name="efectivo"]');
                const qrInput = modal.find('input[name="qr"]');
                const costoTotal = parseFloat(modal.find('input[name="costo_total"]').val());
                const cambioInput = modal.find(`#cambio${modal.attr('id').replace('modalEditar', '')}`);

                if (tipoPago === 'Efectivo') {
                    efectivoInput.prop('disabled', false);
                    qrInput.prop('disabled', true).val(0);
                } else if (tipoPago === 'QR') {
                    efectivoInput.prop('disabled', true).val(0);
                    qrInput.prop('disabled', false);
                } else if (tipoPago === 'Efectivo y QR') {
                    efectivoInput.prop('disabled', false);
                    qrInput.prop('disabled', false);
                }

                // Calcular cambio al editar cualquier campo
                function calcularCambio() {
                    const efectivo = parseFloat(efectivoInput.val()) || 0;
                    const qr = parseFloat(qrInput.val()) || 0;
                    const pagadoInput = modal.find('input[name="pagado"]');
                    const pagado = efectivo + qr;
                    const cambio = pagado > costoTotal ? (pagado - costoTotal).toFixed(2) : 0;

                    pagadoInput.val(pagado.toFixed(2));
                    cambioInput.val(cambio);
                }


                efectivoInput.on('input', calcularCambio);
                qrInput.on('input', calcularCambio);

                calcularCambio();
            }

        });
    </script>


@endpush
