<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Juego Amigable</title>
    <link rel="stylesheet" href="{{ asset('amigable/css/app.css') }}">
    <script>
        function nextStep(step) {
            const currentStep = document.getElementById(`step${step}`);
            currentStep.classList.add('hidden');

            const nextStep = document.getElementById(`step${step + 1}`);
            fadeIn(nextStep);
        }

        function showSummary() {
            const name = document.getElementById('name').value;
            const ci = document.getElementById('ci').value;
            const celular = document.getElementById('celular').value;
            const destino = document.getElementById('destino').value;
            const direccion = document.getElementById('direccion').value;
            const estado = document.getElementById('estado').value;
            const cantidad_productos = document.getElementById('cantidad_productos').value;
            const detalle = document.getElementById('detalle').value;
            const productos = document.getElementById('productos').value;
            const monto_deposito = document.getElementById('monto_deposito').value;
            const monto_enviado_pagado = document.getElementById('monto_enviado_pagado').value;

            document.getElementById('step11').classList.add('hidden');
            const resultDiv = document.getElementById('result');
            fadeIn(resultDiv);

            const greeting = `¡Felicidades, ${name}!`;
            document.getElementById('greeting').innerText = greeting;

            const summary = `
                Nombre: ${name}<br>
                CI: ${ci}<br>
                Celular: ${celular}<br>
                Destino: ${destino}<br>
                Dirección: ${direccion}<br>
                Estado: ${estado}<br>
                Cantidad de productos: ${cantidad_productos}<br>
                Detalle: ${detalle}<br>
                Productos: ${productos}<br>
                Monto de depósito: ${monto_deposito}<br>
                Monto enviado/pagado: ${monto_enviado_pagado}
            `;
            document.getElementById('summary').innerHTML = summary;

            const submitButton = document.querySelector('button[type="submit"]');
            submitButton.disabled = false;
        }

        function fadeIn(element) {
            element.classList.remove('hidden');
            element.style.opacity = 0;
            setTimeout(() => {
                element.style.transition = 'opacity 0.5s ease';
                element.style.opacity = 1;
            }, 10);
        }
    </script>
</head>
<body>
    <div class="container visible" id="gameContainer">
        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form id="gameForm" action="{{ route('cliente.store') }}" method="POST">
            @csrf <!-- Protección CSRF -->

            <div id="step1">
                <h1 for="name">Por favor, ingresa tu nombre:</h1>
                <input type="text" id="name" name="nombre" value="Juan Pérez" required>
                <button type="button" onclick="nextStep(1)">Siguiente</button>
            </div>

            <div id="step2" class="hidden">
                <h1 for="ci">Ahora, ingresa tu CI:</h1>
                <input type="text" id="ci" name="ci" value="12345678" required>
                <button type="button" onclick="nextStep(2)">Siguiente</button>
            </div>

            <div id="step3" class="hidden">
                <h1 for="celular">Ingresa tu celular:</h1>
                <input type="text" id="celular" name="celular" value="987654321" required>
                <button type="button" onclick="nextStep(3)">Siguiente</button>
            </div>

            <div id="step4" class="hidden">
                <h1 for="destino">Ingresa tu destino:</h1>
                <input type="text" id="destino" name="destino" value="Calle Falsa 123" required>
                <button type="button" onclick="nextStep(4)">Siguiente</button>
            </div>

            <div id="step5" class="hidden">
                <h1 for="direccion">Ingresa tu dirección:</h1>
                <input type="text" id="direccion" name="direccion" value="Calle Falsa 123" required>
                <button type="button" onclick="nextStep(5)">Siguiente</button>
            </div>

            <div id="step6" class="hidden">
                <h1 for="estado">Ingresa tu estado:</h1>
                <input type="text" id="estado" name="estado" value="Activo" required>
                <button type="button" onclick="nextStep(6)">Siguiente</button>
            </div>

            <div id="step7" class="hidden">
                <h1 for="cantidad_productos">Cantidad de productos:</h1>
                <input type="number" id="cantidad_productos" name="cantidad_productos" value="3" required>
                <button type="button" onclick="nextStep(7)">Siguiente</button>
            </div>

            <div id="step8" class="hidden">
                <h1 for="detalle">Detalles (opcional):</h1>
                <input type="text" id="detalle" name="detalle" value="Ninguno">
                <button type="button" onclick="nextStep(8)">Siguiente</button>
            </div>

            <div id="step9" class="hidden">
                <h1 for="productos">Productos (opcional):</h1>
                <input type="text" id="productos" name="productos" value="Producto A, Producto B">
                <button type="button" onclick="nextStep(9)">Siguiente</button>
            </div>

            <div id="step10" class="hidden">
                <h1 for="monto_deposito">Monto de depósito:</h1>
                <input type="number" id="monto_deposito" name="monto_deposito" value="100.00" required>
                <button type="button" onclick="nextStep(10)">Siguiente</button>
            </div>

            <div id="step11" class="hidden">
                <h1 for="monto_enviado_pagado">Monto enviado/pagado:</h1>
                <input type="number" id="monto_enviado_pagado" name="monto_enviado_pagado" value="100.00" required>
                <button type="button" onclick="showSummary()">Finalizar</button>
            </div>

            <div id="result" class="hidden">
                <h2 id="greeting"></h2>
                <p id="summary"></p>
                <button type="submit" disabled>Enviar</button>
            </div>
        </form>
    </div>
</body>
</html>
