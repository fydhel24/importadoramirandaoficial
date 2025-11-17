<div class="row padding-1 p-1">
    <div class="col-md-12">

        <!-- Codigo -->
        <div class="form-group mb-2 mb20">
            <label for="codigo" class="form-label">{{ __('Codigo') }}</label>
            <div class="input-group">
                <input type="text" name="codigo" class="form-control @error('codigo') is-invalid @enderror" value="{{ old('codigo', $cupo?->codigo) }}" id="codigo" placeholder="Codigo">
                <div class="input-group-append">
                    <button type="button" id="generate-code" class="btn btn-info">Generar Código</button>
                </div>
            </div>
            {!! $errors->first('codigo', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
            <div id="code-validation-message"></div>
        </div>

        <!-- Porcentaje -->
        <div class="form-group mb-2 mb20">
    <input type="text" name="porcentaje" class="form-control @error('porcentaje') is-invalid @enderror" 
           value="{{ old('porcentaje', 0) }}" id="porcentaje" placeholder="Porcentaje" style="display:none;">
    {!! $errors->first('porcentaje', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
</div>


        <!-- Estado (Select) -->
        <div class="form-group mb-2 mb20">
            <select name="estado" class="form-control @error('estado') is-invalid @enderror" id="estado" hidden>
                <option value="Activo" {{ old('estado', $cupo?->estado) == 'Activo' ? 'selected' : '' }}>Activo</option>
                <option value="Inactivo" {{ old('estado', $cupo?->estado) == 'Inactivo' ? 'selected' : '' }}>Inactivo</option>
            </select>
            {!! $errors->first('estado', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

        <!-- Fecha de Inicio -->
        <div class="form-group mb-2 mb20">
            <label for="fecha_inicio" class="form-label">{{ __('INGRESE FECHA Y HORA DE ACTIVACION') }}</label>
            <input type="datetime-local" name="fecha_inicio" class="form-control @error('fecha_inicio') is-invalid @enderror" value="{{ old('fecha_inicio', $cupo?->fecha_inicio ? $cupo->fecha_inicio->format('Y-m-d\TH:i') : '') }}" id="fecha_inicio" placeholder="Fecha de Inicio">
            {!! $errors->first('fecha_inicio', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

        <!-- Fecha de Fin -->
        <div class="form-group mb-2 mb20">
            <label for="fecha_fin" class="form-label">{{ __('INGRESE FECHA Y HORA DE DESACTIVACION') }}</label>
            <input type="datetime-local" name="fecha_fin" class="form-control @error('fecha_fin') is-invalid @enderror" value="{{ old('fecha_fin', $cupo?->fecha_fin ? $cupo->fecha_fin->format('Y-m-d\TH:i') : '') }}" id="fecha_fin" placeholder="Fecha de Fin">
            {!! $errors->first('fecha_fin', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

        <!-- ID de Usuario (para "admin" o el usuario logueado) -->
        <div class="form-group mb-2 mb20">
            <input type="text" name="id_user" class="form-control @error('id_user') is-invalid @enderror" value="{{ old('id_user', auth()->user()->id) }}" id="id_user" readonly hidden>
            {!! $errors->first('id_user', '<div class="invalid-feedback" role="alert"><strong>:message</strong></div>') !!}
        </div>

    </div>

    <div class="col-md-12 mt20 mt-2">
        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
    </div>
</div>

@section('js')
    <script>
        // Función para generar un código único con las palabras "envivo", "importadora" y "miranda"
        function generateUniqueCode() {
            // Generamos el código con las palabras clave y un sufijo aleatorio
            const prefix = 'I-M-Y';
            const randomString = Math.random().toString(36).substr(2, 6).toUpperCase(); // Generar una cadena aleatoria
            const code = prefix + randomString;

            // Asignamos el código generado al campo de entrada
            document.getElementById('codigo').value = code;

            // Verificar si el código generado ya existe en la base de datos
            checkCodeAvailability(code);
        }

        // Función para verificar si el código ya existe en la base de datos
        function checkCodeAvailability(code) {
            const messageElement = document.getElementById('code-validation-message');

            // Realizar la solicitud AJAX para verificar si el código existe
            fetch(`/check-code-existence/${code}`)
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        messageElement.innerHTML = '<span class="text-danger">Este código ya existe.</span>';
                        document.getElementById('generate-code').disabled = true; // Deshabilitar botón
                    } else {
                        messageElement.innerHTML = '<span class="text-success">Este código está disponible.</span>';
                        document.getElementById('generate-code').disabled = false; // Habilitar botón
                    }
                });
        }

        // Evento para generar el código cuando se hace clic en el botón
        document.getElementById('generate-code').addEventListener('click', function () {
            generateUniqueCode();
        });

        // Evento para la validación en tiempo real al escribir en el campo de código
        document.getElementById('codigo').addEventListener('blur', function () {
            const code = this.value;
            if (code) {
                checkCodeAvailability(code);
            }
        });
    </script>
@endsection
