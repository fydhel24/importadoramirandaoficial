@extends('adminlte::page')

@section('title', 'Editar Rol')

@section('content_header')
    <h1 class="text-center font-weight-bold">Editar Rol</h1>
@stop

@section('content')
    <div class="d-flex justify-content-between mb-4">
        <a class="btn btn-danger" href="{{ route('roles.index') }}">
            <i class="fas fa-arrow-left"></i> Volver Atrás
        </a>
    </div>

    <form action="{{ route('roles.update', $role) }}" method="POST" class="bg-light p-4 rounded shadow-sm">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name" class="font-italic"><strong>Nombre de Rol:</strong></label>
            <input type="text" class="form-control" name="name" value="{{ $role->name }}" required placeholder="Introduzca el nombre del rol">
        </div>

        <h3 class="mt-4">Seleccionar Permisos:</h3>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="select-all" aria-label="Seleccionar todos los permisos">
            <label class="form-check-label" for="select-all"><strong>Seleccionar / Deseleccionar todos</strong></label>
        </div>

        <div class="row mt-2">
            @foreach ($groupedPermissions as $group => $permissions)
                <div class="col-md-3 mb-4">
                    <div class="card">
                        <div class="card-header bg-info text-white text-center">
                            <h5 class="font-weight-bold">{{ $group }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-2">
                                <input class="form-check-input group-checkbox" type="checkbox" id="select-group-{{ $group }}" aria-label="Seleccionar grupo de permisos">
                                <label class="form-check-label" for="select-group-{{ $group }}"><strong>Seleccionar / Deseleccionar este grupo</strong></label>
                            </div>
                            <div class="form-group">
                                @foreach ($permissions as $permission)
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox" type="checkbox" name="permissions[]" value="{{ $permission->name }}" {{ $role->hasPermissionTo($permission) ? 'checked' : '' }} id="permission_{{ $permission->id }}">
                                        <label class="form-check-label" for="permission_{{ $permission->id }}">
                                            <span style="font-family: 'Courier New', Courier, monospace;">{{ $permission->descripcion }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <button type="submit" class="btn btn-success btn-lg btn-block mt-3">
            <i class="fas fa-save"></i> Actualizar
        </button>
    </form>

    <script>
        document.getElementById('select-all').addEventListener('click', function(event) {
            let isChecked = event.target.checked;
            let checkboxes = document.querySelectorAll('.permission-checkbox');
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = isChecked;
            });
        });

        document.querySelectorAll('.group-checkbox').forEach(groupCheckbox => {
            groupCheckbox.addEventListener('click', function() {
                let groupPermissions = this.closest('.col-md-3').querySelectorAll('.permission-checkbox');
                groupPermissions.forEach(checkbox => {
                    checkbox.checked = groupCheckbox.checked;
                });
            });
        });
    </script>
@endsection
