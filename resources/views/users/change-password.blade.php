@extends('adminlte::page')

@section('title', 'Cambiar Contraseña')

@section('content_header')
    <h1>Cambiar Contraseña</h1>
@stop

@section('css')
    <style>
        /* Estilos generales del formulario */
        .card {
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .card-body {
            padding: 30px;
        }

        /* Títulos */
        h1 {
            font-size: 1.75rem;
            font-weight: bold;
            color: #495057;
        }

        /* Campos de entrada */
        .form-group label {
            font-size: 1rem;
            font-weight: 500;
            color: #495057;
        }

        .form-control {
            border-radius: 5px;
            padding: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }

        /* Estilos de los iconos de ver/ocultar contraseñas */
        .input-group-append button {
            border-radius: 0 5px 5px 0;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            cursor: pointer;
        }

        .input-group-append button:focus {
            box-shadow: none;
            border-color: #007bff;
        }

        .input-group-append button i {
            color: #007bff;
        }

        /* Mensajes de error */
        .invalid-feedback {
            font-size: 0.875rem;
            color: #dc3545;
        }

        /* Botón de enviar */
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            border-radius: 5px;
            padding: 10px 20px;
            font-size: 1rem;
            font-weight: 600;
            width: 100%;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        /* Añadir un espaciado entre los campos */
        .form-group {
            margin-bottom: 20px;
        }
    </style>
@stop

@section('content')
    <div class="row justify-content-center"> <div class="col-md-8 col-lg-6"> <div class="card">
                <div class="card-body">
                    <form action="{{ route('change.password') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="old_password">Contraseña Actual</label>
                            <div class="input-group">
                                <input type="password"
                                    class="form-control @error('old_password') is-invalid @enderror" id="old_password"
                                    name="old_password" required>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="showOldPassword"
                                        onclick="showPassword('old_password', 'showOldPassword')">
                                        <i class="fas fa-eye" id="oldPasswordIcon"></i>
                                    </button>
                                </div>
                                @error('old_password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="password">Nueva Contraseña</label>
                            <div class="input-group">
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    id="password" name="password" required minlength="8"
                                    pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}"
                                    aria-describedby="passwordHelpBlock"
                                    title="Debe tener al menos 8 caracteres, incluyendo mayúscula, minúscula, número y carácter especial.">

                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="showNewPassword"
                                        onclick="showPassword('password', 'showNewPassword')">
                                        <i class="fas fa-eye" id="newPasswordIcon"></i>
                                    </button>
                                </div>

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <small id="passwordHelpBlock" class="form-text text-muted mt-2">
                                La contraseña debe contener:
                                <ul class="mb-0 mt-1 pl-3" id="passwordCriteria">
                                    <li id="length" class="text-danger">Mínimo 8 caracteres</li>
                                    <li id="uppercase" class="text-danger">Una letra mayúscula</li>
                                    <li id="lowercase" class="text-danger">Una letra minúscula</li>
                                    <li id="number" class="text-danger">Un número</li>
                                    <li id="special" class="text-danger">Un carácter especial (!@#$%^&amp;*)</li>
                                </ul>
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Confirmar Nueva Contraseña</label>
                            <div class="input-group">
                                <input type="password"
                                    class="form-control @error('password_confirmation') is-invalid @enderror"
                                    id="password_confirmation" name="password_confirmation" required>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="showConfirmPassword"
                                        onclick="showPassword('password_confirmation', 'showConfirmPassword')">
                                        <i class="fas fa-eye" id="confirmPasswordIcon"></i>
                                    </button>
                                </div>
                                @error('password_confirmation')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Cambiar Contraseña</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        function showPassword(inputId, buttonId) {
            const input = document.getElementById(inputId);
            const button = document.getElementById(buttonId);
            const icon = button.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');

            passwordInput.addEventListener('input', function() {
                const value = passwordInput.value;

                // Reglas
                const hasLength = value.length >= 8;
                const hasUpper = /[A-Z]/.test(value);
                const hasLower = /[a-z]/.test(value);
                const hasNumber = /\d/.test(value);
                const hasSpecial = /[\W_]/.test(value);

                updateCriteria('length', hasLength);
                updateCriteria('uppercase', hasUpper);
                updateCriteria('lowercase', hasLower);
                updateCriteria('number', hasNumber);
                updateCriteria('special', hasSpecial);
            });

            function updateCriteria(id, isValid) {
                const item = document.getElementById(id);
                if (isValid) {
                    item.classList.remove('text-danger');
                    item.classList.add('text-success');
                } else {
                    item.classList.remove('text-success');
                    item.classList.add('text-danger');
                }
            }
        });
    </script>

@stop