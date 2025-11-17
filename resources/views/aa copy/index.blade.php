<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Juego Amigable</title>
    <link rel="stylesheet" href="{{ asset('amigable/css/app.css') }}">
    <script src="{{ asset('amigable/js/app.js') }}" defer></script>
</head>
<body>
    <div class="container visible" id="gameContainer">
        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo">
        <h1>Bienvenido al Juego Amigable</h1>

        <div id="step1">
            <label for="name">Por favor, ingresa tu nombre:</label>
            <input type="text" id="name" required>
            <button onclick="nextStep(1)">Siguiente</button>
        </div>

        <div id="step2" class="hidden">
            <label for="ci">Ahora, ingresa tu CI:</label>
            <input type="text" id="ci" required>
            <button onclick="nextStep(2)">Siguiente</button>
        </div>

        <div id="step3" class="hidden">
            <label for="destination">Finalmente, ingresa tu destino:</label>
            <input type="text" id="destination" required>
            <button onclick="finishGame()">Finalizar</button>
        </div>

        <div id="result" class="hidden">
            <h2 id="greeting"></h2>
            <p id="summary"></p>
        </div>
    </div>
</body>
</html>
