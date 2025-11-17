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
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="fotoModalLabel">Editar Foto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="canvas-container">
                        <canvas id="canvas" width="800" height="600"></canvas>
                        <div class="toolbar">
                            <button id="freeDraw"><span class="icon">🖌️</span>Dibujar libre</button>
                            <button id="drawRect"><span class="icon">⬛</span>Rectángulo</button>
                            <button id="drawCircle"><span class="icon">⚪</span>Círculo</button>
                            <button id="drawText"><span class="icon">🔤</span>Texto</button>
                            <button id="drawLine"><span class="icon">📏</span>Línea</button>
                            <button id="clearCanvas"><span class="icon">🗑️</span>Limpiar</button>
                            <button id="saveImage"><span class="icon">💾</span>Guardar</button>
                        </div>
                    </div>
                    <input type="file" id="imageLoader" accept="image/*" style="display:none;">
                </div>
                <div class="modal-footer">
                    <form id="saveForm" action="{{ route('capturas.update', 0) }}" method="POST" style="display:none;">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="foto_original" id="foto_original">
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </form>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/4.5.0/fabric.min.js"></script>
    <script>
        // Función para abrir la modal con la foto a editar
        function editFoto(id) {
            // Cambiar la URL de la acción del formulario para usar el ID correcto
            $('#saveForm').attr('action', '/capturas/' + id);

            // Mostrar la modal
            $('#fotoModal').modal('show');

            // Cargar la imagen original en el canvas
            $.ajax({
                url: '/capturas/' + id, // Obtener los datos de la foto
                method: 'GET',
                success: function(response) {
                    const canvas = new fabric.Canvas('canvas', { backgroundColor: '#ffffff' });

                    fabric.Image.fromURL('/storage/' + response.foto_original, function(img) {
                        canvas.setWidth(img.width);
                        canvas.setHeight(img.height);
                        canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas), {
                            scaleX: 1,
                            scaleY: 1
                        });
                    });

                    // Herramientas de dibujo
                    let currentTool = null;
                    let strokeColor = '#000000';

                    // Habilitar dibujo libre
                    document.getElementById('freeDraw').addEventListener('click', () => {
                        canvas.isDrawingMode = !canvas.isDrawingMode;
                        canvas.freeDrawingBrush.width = 3;
                        canvas.freeDrawingBrush.color = strokeColor;
                    });

                    // Herramientas de formas y texto
                    const tools = {
                        drawRect: 'rect',
                        drawCircle: 'circle',
                        drawText: 'text',
                        drawLine: 'line'
                    };

                    for (const id in tools) {
                        document.getElementById(id).addEventListener('click', () => {
                            currentTool = tools[id];
                            canvas.isDrawingMode = false;
                        });
                    }

                    document.getElementById('clearCanvas').addEventListener('click', () => {
                        canvas.clear();
                        fabric.Image.fromURL('/storage/' + response.foto_original, function(img) {
                            canvas.setWidth(img.width);
                            canvas.setHeight(img.height);
                            canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas), {
                                scaleX: 1,
                                scaleY: 1
                            });
                        });
                        canvas.isDrawingMode = false;
                    });

                    // Guardar imagen
                    document.getElementById('saveImage').addEventListener('click', () => {
                        const dataURL = canvas.toDataURL({ format: 'png' });
                        document.getElementById('foto_original').value = dataURL;
                        document.getElementById('saveForm').submit();
                    });

                    // Evento de dibujo
                    canvas.on('mouse:down', (e) => {
                        if (!currentTool || canvas.isDrawingMode) return;

                        const pointer = canvas.getPointer(e.e);
                        let obj;

                        switch (currentTool) {
                            case 'rect':
                                obj = new fabric.Rect({
                                    left: pointer.x,
                                    top: pointer.y,
                                    width: 100,
                                    height: 60,
                                    fill: 'transparent',
                                    stroke: strokeColor,
                                    strokeWidth: 2
                                });
                                break;
                            case 'circle':
                                obj = new fabric.Circle({
                                    left: pointer.x,
                                    top: pointer.y,
                                    radius: 30,
                                    fill: 'transparent',
                                    stroke: strokeColor,
                                    strokeWidth: 2
                                });
                                break;
                            case 'text':
                                obj = new fabric.Textbox('Texto', {
                                    left: pointer.x,
                                    top: pointer.y,
                                    fontSize: 20,
                                    fill: strokeColor
                                });
                                break;
                            case 'line':
                                obj = new fabric.Line([pointer.x, pointer.y, pointer.x + 100, pointer.y], {
                                    stroke: strokeColor,
                                    strokeWidth: 2
                                });
                                break;
                        }

                        if (obj) {
                            canvas.add(obj);
                            currentTool = null;
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
