@extends('adminlte::page')

@section('title', 'Agenda Jefa')

@section('content_header')
    <h1 class="text-center">Agenda de notas Jefa </h1>
@stop

@section('content')
    <style>
        /* Estilo general para el calendario */
        #calendar {
            border-radius: 12px;
            background-color: #ffffff;
            padding: 15px;
        }

        /* Estilo para los eventos del calendario */
        .fc-event {
            border-radius: 8px !important;
            /* Bordes más suaves */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            /* Sombra más pronunciada */
            padding: 8px 15px;
            font-size: 14px;
            color: #fff;
            /* Texto blanco para mayor contraste */
            transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
        }

        .fc-event:hover {
            transform: scale(1.05);
            /* Aumento de tamaño suave al pasar el cursor */
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.25);
            /* Sombra más fuerte */
            background-color: #333;
            /* Cambio de color de fondo al pasar el cursor */
        }

        /* Estilo para las cabeceras del calendario */
        .fc-header-toolbar .fc-button {
            background: linear-gradient(to bottom, #6DD5ED, #9B59B6);
            color: #e6f0ff;
            text-transform: uppercase;
        }

        /* Efecto hover con un degradado diferente para los botones */
        .fc-header-toolbar .fc-button:hover {
            background: linear-gradient(135deg, #9b719b, #923fac);
            cursor: pointer;
        }

        /* Estilo para los días de la semana */
        .fc-day-header {
            background-color: #f4f5fa;
            color: #333;
            font-weight: bold;
            font-size: 16px;
        }

        /* Mejoras en los días del mes */
        .fc-day {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }

        .fc-day:hover {
            background-color: #e6f0ff;
            cursor: pointer;
        }

        /* Estilo para el evento de todo el día */
        .fc-day-grid-event.fc-event {
            background-color: #34c38f;
            /* Color verde claro para los eventos */
            border-color: #28a745;
            /* Borde verde más oscuro */
        }

        .fc-day-grid-event.fc-event:hover {
            background-color: #2a7d60;
            /* Cambio de color al pasar el cursor */
            border-color: #1e5e40;
        }

        /* Cambiar el estilo de los botones */
        .btn {
            border-radius: 30px;
            padding: 10px 20px;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        /* Botón Crear nota */
        .btn-success {
            background: linear-gradient(145deg, #56ab2f, #a8e063);
            border: none;
            color: white;
        }

        /* Mejoras en los formularios de los modales */
        .form-group input,
        .form-group textarea {
            border-radius: 5px;
            border: 1px solid #ddd;
            padding: 5px;
            font-size: 14px;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #17a2b8;
        }

        /* Modificación en los modales */
        .modal-content {
            border-radius: 8px;
            border: 1px solid #ddd;
        }
    </style>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <!-- Botón para abrir el modal -->
                <button type="button" class="btn btn-success" id="crearNotaBtn">
                    <i class="fas fa-plus"></i> Crear nota
                </button>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary">
                    <div class="card-body p-0">
                        <!-- Calendario de FullCalendar -->
                        <div id="calendar" class="fc fc-media-screen fc-direction-ltr fc-theme-bootstrap"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal para crear nota -->
    <div class="modal fade" id="crearNotaModal" tabindex="-1" role="dialog" aria-labelledby="crearNotaModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="crearNotaModalLabel">Crear Nota</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Formulario para crear una nota -->
                    <form id="formCrearNota">
                        <div class="form-group">
                            <label for="tituloNota">Título de la Nota</label>
                            <input type="text" class="form-control" id="tituloNota" name="titulo" required>
                        </div>
                        <div class="form-group">
                            <label for="contenidoNota">Descripción de la Nota</label>
                            <textarea class="form-control" id="contenidoNota" name="nota" rows="4" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="colorNota">Elige un color</label>
                                    <input type="color" class="form-control" id="colorNota" name="color" value="#FF5733"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fechaNota">Fecha</label>
                                    <input type="date" class="form-control" id="fechaNota" name="fecha" required>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary" id="guardarNota">Guardar Nota</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal para editar nota -->
    <div class="modal fade" id="editarNotaModal" tabindex="-1" role="dialog" aria-labelledby="editarNotaModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editarNotaModalLabel">Editar Nota</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Formulario para editar la nota -->
                    <form id="formEditarNota">
                        <div class="form-group">
                            <label for="usuarioEditarNota">Creado por</label>
                            <input type="text" class="form-control" id="usuarioEditarNota" name="usuario" readonly>
                        </div>
                        <div class="form-group">
                            <label for="tituloEditarNota">Título de la Nota</label>
                            <input type="text" class="form-control" id="tituloEditarNota" name="titulo" required>
                        </div>
                        <div class="form-group">
                            <label for="contenidoEditarNota">Descripción de la Nota</label>
                            <textarea class="form-control" id="contenidoEditarNota" name="nota" rows="4" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="colorEditarNota">Elige un color</label>
                                    <input type="color" class="form-control" id="colorEditarNota" name="color"
                                        value="#FF5733" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fechaEditarNota">Fecha</label>
                                    <input type="date" class="form-control" id="fechaEditarNota" name="fecha"
                                        required>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <!-- Botón para cerrar el modal -->
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <!-- Botón para eliminar la nota -->
                    <button type="button" class="btn btn-danger" id="eliminarNotaModal">Eliminar Nota</button>
                    <!-- Botón para guardar los cambios -->
                    <button type="submit" class="btn btn-primary" id="guardarCambiosNota">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>



@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.0/fullcalendar.min.css" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.0/fullcalendar.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.0/locale/es.js"></script> <!-- Localización en español -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            // Cargar los eventos desde la base de datos (pasados desde el controlador)
            let eventos = @json($events); // Pasamos los eventos desde PHP a JavaScript

            // Inicializa el calendario de FullCalendar
            let calendar = $('#calendar').fullCalendar({
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
                locale: 'es', // Configura el idioma del calendario a español
                defaultView: 'month', // Establece la vista por defecto a 'mes'
                editable: false, // Deshabilita la edición
                selectable: false, // Deshabilita la selección de eventos
                events: eventos,
                eventRender: function(event, element) {
                    // Asigna el color del evento al renderizarlo
                    let color = event.color;
                    element.css('background-color', color); // Cambia el color de fondo del evento
                    element.css('border-color', color); // Cambia el color del borde del evento
                },
                eventClick: function(event, jsEvent, view) {
                    // Cargar los datos del evento en el modal de edición
                    $('#tituloEditarNota').val(event.title);
                    $('#contenidoEditarNota').val(event.description);
                    $('#colorEditarNota').val(event.color);
                    $('#fechaEditarNota').val(event.start.format('YYYY-MM-DD'));

                    // Mostrar el usuario que creó la nota
                    $('#usuarioEditarNota').val(event.user);

                    // Guardar el ID del evento en un atributo del modal
                    $('#editarNotaModal').data('event-id', event.id);

                    // Mostrar el modal
                    $('#editarNotaModal').modal('show');
                }
            });
            // Mostrar el modal cuando se hace clic en "Crear nota"
            $('#crearNotaModal').on('show.bs.modal', function(event) {
                // Limpiar los campos del formulario cada vez que el modal se abre
                $('#formCrearNota')[0].reset();
            });

            // Abre el modal al hacer clic en el botón "Crear nota"
            $('#crearNotaBtn').click(function() {
                $('#crearNotaModal').modal('show');
            });

            // Lógica para guardar la nueva nota mediante AJAX
            $('#guardarNota').click(function() {
                let titulo = $('#tituloNota').val();
                let contenido = $('#contenidoNota').val();
                let fecha = $('#fechaNota').val();
                let color = $('#colorNota').val(); // Obtener el color seleccionado

                // Validar que los campos no estén vacíos
                if (titulo && contenido && fecha && color) {
                    // Enviar la solicitud AJAX para crear la nota
                    $.ajax({
                        url: '{{ route('notasjefa.store') }}', // Ruta definida en el archivo de rutas
                        type: 'POST',
                        data: {
                            titulo: titulo,
                            nota: contenido,
                            fecha: fecha,
                            color: color, // Enviar el color seleccionado
                            _token: '{{ csrf_token() }}' // Asegurarnos de incluir el token CSRF
                        },
                        success: function(response) {
                            // Mostrar un mensaje de éxito con SweetAlert2
                            Swal.fire('¡Nota Creada!', response.message, 'success');
                            location.reload();
                            // Cerrar el modal
                            $('#crearNotaModal').modal('hide');

                            // Agregar el evento al calendario (sin necesidad de recargar la página)
                            calendar.fullCalendar('renderEvent', {
                                title: titulo,
                                start: fecha,
                                allDay: true,
                                description: contenido,
                                color: response
                                    .color, // Usar el color recibido de la respuesta
                                user: response
                                    .user, // Agregar el nombre del usuario a los datos del evento
                            });
                        },
                        error: function(status) {
                            // Mostrar un mensaje de error con SweetAlert2
                            Swal.fire('Error', 'Hubo un error al guardar la nota.', 'error');
                        }
                    });
                } else {
                    // Alerta de campos vacíos con SweetAlert2
                    Swal.fire('Error', 'Por favor, completa todos los campos.', 'error');
                }
            });

            // Lógica para guardar los cambios de la nota editada mediante AJAX
            $('#guardarCambiosNota').click(function() {
                let titulo = $('#tituloEditarNota').val();
                let contenido = $('#contenidoEditarNota').val();
                let fecha = $('#fechaEditarNota').val();
                let color = $('#colorEditarNota').val();
                let eventId = $('#editarNotaModal').data('event-id'); // Obtener el ID del evento

                // Validar que los campos no estén vacíos
                if (titulo && contenido && fecha && color) {
                    // Enviar la solicitud AJAX para actualizar la nota
                    $.ajax({
                        url: '{{ route('notasjefa.update', ':id') }}'.replace(':id',
                            eventId), // Ruta de actualización
                        type: 'PUT',
                        data: {
                            titulo: titulo,
                            nota: contenido,
                            fecha: fecha,
                            color: color,
                            _token: '{{ csrf_token() }}' // Incluir el token CSRF
                        },

                        success: function(response) {
                            // Mostrar un mensaje de éxito con SweetAlert2
                            Swal.fire('¡Nota Actualizada!', response.message, 'success').then(
                                () => {
                                    // Recargar la página después de la alerta
                                    location.reload();
                                });
                        },
                        error: function(status) {
                            // Mostrar mensaje de error con SweetAlert2
                            Swal.fire('Error', 'Hubo un error al guardar los cambios.',
                                'error');
                        }
                    });
                } else {
                    // Alerta si los campos están vacíos
                    Swal.fire('Error', 'Por favor, completa todos los campos.', 'error');
                }
            });

            // Lógica para eliminar la nota
            $('#eliminarNotaModal').click(function() {
                let eventId = $('#editarNotaModal').data('event-id'); // Obtener el ID del evento

                // Confirmación de eliminación con SweetAlert2
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "¡No podrás recuperar esta nota después de eliminarla!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Enviar solicitud AJAX para eliminar la nota
                        $.ajax({
                            url: '{{ route('notasjefa.destroy', ':id') }}'.replace(':id',
                                eventId), // Ruta para eliminar la nota
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}', // Incluir token CSRF
                            },
                            success: function(response) {
                                // Mostrar mensaje de éxito con SweetAlert2
                                Swal.fire('Eliminado!', response.message, 'success');

                                // Eliminar el evento del calendario
                                calendar.fullCalendar('removeEvents', eventId);

                                // Cerrar el modal
                                $('#editarNotaModal').modal('hide');
                            },
                            error: function(status) {
                                // Mostrar mensaje de error con SweetAlert2
                                Swal.fire('Error', 'Hubo un error al eliminar la nota.',
                                    'error');
                            }
                        });
                    }
                });
            });
        });
    </script>

@stop
