@extends('adminlte::page')

@section('title', 'Capturas')

@section('content_header')
    <h1>Administrar Capturas</h1>
@stop

@section('content')
    <div class="card" style="border-radius: 15px;">
        <div class="card-header bg-gradient-primary text-white"
            style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
            <button id="addFotoBtn" class="btn btn-success btn-lg">
                <i class="fas fa-camera"></i> Agregar Foto
            </button>
        </div>

        <div class="card-body" style="background-color: #f8f9fa; border-radius: 15px;">
            <table class="table table-bordered table-striped" id="capturasTable">
                <thead class="bg-gradient-blue text-white">
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
            <div class="modal-content" style="border-radius: 15px;">
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
    <!-- Modal para ver la imagen en grande -->
    <div class="modal fade" id="viewImageModal" tabindex="-1" aria-labelledby="viewImageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius: 15px;">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewImageModalLabel">Vista de Foto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="text-align: center;">
                    <img id="viewImage" src="" alt="Imagen" class="img-fluid"
                        style="border-radius: 8px; transition: transform 0.3s ease;">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="zoomOutBtn">Alejar</button>
                    <button type="button" class="btn btn-secondary" id="zoomInBtn">Acercar</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Canvas para editar imagen -->
    <div id="canvas-container" style="display:none; padding: 20px;">
        <canvas id="canvas" width="800" height="600" style="border: 1px solid #ddd;"></canvas>
        <button id="saveImage" class="btn btn-success mt-3">Guardar Imagen</button>
    </div>
@stop

@section('css')
    <style>
        /* Personalización de la tabla */
        .table {
            font-size: 14px;
            border-radius: 8px;
        }

        .table th {
            background-color: #007bff;
            color: white;
            text-align: center;
        }

        .table td {
            text-align: center;
        }

        /* Estilo para los botones */
        .btn {
            border-radius: 5px;
        }

        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }

        .btn-lg {
            padding: 10px 20px;
        }

        /* Modal */
        .modal-content {
            border-radius: 15px;
        }

        .modal-header {
            background-color: #007bff;
            color: white;
        }

        /* Canvas */
        #canvas-container {
            padding: 20px;
            text-align: center;
        }

        /* Estilos del fondo de la tarjeta */
        .card {
            border-radius: 15px;
        }

        .card-header {
            background-color: #007bff;
            color: white;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }

        /* Estilo del botón de agregar foto */
        #addFotoBtn {
            font-size: 16px;
            background-color: #28a745;
            border: none;
            color: white;
            border-radius: 5px;
            padding: 10px 20px;
        }

        /* Estilos para el fondo de la tabla */
        .bg-gradient-blue {
            background: linear-gradient(to right, #00aaff, #0056b3);
        }

        .btn-close {
            background-color: transparent;
            border: none;
        }

        /* Aseguramos que las imágenes en la tabla sean más grandes */
        .table img {
            width: 100px;
            height: auto;
            cursor: pointer;
            border-radius: 8px;
        }
    </style>
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
                            var errorMessage = xhr.responseJSON && xhr.responseJSON.error ? xhr
                                .responseJSON.error :
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
                            return '<img src="/storage/' + data +
                                '" width="100" alt="Foto" class="view-photo">';
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

            var zoomLevel = 1;  // Nivel de zoom inicial (sin zoom)
            // Mostrar la imagen en el modal cuando se hace clic en ella
            // Mostrar la imagen en el modal cuando se hace clic en ella
            $(document).on('click', '.view-photo', function() {
                var imgSrc = $(this).attr('src');
                $('#viewImage').attr('src', imgSrc);
                $('#viewImageModal').modal('show');
            });

            // Función para acercar la imagen
            $('#zoomInBtn').click(function() {
                zoomLevel += 0.1; // Aumentar el nivel de zoom
                updateImageZoom();
            });

            // Función para alejar la imagen
            $('#zoomOutBtn').click(function() {
                zoomLevel = Math.max(0.1, zoomLevel -
                0.1); // Disminuir el nivel de zoom, pero no permitir que sea menor que 0.1
                updateImageZoom();
            });

            // Actualiza el zoom de la imagen
            function updateImageZoom() {
                $('#viewImage').css('transform', 'scale(' + zoomLevel + ')');
            }
        });
    </script>
@stop
