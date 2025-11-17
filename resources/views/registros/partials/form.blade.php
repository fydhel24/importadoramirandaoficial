<div class="form-group">
    <label>Celular</label>
    <input type="text" name="celular" class="form-control" value="{{ old('celular', $registro->celular ?? '') }}">
</div>

<div class="form-group">
    <label>Persona</label>
    <input type="text" name="persona" class="form-control" value="{{ old('persona', $registro->persona ?? '') }}">
</div>

<div class="form-group">
    <label>Departamento</label>
    <select name="departamento" class="form-control">
        @foreach(['La Paz','Cochabamba','Santa Cruz','Oruro','Potosí','Chuquisaca','Tarija','Beni','Pando'] as $dep)
            <option value="{{ $dep }}" {{ old('departamento', $registro->departamento ?? '') == $dep ? 'selected' : '' }}>{{ $dep }}</option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label>Producto</label>
    <select name="producto_id" class="form-control">
        @foreach($productos as $producto)
            <option value="{{ $producto->id }}" {{ old('producto_id', $registro->producto_id ?? '') == $producto->id ? 'selected' : '' }}>
                {{ $producto->nombre }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label>Estado</label>
    <select name="estado" class="form-control">
        <option value="mal" {{ old('estado', $registro->estado ?? '') == 'mal' ? 'selected' : '' }}>Mal</option>
        <option value="bueno" {{ old('estado', $registro->estado ?? '') == 'bueno' ? 'selected' : '' }}>Bueno</option>
    </select>
</div>

<div class="form-group">
    <label>Descripción del Problema</label>
    <textarea name="descripcion_problema" class="form-control">{{ old('descripcion_problema', $registro->descripcion_problema ?? '') }}</textarea>
</div>

<h4>Opciones Extra</h4>
@foreach(['checkbox','de_la_paz','enviado','extra1','extra2','extra3','extra4','extra5'] as $field)
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="{{ $field }}" value="1"
               {{ old($field, $registro->$field ?? false) ? 'checked' : '' }}>
        <label class="form-check-label">{{ ucfirst(str_replace('_',' ', $field)) }}</label>
    </div>
@endforeach
