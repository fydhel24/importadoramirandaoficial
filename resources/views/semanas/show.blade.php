<!DOCTYPE html>
<html>
<head>
    <title>Ver Semana</title>
</head>
<body>
    <h1>Detalles de la Semana</h1>
    <p><strong>Nombre:</strong> {{ $semana->nombre }}</p>
    <p><strong>Fecha:</strong> {{ $semana->fecha }}</p>
    <a href="{{ route('semanas.index') }}">Volver</a>
</body>
</html>
