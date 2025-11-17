<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login V1</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('images/icons/favicon.ico') }}"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- SweetAlert Library -->
    <style>
        /* Existing styles */
        body {
            font-family: Arial, sans-serif; /* A simple font for better readability */
            background-color: #f0f0f0; /* Light background color */
            margin: 0; /* Remove default margin */
            padding: 20px; /* Padding around the body */
        }

        .limiter {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh; /* Full viewport height */
        }

        .container-login100 {
            background: white; /* White background for the form */
            border-radius: 10px; /* Rounded corners */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Subtle shadow */
            padding: 40px; /* Padding inside the form */
            width: 100%; /* Full width */
            max-width: 400px; /* Maximum width of the form */
        }

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
            padding: 10px; /* Padding inside input */
            border: 1px solid #ccc; /* Border for inputs */
            border-radius: 5px; /* Rounded corners for inputs */
        }

        .input100:focus {
            border-color: #007bff; /* Change border color on focus */
            outline: none; /* Remove outline */
        }

        .login100-form-btn {
            background-color: #007bff; /* Primary button color */
            color: white; /* Button text color */
            padding: 10px; /* Padding inside button */
            border: none; /* No border */
            border-radius: 5px; /* Rounded corners for button */
            cursor: pointer; /* Pointer on hover */
            transition: background-color 0.3s; /* Transition for background color */
            width: 100%; /* Full width button */
        }

        .login100-form-btn:hover {
            background-color: #0056b3; /* Darker shade on hover */
        }

        @media (max-width: 768px) {
            .wrap-input100 label {
                width: auto; /* Adjust label width for smaller screens */
            }
        }

        @media (max-width: 576px) {
            .login100-pic img {
                width: 80%; /* Adjust image size */
                height: auto;
            }

            .login100-form-title {
                font-size: 1.5rem; /* Reduce title font size */
            }

            .login100-form-btn {
                width: 100%; /* Button occupies full width */
            }
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

                <form id="registrationForm" class="login100-form validate-form" method="POST" action="{{ route('cliente.storeFast') }}">
                    @csrf
                    <span class="login100-form-title">
                        FORMULARIO DE REGISTRO RÁPIDO
                    </span>
                
                    <div class="wrap-input100 validate-input">
                        <input class="input100" type="text" id="celular" name="celular" placeholder="Ingrese su Celular" required>
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-phone" aria-hidden="true"></i>
                        </span>
                    </div>
                
                    <div class="wrap-input100 validate-input">
                        <select class="input100" id="destino" name="destino" required>
                            <option value="">Seleccione un Departamento</option>
                            <option value="Pando">Pando</option>
                            <option value="La Paz">La Paz</option>
                            <option value="Cochabamba">Cochabamba</option>
                            <option value="Santa Cruz">Santa Cruz</option>
                            <option value="Beni">Beni</option>
                            <option value="Potosí">Potosí</option>
                            <option value="Oruro">Oruro</option>
                            <option value="Chuquisaca">Chuquisaca</option>
                            <option value="Tarija">Tarija</option>
                        </select>
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-map-marker" aria-hidden="true"></i>
                        </span>
                    </div>
                    <div class="wrap-input100 validate-input">
                        <input class="input100" type="text" id="monto_deposito" name="monto_deposito" placeholder="Ingrese Monto de Depósito" required>
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-money" aria-hidden="true"></i>
                        </span>
                    </div>
                    <div class="wrap-input100 validate-input">
                        <input class="input100" type="text" id="productos" name="productos" placeholder="Ingrese el producto" required>
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-phone" aria-hidden="true"></i>
                        </span>
                    </div>
                
                    <div class="container-login100-form-btn">
                        <button type="submit" class="login100-form-btn">
                            Registrar
                        </button>
                    </div>
                </form>
                
            </div>
        </div>
    </div>

    <script>
        document.getElementById('registrationForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent the default form submission

            // Show SweetAlert notification
            Swal.fire({
                title: 'Registro Exitoso!',
                text: 'Su registro se ha realizado correctamente.',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit(); // Submit the form if the user clicks 'Aceptar'
                }
            });
        });
    </script>
</body>
</html>
