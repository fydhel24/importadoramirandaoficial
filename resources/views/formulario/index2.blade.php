<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login V1</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('images/icons/favicon.ico') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('fonts/font-awesome-4.7.0/css/font-awesome.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/animate/animate.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/css-hamburgers/hamburgers.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/select2/select2.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/util.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        .wrap-input100 {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .wrap-input100 label {
            margin-right: 10px;
            width: 100px;
        }

        .input100 {
            flex-grow: 1;
        }
    </style>
</head>
<body>
    
    <div class="limiter">
        <div class="container-login100">
            <div class="wrap-login100">
                <div class="login100-pic js-tilt" data-tilt>
                    <img src="{{ asset('images/logo.png') }}" alt="IMG">
                </div>

                <form class="login100-form validate-form">
                    @csrf
                    <span class="login100-form-title">
                        Formulario de Registro
                    </span>

                    <div class="wrap-input100 validate-input">
                        <input class="input100" type="text" id="nombre" name="nombre" placeholder="Ingrese su codigo para completar su pedido" required>
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-user" aria-hidden="true"></i>
                        </span>
                    </div>

                    <div class="container-login100-form-btn" onclick="window.location='{{ route('cliente.index') }}'">
                        <button class="login100-form-btn" >
                            Ingresar al Formulario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="{{ asset('vendor/jquery/jquery-3.2.1.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/popper.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('vendor/select2/select2.min.js') }}"></script>
    <script src="{{ asset('vendor/tilt/tilt.jquery.min.js') }}"></script>
    <script>
        $('.js-tilt').tilt({
            scale: 1.1
        });
        function validateForm() {
        const nombre = document.getElementById('nombre').value;

        if (nombre.trim() === '') {
            alert('Por favor, ingrese su código para completar su pedido.');
            return; // Detiene la ejecución si el campo está vacío
        }

        // Si el campo está lleno, envía el formulario
        document.getElementById('registrationForm').submit();
    }
    </script>
    <script src="{{ asset('js/main.js') }}"></script>

</body>
</html>
