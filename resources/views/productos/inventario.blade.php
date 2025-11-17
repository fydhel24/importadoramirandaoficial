@extends('adminlte::page')

@section('title', 'Auditoría de Productos')

@section('content_header')
    <h1 class="text-center">Productos Auditados</h1>
@stop

@section('content')
    <div class="container">
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h2 class="card-title">Listado de Auditorías</h2>
            </div>

            <div class="card-body">
                {{-- Filtro por sucursal --}}
                <div class="form-row mb-3">
                    <div class="col-md-4">
                        <label for="filtro_sucursal">Filtrar por sucursal</label>
                        <select id="filtro_sucursal" class="form-control">
                            <option value="">Todas</option>
                            @foreach ($sucursales as $sucursal)
                                <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filtro por nombre de producto --}}
                    <div class="col-md-4">
                        <label for="filtro_producto">Buscar producto</label>
                        <input type="text" id="filtro_producto" class="form-control" placeholder="Nombre del producto">
                    </div>
                </div>

                {{-- Tabla --}}
                <div class="table-responsive">
                    <table id="tabla-auditorias" class="table table-bordered table-striped table-hover table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>Fecha Auditoría</th>
                                <th>Producto</th>
                                <th>Sucursal</th>
                                <th>Stock Sistema</th>
                                <th>Stock Real</th>
                                <th>Diferencia</th>
                                <th>Estado</th>
                                <th>Auditor</th>
                                <th>Detalle</th>
                                <th>Fecha Solución</th> {{-- Nueva columna --}}
                                <th>Observación Solución</th> {{-- Nueva columna --}}
                                <th>Acciones</th>

                            </tr>
                        </thead>
                        <tbody>
                            {{-- Se llena por AJAX --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalSolucionar" tabindex="-1" role="dialog" aria-labelledby="modalSolucionarLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="formSolucionar">
            @csrf
            <input type="hidden" id="detalle_id">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalSolucionarLabel">Marcar como Solucionado</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="fecha_solucion">Fecha de Solución</label> {{-- Nuevo campo de fecha --}}
                        <input type="date" id="fecha_solucion" name="fecha_solucion" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label for="observacion_solucion">Observación de solución</label>
                        <textarea id="observacion_solucion" name="observacion_solucion" class="form-control" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Guardar solución</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('js')
    <script>
        // Función para obtener la fecha actual en formato YYYY-MM-DD
        function getCurrentDate() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        $(document).ready(function() {
            // Inicializar DataTables
            const tabla = $('#tabla-auditorias').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '{{ route('auditorias.data') }}',
                    data: function (d) {
                        d.sucursal_id = $('#filtro_sucursal').val();
                        d.producto_nombre = $('#filtro_producto').val();
                    }
                },
                columns: [
                    { data: 'fecha', name: 'fecha' },
                    { data: 'producto', name: 'producto' },
                    { data: 'sucursal', name: 'sucursal' },
                    { data: 'stock_sistema', name: 'stock_sistema', className: 'text-center' },
                    { data: 'stock_real', name: 'stock_real', className: 'text-center' },
                    { data: 'diferencia', name: 'diferencia', className: 'text-center' },
                    { data: 'estado', name: 'estado', className: 'text-center' },
                    { data: 'usuario', name: 'usuario' },
                    { data: 'comentario', name: 'comentario' },
                    { data: 'fecha_solucion', name: 'fecha_solucion', className: 'text-center' }, // Nueva columna
                    { data: 'observacion_solucion', name: 'observacion_solucion' }, // Nueva columna
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },

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
                    zeroRecords: "No se encontraron registros",
                    emptyTable: "No hay datos disponibles en la tabla",
                    paginate: {
                        first: "Primero",
                        previous: "Anterior",
                        next: "Siguiente",
                        last: "Último"
                    }
                },
                order: [[0, 'desc']] // Opcional: ordenar por fecha descendente
            });

            // Redibujar tabla al cambiar filtros
            $('#filtro_sucursal, #filtro_producto').on('change keyup', function() {
                tabla.ajax.reload();
            });

            // Abrir modal al hacer clic en "Solucionar"
            $(document).on('click', '.btn-solucionar', function () {
                const id = $(this).data('id');
                $('#detalle_id').val(id);
                $('#observacion_solucion').val('');
                // Establecer la fecha actual por defecto
                $('#fecha_solucion').val(getCurrentDate());
                $('#modalSolucionar').modal('show');
            });

            // Enviar formulario de solución
            $('#formSolucionar').on('submit', function (e) {
                e.preventDefault();

                const id = $('#detalle_id').val();
                const observacion = $('#observacion_solucion').val();
                const fecha_solucion = $('#fecha_solucion').val(); // Obtener la fecha

                $.ajax({
                    url: `/auditorias/${id}/solucionar`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        observacion_solucion: observacion,
                        fecha_solucion: fecha_solucion // Enviar la fecha
                    },
                    success: function (response) {
                        if (response.success) {
                            $('#modalSolucionar').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: '¡Solucionado!',
                                text: 'El detalle de auditoría se ha marcado como solucionado.',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            $('#tabla-auditorias').DataTable().ajax.reload();
                        }
                    },
                    error: function (xhr) {
                         const errorMessage = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error desconocido al guardar la solución';
                        alert('Error al guardar la solución: ' + errorMessage);
                    }
                });
            });
        });
    </script>
@endsection