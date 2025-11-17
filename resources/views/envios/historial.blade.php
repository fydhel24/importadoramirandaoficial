@extends('adminlte::page')


@section('content')
    <div class="container">
        <div class="card-header">
            <h1 class="my-4 text-center text-primary font-weight-bold">Historial de Envíos de Productos</h1>

            <!-- Filtros -->
            <div class="filters mb-4 p-4 shadow bg-light rounded">
                <form action="{{ route('envios.historial') }}" method="GET">

                    <i class="fas fa-filter"></i> Filtros de Búsqueda

                    <div class="form-row align-items-end">
                        <!-- Filtro por Rango de Fechas -->
                        <div class="form-group col-md-4">
                            <label for="fecha_inicio" class="font-weight-bold">Fecha Inicio</label>
                            <input type="date" class="form-control border-primary" name="fecha_inicio" id="fecha_inicio"
                                value="{{ request()->input('fecha_inicio') }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="fecha_fin" class="font-weight-bold">Fecha Fin</label>
                            <input type="date" class="form-control border-primary" name="fecha_fin" id="fecha_fin"
                                value="{{ request()->input('fecha_fin') }}">
                        </div>

                        <!-- Filtro por Usuario Destino -->
                        <div class="form-group col-md-4">
                            <label for="usuario_destino" class="font-weight-bold">Usuario Destino</label>
                            <select name="usuario_destino" id="usuario_destino" class="form-control border-primary">
                                <option value="">Seleccione Usuario</option>
                                @foreach ($usuariosDestino as $usuario)
                                    <option
                                        value="{{ $usuario->id }}"{{ request()->input('usuario_destino') == $usuario->id ? 'selected' : '' }}>
                                        {{ $usuario->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block font-weight-bold">Buscar</button>

                </form>
            </div>
        </div>
    </div>


    <div class="card-body">
        <i class="fas fa-table"></i> Registro de Envios

        <div class="table-responsive">
            <table id="historialTable" class="table table-bordered table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Sucursal Origen</th>
                        <th>Sucursal Destino</th>
                        <th>Usuario Origen</th>
                        <th>Usuario Destino</th>
                        <th>Fecha de Envío</th>
                        <th>Estado</th>
                        <th>Producto</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    </div>
@endsection

@section('css')
    <style>
        /* Card styles */
        .card {
            border: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
        }

        .card-header {
            background: linear-gradient(135deg, #4e73df, #df3383);


            border-radius: 15px 15px 0 0;
        }


        .card-body {
            background-color: #ffffff;
            border-radius: 0 0 15px 15px;
        }

        label {
            font-weight: bold;
            color: #000;
        }
    </style>
    <link href="https://unpkg.com/slim-select@latest/dist/slimselect.css" rel="stylesheet">
    <!-- DataTables JS & CSS (CDN) -->
    <link href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.4.0/css/responsive.dataTables.min.css" rel="stylesheet">
@endsection

@section('js')
    <!-- Inicializar DataTables -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.0/js/dataTables.responsive.min.js"></script>

    <!-- Importación de SlimSelect -->
    <script src="https://unpkg.com/slim-select@latest/dist/slimselect.min.js"></script>
    <script>
        $(document).ready(function() {
            const table = $('#historialTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('envios.historial') }}",
                    data: function(d) {
                        d.fecha_inicio = $('#fecha_inicio').val();
                        d.fecha_fin = $('#fecha_fin').val();
                        d.usuario_destino = $('#usuario_destino').val();
                    }
                },
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'sucursal_origen_nombre',
                        name: 'sucursalOrigen.nombre'
                    },
                    {
                        data: 'sucursal_destino_nombre',
                        name: 'sucursalDestino.nombre'
                    },
                    {
                        data: 'usuario_origen_name',
                        name: 'usuarioOrigen.name'
                    },
                    {
                        data: 'usuario_destino_name',
                        name: 'usuarioDestino.name'
                    },
                    {
                        data: 'fecha_envio',
                        name: 'fecha_envio'
                    },
                    {
                        data: 'estado',
                        name: 'estado',
                        orderable: false
                    },
                    {
                        data: 'productos',
                        name: 'productos',
                        orderable: false
                    },
                    {
                        data: 'acciones',
                        name: 'acciones',
                        orderable: false,
                        searchable: false
                    }
                ],
                responsive: true,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/Spanish.json"
                }
            });

            $('#filter-button').on('click', function() {
                table.ajax.reload();
            });

            $('#historialTable').on('click', '.revertir-envio', function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "Esta acción no se puede deshacer.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, revertir',
                    cancelButtonText: 'Cancelar',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/envios/revertir/${id}`,
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(data) {
                                Swal.fire('¡Revertido!', data.message, 'success');
                                table.ajax.reload();
                            },
                            error: function(error) {
                                Swal.fire('Error', error.responseJSON.message, 'error');
                            }
                        });
                    }
                });
            });

            $('#historialTable').on('click', '.generar-reporte', function() {
                const id = $(this).data('id');
                window.location.href = `/envios/generar-reporte/${id}`;
            });
        });
    </script>
@endsection
