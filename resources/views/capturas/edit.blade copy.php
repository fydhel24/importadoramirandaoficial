@extends('adminlte::page')

@section('title', 'Editar Foto')

@section('content_header')
    <h1>Editar Foto</h1>
@stop

@section('content')
    <div class="container" style="margin-top: 30px;">
        <div class="row">
            <div class="col-md-12">
                <canvas id="canvas" width="800" height="600"
                    style="border: 1px solid #ddd; border-radius: 8px;"></canvas>
                <div class="toolbar" style="margin-top: 20px; background-color: #f8f9fa; padding: 10px; border-radius: 8px;">
                    <button id="freeDraw" class="btn btn-light"><span class="icon">🖌️</span>Dibujar libre</button>
                    <button id="drawRect" class="btn btn-light"><span class="icon">⬛</span>Rectángulo</button>
                    <button id="drawCircle" class="btn btn-light"><span class="icon">⚪</span>Círculo</button>
                    <button id="drawText" class="btn btn-light"><span class="icon">🔤</span>Texto</button>
                    <button id="drawLine" class="btn btn-light"><span class="icon">📏</span>Línea</button>
                    <button id="clearCanvas" class="btn btn-light"><span class="icon">🗑️</span>Limpiar</button>
                    <button id="saveImage" class="btn btn-success"><span class="icon">💾</span>Guardar</button>
                </div>
                <input type="file" id="imageLoader" accept="image/*" style="display:none;">
            </div>
        </div>
    </div>

    <form id="saveForm" action="{{ route('capturas.update', $captura->id) }}" method="POST" style="display:none;">
        @csrf
        @method('PUT')
        <input type="hidden" name="foto_original" id="foto_original">
    </form>
@stop

@section('css')
    <style>
        /* Estilos para el contenedor de la página */
        /* Estilos para el contenedor de la página */
        .container {
            max-width: 100%;
            margin: 0 auto;
            padding: 0;
        }

        /* Estilos del canvas responsivo */
        #canvas {
            display: block;
            width: 100%;
            height: auto;
            margin: 0 auto;
            border: 1px solid #ddd;
            border-radius: 8px;
        }


        /* Estilos de la barra de herramientas */
        .toolbar {
            margin-top: 20px;
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
        }

        .toolbar button {
            border-radius: 5px;
            font-size: 16px;
            margin-right: 10px;
            padding: 10px 20px;
        }

        .toolbar button:hover {
            background-color: #007bff;
            color: white;
        }

        .btn-light {
            background-color: #f0f0f0;
            color: #333;
            border: 1px solid #ccc;
        }

        .btn-light:hover {
            background-color: #d6d6d6;
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        .icon {
            margin-right: 5px;
        }

        /* Formulario de guardar imagen oculto */
        #saveForm {
            display: none;
        }
    </style>
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/4.5.0/fabric.min.js"></script>
    <script>
        const canvas = new fabric.Canvas('canvas', {
            backgroundColor: '#ffffff',
            preserveObjectStacking: true
        });

        // Función para ajustar el canvas y la imagen a la pantalla
        function resizeCanvas() {
            const canvasContainer = document.querySelector('.container');
            const canvasWidth = canvasContainer.offsetWidth; // Obtener el ancho del contenedor
            const canvasHeight = window.innerHeight * 0.6; // Tomar un 60% de la altura de la ventana

            canvas.setWidth(canvasWidth);
            canvas.setHeight(canvasHeight);

            // Recargar la imagen y ajustarla al nuevo tamaño del canvas
            fabric.Image.fromURL('/storage/{{ $captura->foto_original }}', function(img) {
                const scaleX = canvasWidth / img.width;
                const scaleY = canvasHeight / img.height;

                // Escalar la imagen proporcionalmente
                const scale = Math.min(scaleX, scaleY); // Elegir el factor de escala más pequeño
                img.set({
                    scaleX: scale,
                    scaleY: scale,
                    left: (canvasWidth - img.width * scale) / 2, // Centrar la imagen en el canvas
                    top: (canvasHeight - img.height * scale) / 2
                });

                canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas));
            });
        }

        // Llamamos a la función de resize al cargar la página y cuando se redimensione la ventana
        window.addEventListener('load', resizeCanvas);
        window.addEventListener('resize', resizeCanvas);


        // Cargar la imagen original en el canvas
        fabric.Image.fromURL('/storage/{{ $captura->foto_original }}', function(img) {
            canvas.setWidth(img.width);
            canvas.setHeight(img.height);
            canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas), {
                scaleX: 1,
                scaleY: 1
            });
        });

        let currentTool = null;
        let strokeColor = '#000000';

        // Herramientas de dibujo
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
            fabric.Image.fromURL('/storage/{{ $captura->foto_original }}', function(img) {
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
            const dataURL = canvas.toDataURL({
                format: 'png'
            });
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
    </script>
@stop
