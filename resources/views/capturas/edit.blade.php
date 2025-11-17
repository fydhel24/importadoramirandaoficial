@extends('adminlte::page')

@section('title', 'Editar Foto')

@section('content_header')
    <h1>Editar Foto</h1>
@stop

@section('content')
    <div class="container" style="margin-top: 30px; overflow: auto;">
        <div class="row">
            <div class="col-md-12">
                <canvas id="canvas" style="border: 1px solid #ddd; border-radius: 8px;"></canvas>
                <input type="file" id="imageLoader" accept="image/*" style="display:none;">
            </div>
        </div>
    </div>

    <div class="toolbar" style="background-color: #f8f9fa; padding: 10px; border-radius: 8px;">
        <button id="freeDraw" class="btn btn-light"><span class="icon">🖌️</span>Dibujar libre</button>
        <button id="drawRect" class="btn btn-light"><span class="icon">⬛</span>Rectángulo</button>
        <button id="drawCircle" class="btn btn-light"><span class="icon">⚪</span>Círculo</button>
        <button id="drawText" class="btn btn-light"><span class="icon">🔤</span>Texto</button>
        <button id="drawLine" class="btn btn-light"><span class="icon">📏</span>Línea</button>
        <button id="clearCanvas" class="btn btn-light"><span class="icon">🗑️</span>Limpiar</button>
        <button id="saveImage" class="btn btn-success"><span class="icon">💾</span>Guardar</button>

        <!-- Colores -->
        <button class="btn color-btn" id="colorGreen" style="background-color: #39FF14; border: none;"></button>
        <button class="btn color-btn" id="colorYellow" style="background-color: #FFFF00; border: none;"></button>
        <button class="btn color-btn" id="colorPink" style="background-color: #FF1493; border: none;"></button>
        <button class="btn color-btn" id="colorBlue" style="background-color: #00FFFF; border: none;"></button>
    </div>

    <form id="saveForm" action="{{ route('capturas.update', $captura->id) }}" method="POST" style="display:none;">
        @csrf
        @method('PUT')
        <input type="hidden" name="foto_original" id="foto_original">
    </form>
@stop

@section('css')
    <style>
        .toolbar {
            position: fixed;
            top: 100px;  /* Ajusta según sea necesario para la distancia desde el tope de la página */
            right: 20px;
            z-index: 1000;
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .toolbar button {
            border-radius: 5px;
            font-size: 16px;
            margin-bottom: 10px;
            padding: 10px 20px;
            width: 150px;
            text-align: left;
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

        #saveForm {
            display: none;
        }

        .color-btn {
            width: 30px;
            height: 30px;
            margin-bottom: 10px;
            cursor: pointer;
            border-radius: 50%;
            border: 1px solid #ccc;
        }

        .color-btn:hover {
            border: 1px solid #007bff;
        }

        .color-btn:active {
            border: 2px solid #007bff;
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

        let strokeColor = '#000000';

        // Cambiar color
        document.getElementById('colorGreen').onclick = () => strokeColor = '#39FF14';
        document.getElementById('colorYellow').onclick = () => strokeColor = '#FFFF00';
        document.getElementById('colorPink').onclick = () => strokeColor = '#FF1493';
        document.getElementById('colorBlue').onclick = () => strokeColor = '#00FFFF';

        // Cargar imagen escalada al ancho del contenedor
        function loadScaledImage() {
            fabric.Image.fromURL('/storage/{{ $captura->foto_original }}', function(img) {
                const containerWidth = document.querySelector('.col-md-12').clientWidth;
                const scale = containerWidth / img.width;

                img.scale(scale);
                canvas.setWidth(img.width * scale);
                canvas.setHeight(img.height * scale);

                canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas));
            });
        }

        // Cargar imagen al inicio
        window.addEventListener('load', loadScaledImage);
        // Si quieres que se adapte al cambiar tamaño de ventana, descomenta:
        // window.addEventListener('resize', loadScaledImage);

        let currentTool = null;

        document.getElementById('freeDraw').addEventListener('click', () => {
            canvas.isDrawingMode = !canvas.isDrawingMode;
            canvas.freeDrawingBrush.width = 3;
            canvas.freeDrawingBrush.color = strokeColor;
        });

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
            loadScaledImage();
            canvas.isDrawingMode = false;
        });

        document.getElementById('saveImage').addEventListener('click', () => {
            const dataURL = canvas.toDataURL({ format: 'png' });
            document.getElementById('foto_original').value = dataURL;
            document.getElementById('saveForm').submit();
        });

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
