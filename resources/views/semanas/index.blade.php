<!DOCTYPE html>
<html>
<head>
    <title>Lista de Semanas</title>
</head>
<body>
    <h1>Lista de Semanas</h1>
    <a href="{{ route('semanas.create') }}">Crear Nueva Semana</a>
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($semanas as $semana)
                <tr>
                    <td>{{ $semana->nombre }}</td>
                    <td>{{ $semana->fecha }}</td>
                    <td>
                        <a href="{{ route('semanas.show', $semana) }}">Ver</a>
                        <a href="{{ route('semanas.edit', $semana) }}">Editar</a>
                        <form action="{{ route('semanas.destroy', $semana) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
