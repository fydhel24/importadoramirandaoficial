<!DOCTYPE html>
<html>
<head>
    <title>Editar Semana</title>
</head>
<body>
    <h1>Editar Semana</h1>
    <form action="{{ route('semanas.update', $semana) }}" method="POST">
        @csrf
        @method('PUT')
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" value="{{ $semana->nombre }}" required>
        <label for="fecha">Fecha:</label>
        <input type="date" id="fecha" name="fecha" value="{{ $semana->fecha->format('Y-m-d') }}" required>
        <button type="submit">Actualizar</button>
    </form>
    <a href="{{ route('semanas.index') }}">Volver</a>
</body>
</html>
