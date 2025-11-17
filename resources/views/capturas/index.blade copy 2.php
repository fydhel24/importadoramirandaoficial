@extends('adminlte::page')

@section('title', 'Capturas')

@section('content_header')
    <h1>Administrar Capturas</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <button id="addFotoBtn" class="btn btn-success">Agregar Foto</button>
        </div>

        <div class="card-body">
            <table class="table table-bordered" id="capturasTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Foto Original</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Las filas de capturas se generarán dinámicamente con DataTables -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal de agregar o modificar foto -->
    <div class="modal fade" id="fotoModal" tabindex="-1" aria-labelledby="fotoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="fotoModalLabel">Agregar Foto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="fotoForm" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="_method" value="POST">
                        <input type="hidden" id="captura_id" name="captura_id">
                        <div class="mb-3">
                            <label for="foto_original" class="form-label">Foto Original</label>
                            <input type="file" class="form-control" id="foto_original" name="foto_original" required>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitFotoBtn">Guardar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Canvas para editar imagen -->
    <div id="canvas-container" style="display:none;">
        <canvas id="canvas" width="800" height="600"></canvas>
        <button id="saveImage" class="btn btn-success">Guardar Imagen</button>
    </div>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/4.5.0/fabric.min.js"></script>
    <script>
        // Añadir token CSRF a todas las solicitudes AJAX en Laravel
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Redirigir a la vista de edición con el ID
        function editFoto(id) {
            window.location.href = '/capturas/edit/' + id;
        }

        // Función para eliminar la foto
        function deleteFoto(id) {
            // Mostrar una confirmación con SweetAlert2
            Swal.fire({
                title: '¿Seguro que deseas eliminar esta foto?',
                text: "¡No podrás revertir esta acción!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Realizamos una petición AJAX para eliminar la foto
                    $.ajax({
                        url: '/capturas/' + id, // La URL a la que se hace la solicitud
                        method: 'delete', // El método HTTP que estamos usando para eliminar el recurso
                        success: function(response) {
                            // Si la respuesta del servidor contiene un mensaje de éxito
                            if (response.success) {
                                // Mostrar mensaje de éxito con SweetAlert2
                                Swal.fire(
                                    '¡Eliminado!',
                                    response.success,
                                    'success'
                                );

                                // Recargamos la tabla de capturas después de eliminar la foto
                                $('#capturasTable').DataTable().ajax.reload();
                            } else {
                                // Mostrar error con SweetAlert2
                                Swal.fire(
                                    'Error',
                                    response.error || 'Hubo un error al eliminar la foto.',
                                    'error'
                                );
                            }
                        },
                        error: function(xhr, status, error) {
                            // Capturamos el mensaje de error del backend (si lo hay) o mostramos uno genérico
                            var errorMessage = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error :
                                'Hubo un error al eliminar la foto.';
                            
                            // Mostrar mensaje de error con SweetAlert2
                            Swal.fire(
                                'Error',
                                errorMessage,
                                'error'
                            );
                        }
                    });
                }
            });
        }

        $(document).ready(function() {
            // Inicializar DataTable con Yajra y AJAX
            var table = $('#capturasTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('capturas.index') }}',
                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'foto_original',
                        render: function(data) {
                            return '<img src="/storage/' + data + '" width="100" alt="Foto">';
                        }
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
            });

            // Abrir el modal para agregar una foto
            $('#addFotoBtn').click(function() {
                $('#fotoModalLabel').text('Agregar Foto');
                $('#fotoForm')[0].reset();
                $('#fotoForm').attr('action', '{{ route('capturas.store') }}');
                $('#submitFotoBtn').text('Agregar');
                $('#fotoModal').modal('show');
            });
        });
    </script>
@stop
