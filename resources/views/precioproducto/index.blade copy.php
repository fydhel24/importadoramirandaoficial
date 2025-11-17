@extends('adminlte::page')
@section('adminlte_css')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- jQuery (necesario para DataTables) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
@endsection

@section('content')
    <div class="container mt-4">
        <h1>Listado de Productos y Precios</h1>

        <table class="table table-bordered" id="productos-table">
            <thead>
                <tr>
                    <th>Nombre del Producto</th>
                    <th>Precio Jefa</th>
                    <th>Precio Unitario</th>
                    <th>Precio General</th>
                    <th>Precio Extra</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
@endsection

@section('adminlte_js')
    <script>
        // Configuración de AJAX con CSRF
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $(document).ready(function() {
            var table = $('#productos-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('precioproductos.data') }}',
                    type: 'GET',
                    dataSrc: function(json) {
                        return json.data;
                    }
                },
                columns: [{
                        data: 'nombre'
                    },
                    {
                        data: 'precio_jefa',
                        render: function(data, type, row) {
                            return '<input type="text" class="form-control precio-edit" data-id="' +
                                row.id + '" name="precio_jefa" value="' + data + '">';
                        }
                    },
                    {
                        data: 'precio_unitario',
                        render: function(data, type, row) {
                            return '<input type="text" class="form-control precio-edit" data-id="' +
                                row.id + '" name="precio_unitario" value="' + data + '">';
                        }
                    },
                    {
                        data: 'precio_general',
                        render: function(data, type, row) {
                            return '<input type="text" class="form-control precio-edit" data-id="' +
                                row.id + '" name="precio_general" value="' + data + '">';
                        }
                    },
                    {
                        data: 'precio_extra',
                        render: function(data, type, row) {
                            return '<input type="text" class="form-control precio-edit" data-id="' +
                                row.id + '" name="precio_extra" value="' + data + '">';
                        }
                    },

                ]
            });

            $(document).on('change', '.precio-edit', function() {
                var productoId = $(this).data('id');
                var nombreCampo = $(this).attr('name');
                var valor = $(this).val();
                console.log(productoId, nombreCampo, valor);

                // Obtener todos los datos del producto desde la fila actual
                var rowData = table.row($(this).closest('tr')).data();

                // Construir los datos que se enviarán, incluyendo todos los campos de precio
                var data = {
                    precio_jefa: rowData.precio_jefa,
                    precio_unitario: rowData.precio_unitario,
                    precio_general: rowData.precio_general,
                    precio_extra: rowData.precio_extra
                };

                // Actualizar el campo que cambió
                data[nombreCampo] = valor;

                // Hacer la solicitud AJAX para actualizar el precio
                $.ajax({
                    url: '/precioproductos/' + productoId + '/update',
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        if (response.success) {
                            console.log('Precio actualizado');
                        } else {
                            console.log('Error al actualizar el precio');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('Error: ', error);
                    }
                });
            });


        });
    </script>
@endsection
