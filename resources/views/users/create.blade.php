@extends('adminlte::page')

@section('title', 'Crear Usuario')

@section('content_header')
    <h1>Crear Usuarioooo</h1>
@stop

@section('content')
    <div class="container d-flex justify-content-center">
        <div class="col-md-6">
            <!-- Formulario con estilo personalizado -->
            <form action="{{ route('users.store') }}" method="POST" class="bg-light p-4 rounded shadow-sm">
                @csrf

                <!-- Campo para el nombre -->
                <div class="form-group">
                    <label for="name">Nombre</label>
                    <input type="text" name="name" class="form-control" placeholder="Ingrese el nombre" required>
                </div>

                <!-- Campo para el email -->
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="Ingrese el email" required>
                </div>

                <!-- Campo para la contraseña -->
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" name="password" class="form-control" placeholder="Ingrese la contraseña"
                        required>
                </div>

                <!-- Campo para seleccionar el rol -->
                <div class="form-group">
                    <label for="role">Rol</label>
                    <select name="role" class="form-control" required>
                        <option value="">Selecciona un rol</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Campo para seleccionar una sucursal -->
                <div class="form-group">
                    <label for="sucursal">Sucursal</label>
                    <select name="sucursal" class="form-control" required>
                        <option value="">Selecciona una sucursal</option>
                        @foreach ($sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Campo para el estado -->
                <div class="form-group">
                    <label for="status">Estado</label>
                    <div>
                        <input type="radio" name="status" value="active" required checked> Activo
                        <input type="radio" name="status" value="inactive" required> Inactivo
                    </div>
                </div>


                <!-- Botones de acción -->
                <div class="d-flex justify-content-between mt-4">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-plus"></i> Crear
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Cancelar
                    </a>
                </div>
            </form>

            <!-- Mostrar los errores en caso de que existan -->
            @if ($errors->any())
                <div class="alert alert-danger mt-3">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
@stop
