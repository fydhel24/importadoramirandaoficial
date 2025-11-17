    @extends('adminlte::page')

    @section('title', 'Capturas')

    @section('content_header')
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Administrar Capturas</h1>
            </div>
            <div class="col-sm-6">
                <a href="{{ route('carpetas.index') }}" class="btn btn-secondary float-right">
                    <i class="fas fa-arrow-left"></i> Volver a Carpetas
                </a>
            </div>
        </div>
    @stop

    @section('content')
        <div class="card" style="border-radius: 15px;">
            <div class="card-header bg-gradient-primary text-white"
                style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <button id="addFotoBtn" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#fotoModal">
                    <i class="fas fa-camera"></i> Agregar Fotos
                </button>
            </div>


            <div class="card-body" style="background-color: #f8f9fa; border-radius: 15px;">
                <table class="table table-bordered table-striped" id="capturasTable">
                    <thead class="bg-gradient-blue text-white">
                        <tr>
                            <th>ID</th>
                            <th>Foto Original</th>
                            <th>Carpeta</th>
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
        <!-- Modal para agregar o modificar foto -->
        <!-- Modal para agregar o modificar fotos -->
        <div class="modal fade" id="fotoModal" tabindex="-1" aria-labelledby="fotoModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content" style="border-radius: 15px;">
                    <div class="modal-header">
                        <h5 class="modal-title" id="fotoModalLabel">Agregar Fotos</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="fotoForm" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="_method" value="POST">
                            <input type="hidden" id="captura_id" name="captura_id">
                            <div class="mb-3">
                                <label for="carpeta_id" class="form-label">Carpeta</label>
                                <select class="form-control" id="carpeta_id" name="carpeta_id" required>
                                    <option value="">Seleccione una carpeta</option>
                                    @foreach(\App\Models\Carpeta::all() as $carpeta)
                                        <option value="{{ $carpeta->id }}">{{ $carpeta->descripcion }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="foto_original" class="form-label">Selecciona Fotos</label>
                                <!-- Permitir múltiples archivos -->
                                <input type="file" class="form-control" id="foto_original" name="foto_original[]"
                                    accept=".jpg, .jpeg, .png" multiple required>
                            </div>
                            <button type="submit" class="btn btn-primary" id="submitFotoBtn">Guardar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>


        <!-- Modal para ver la imagen en grande -->
        <!-- Modal para ver la imagen en grande -->
        <div class="modal fade" id="viewImageModal" tabindex="-1" aria-labelledby="viewImageModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content" style="border-radius: 15px;">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewImageModalLabel">Vista de Foto</h5>
                        <!-- Botón de cierre con el ícono de la "X" -->
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fas fa-times"></i> <!-- Ícono de la "X" -->
                        </button>
                    </div>
                    <div class="modal-body">
                        <img id="viewImage" src="" alt="Imagen" class="img-fluid" style="border-radius: 8px;" />
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
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

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


            /* Aseguramos que las imágenes en la tabla sean más grandes */
            .table img {
                width: 100px;
                height: auto;
                cursor: pointer;
                border-radius: 8px;
            }

            /* Estilo para hacer el modal más grande */
            #viewImageModal .modal-dialog {
                max-width: 90%;
                /* Puedes ajustar el valor para que sea más grande o más pequeño */
                width: 90%;
            }

            #viewImageModal .modal-body {
                text-align: center;
                /* Centra la imagen */
            }

            #viewImageModal .img-fluid {
                max-width: 100%;
                /* Asegura que la imagen se ajuste al ancho del modal */
                height: auto;
            }

            /* Personalizar el botón de cierre */

            .modal-header .btn-close {
                background: transparent;
                border: none;
                font-size: 1.5rem;
                color: #000;
                /* Cambiar el color de la "X" si es necesario */
            }

            .modal-header .btn-close:hover {
                color: #ff0000;
                /* Color al pasar el mouse */
                font-size: 1.7rem;
                /* Agrandar el ícono al pasar el mouse */
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
                                    '" width="400" height="auto" alt="Foto" class="view-photo" style="max-width: 100%;">';
                            }

                        },
                        {
                            data: 'carpeta.descripcion',
                            name: 'carpeta.descripcion'
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

                // Mostrar la imagen en el modal cuando se hace clic en ella
                $(document).on('click', '.view-photo', function() {
                    var imgSrc = $(this).attr('src');
                    $('#viewImage').attr('src', imgSrc);
                    $('#viewImageModal').modal('show');
                });
            });
        </script>
    @stop
