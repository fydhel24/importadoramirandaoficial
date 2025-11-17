<div class="row padding-1 p-1">
    <div class="col-md-12">

        <!-- Campo Nombre -->
        <div class="form-group mb-2 mb20">
            <label for="nombre" class="form-label">{{ __('Nombre') }}</label>
            <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                value="{{ old('nombre', $sucursale->nombre ?? '') }}" id="nombre" placeholder="Nombre" required>
            {!! $errors->first('nombre', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

        <!-- Campo Dirección -->
        <div class="form-group mb-2 mb20">
            <label for="direccion" class="form-label">{{ __('Dirección') }}</label>
            <input type="text" name="direccion" class="form-control @error('direccion') is-invalid @enderror"
                value="{{ old('direccion', $sucursale->direccion ?? '') }}" id="direccion" placeholder="Dirección"
                required>
            {!! $errors->first('direccion', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

        <!-- Campo Celular -->
        <!-- Campo Celular -->
        <div class="form-group mb-2 mb20">
            <label for="celular" class="form-label">{{ __('Celular') }}</label>
            <input type="text" name="celular" class="form-control @error('celular') is-invalid @enderror"
                value="{{ old('celular', $sucursale->celular ?? '') }}" id="celular" placeholder="Celular" required>
            {!! $errors->first('celular', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>


        <!-- Campo Estado -->
        <div class="form-group mb-2 mb20">
            <label for="estado" class="form-label">{{ __('Estado') }}</label>
            <select name="estado" class="form-control @error('estado') is-invalid @enderror" id="estado" required>
                <option value="activo" {{ old('estado', $sucursale->estado ?? '') == 'activo' ? 'selected' : '' }}>
                    Activo</option>
                <option value="inactivo" {{ old('estado', $sucursale->estado ?? '') == 'inactivo' ? 'selected' : '' }}>
                    Inactivo</option>
                <option value="pendiente"
                    {{ old('estado', $sucursale->estado ?? '') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
            </select>
            {!! $errors->first('estado', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

        <!-- Campo Logo -->
        <div class="form-group mb-2 mb20">
            <label for="logo" class="form-label">{{ __('Logo') }}</label>
            <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror"
                id="logo">
            {!! $errors->first('logo', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}

            @if (isset($sucursale) && $sucursale->logo)
                <div class="mt-3">
                    <label>Logo Actual:</label>
                    <img src="{{ asset('storage/' . $sucursale->logo) }}" alt="Logo" class="img-fluid"
                        style="max-width: 100px; height: auto;">
                </div>
            @endif
        </div>

    </div>

    <!-- Botón de Enviar -->
    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Guardar') }}</button>
    </div>
</div>
