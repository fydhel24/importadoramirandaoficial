<!DOCTYPE html>
<html>
<head>
    <title>Crear Semana</title>
</head>
<body>
    <h1>Crear Nueva Semana</h1>
    <form action="{{ route('semanas.store') }}" method="POST">
        @csrf
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" required>
        <label for="fecha">Fecha:</label>
        <input type="date" id="fecha" name="fecha" required>
        <button type="submit">Guardar</button>
    </form>
    <a href="{{ route('semanas.index') }}">Volver</a>
</body>
</html>
