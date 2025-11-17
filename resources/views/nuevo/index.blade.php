<!-- resources/views/nuevo/index.blade.php -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Pedido</title>
    <!-- SweetAlert CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<!-- SweetAlert JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="estiloFormulario/estilo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <style>
.container {
    margin-top: 200px; /* Ajusta el margen superior */
    max-width: 1000px; /* Ajusta el ancho máximo del contenedor */
    margin: 20px auto; /* Ajusta el margen general */
}
.card {
    margin: 20px auto; /* Ajusta el margen del card */
    padding: 10px; /* Ajusta el padding del card */
}
.card-body {
    padding: 10px; /* Aumenta el padding del cuerpo del card */
    overflow: auto; /* Asegura que el contenido no se desborde */
}

.col-md-8 {
    padding: 20px; /* Añade padding a la columna para más espacio */
}
.step {
    padding: 10px;
    display: none;
}

.step.active {
    display: block;
}

.step.active {
    display: block;
}

.animate-in {
    animation: fadeIn 0.5s ease-out;
}

.animate-out {
    animation: fadeOut 0.5s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateX(-100%); }
    to { opacity: 1; transform: translateX(0); }
}

@keyframes fadeOut {
    from { opacity: 1; transform: translateX(0); }
    to { opacity: 0; transform: translateX(100%); }
}

/* Additional Styles */
.is-invalid {
    border-color: red;
}

.invalid-feedback {
    display: none;
    color: red;
}

.is-invalid + .invalid-feedback {
    display: block;
}
h2 {
    font-size: 1rem; /* Tamaño de fuente grande */
    color: #c21b8d; /* Color llamativo */
    text-align: center; /* Centrar el texto */
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3); /* Sombra del texto */
    animation: bounce 2s infinite; /* Animación de rebote */
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0); /* Posición original */
    }
    40% {
        transform: translateY(-20px); /* Elevar */
    }
    60% {
        transform: translateY(-10px); /* Rebotar ligeramente */
    }
}
body {
    transition: background-color 0.5s ease;
}

body:hover {
    background-color: #f0f8ff; /* Color claro al pasar el cursor */
}
button {
    transition: transform 0.3s ease;
}

button:hover {
    transform: scale(1.1); /* Aumenta el tamaño */
}

textarea#productos {
    width: 100%; /* Asegura que el textarea ocupe todo el ancho del contenedor */
    height: 150px; /* Establece una altura fija para el textarea */
    padding: 10px; /* Añade espacio interno */
    border: 2px solid #007bff; /* Color de borde */
    border-radius: 5px; /* Bordes redondeados */
    resize: none; /* Deshabilita el redimensionamiento */
    font-size: 16px; /* Aumenta el tamaño de la fuente */
    font-family: Arial, sans-serif; /* Cambia la fuente */
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Añade una sombra sutil */
    transition: border-color 0.3s ease; /* Efecto de transición en el borde */
}

textarea#productos:focus {
    border-color: #0056b3; /* Cambia el color del borde al enfocar */
    outline: none; /* Elimina el contorno por defecto */
}


    </style>
</head>
<body>

    <div class="container">
        <div class="col-sm-12 text-center">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo" width="100px">
            <h2 class="m-0">🤩BIENVENIDOS A IMPORTADORA MIRANDA🤩</h2>  </div>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <!-- Step 1 -->
                        <div class="step active" id="step-1">
                            <h2 class="m-0">Ingrese sus datos por favor</h2>
                            <form>
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre  <span class="example-text">(ej. Juan Pablo Perez Veliz)</span></label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                                    <div class="invalid-feedback">Por favor, ingrese su nombre.</div>
                                    
                                </div>
                                <div class="mb-3">
                                    <label for="ci" class="form-label">CI <span class="example-text">(ej. 75395145 LP)</span> </label>
                                    <input type="text" class="form-control" id="ci" name="ci" required>
                                    <div class="invalid-feedback">Por favor, ingrese su ci.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="telefono" class="form-label"> Celular<span class="example-text">(ej. 69745234)</span></label>
                                    <input type="text" class="form-control" id="telefono" name="telefono" required>
                                    <div class="invalid-feedback">Por favor, ingrese su telefono.</div>
                                </div>
                                <button type="button" class="btn btn-primary" onclick="nextStep(1)"> <i class="fa fa-arrow-right" aria-hidden="true"></i> Siguiente</button>
                            </form>
                        </div>
    
                        <!-- Step 2 -->
                       <!-- Step 2 -->
<div class="step" id="step-2">
    <h2>Departamentos de Bolivia</h2>
    <div class="d-flex flex-wrap justify-content-center">
        <button type="button" class="btn btn-secondary m-2" onclick="selectDepartment('La Paz')">
            <i class="fa fa-map-marker" aria-hidden="true"></i> La Paz
        </button>
        <button type="button" class="btn btn-secondary m-2" onclick="selectDepartment('Cochabamba')">
            <i class="fa fa-map-marker" aria-hidden="true"></i> Cochabamba
        </button>
        <button type="button" class="btn btn-secondary m-2" onclick="selectDepartment('Santa Cruz')">
            <i class="fa fa-map-marker" aria-hidden="true"></i> Santa Cruz
        </button>
        <button type="button" class="btn btn-secondary m-2" onclick="selectDepartment('Oruro')">
            <i class="fa fa-map-marker" aria-hidden="true"></i> Oruro
        </button>
        <button type="button" class="btn btn-secondary m-2" onclick="selectDepartment('Potosí')">
            <i class="fa fa-map-marker" aria-hidden="true"></i> Potosí
        </button>
        <button type="button" class="btn btn-secondary m-2" onclick="selectDepartment('Tarija')">
            <i class="fa fa-map-marker" aria-hidden="true"></i> Tarija
        </button>
        <button type="button" class="btn btn-secondary m-2" onclick="selectDepartment('Sucre')">
            <i class="fa fa-map-marker" aria-hidden="true"></i> Sucre
        </button>
        <button type="button" class="btn btn-secondary m-2" onclick="selectDepartment('Beni')">
            <i class="fa fa-map-marker" aria-hidden="true"></i> Beni
        </button>
        <button type="button" class="btn btn-secondary m-2" onclick="selectDepartment('Pando')">
            <i class="fa fa-map-marker" aria-hidden="true"></i> Pando
        </button>
    </div>
</div>
                        <!-- Step 3 -->
                        <div class="step" id="step-3">
                            <H4>Ingrese la direccion de la entrega por favor</H4>
                            <form>
                                <div class="mb-3">
                                    <label for="direccion" class="form-label">Dirección <span class="example-text">EJEMPLO (COCHABAMBA-SACABA AV. TUNEL TRES CUADRAS ANTES
                                                DE LLEGAR AL TERCER SEMáFORO HACIA SACABA)</span></label>
                                    <input type="text" class="form-control" id="direccion" name="direccion" required>
                                    <div class="invalid-feedback">Por favor, ingrese su direccion.</div>
                                </div>
                                <button type="button" class="btn btn-primary" onclick="nextStep(3)"> <i class="fa fa-arrow-right" aria-hidden="true"></i> Siguiente</button>
                            </form>
                        </div>
    
                        <!-- Step 4 -->
                        <div class="step" id="step-4">
                            <h2>QUIERES PAGAR EL ENVIO DE SU PRODUCTO?</h2>
                            <div class="d-flex flex-wrap justify-content-center">
                                <button type="button" class="btn btn-secondary m-2" onclick="selectPayment('Pagado')">
                                    <i class="fa fa-check" aria-hidden="true"></i> SI cancelar la entrega
                                </button>
                                <button type="button" class="btn btn-secondary m-2" onclick="selectPayment('Por Cobrar')">
                                    <i class="fa fa-times" aria-hidden="true"></i> NO cancelar la entrega
                                </button> </div>
                        </div>
    
                        <!-- Step 5 -->
                        <!-- Step 5 -->
                        <div class="step" id="step-5">
                            <h2>Agregar Productos</h2>
                            <textarea id="productos" name="productos" rows="5" style="display: none;" disabled></textarea>
                            <span id="product-error" style="color: red; display: none;">Por favor, agregue un producto.</span>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal"> <i class="fa fa-plus" aria-hidden="true"></i>Agregar Producto</button>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProductModal"> <i class="fa fa-edit" aria-hidden="true"></i>Editar Producto</button>
                            <button type="button" class="btn btn-primary" onclick="nextStep(5)"> <i class="fa fa-arrow-right" aria-hidden="true"></i> Siguiente</button>
                        </div>
                        
    <!-- Modal para agregar producto -->
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">Agregar Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label for="nombreProducto" class="form-label">Nombre del Producto</label>
                            <input type="text" class="form-control" id="nombreProducto" name="nombreProducto" required>
                            <div class="invalid-feedback">Por favor, ingrese los datos.</div>
                        </div>
                        <div class="mb-3">
                            <label for="costoProducto" class="form-label">Costo del Producto</label>
                            <input type="number" class="form-control" id="costoProducto" name="costoProducto" required>
                            <div class="invalid-feedback">Por favor, ingrese los datos.</div>
                        </div>
                        <div class="mb-3">
                            <label for="cantidadProducto" class="form-label">Cantidad del Producto</label>
                            <input type="number" class="form-control" id="cantidadProducto" name="cantidadProducto" required>
                            <div class="invalid-feedback">Por favor, ingrese los datos.</div>
                        </div>
                        <div class="mb-3">
                            <label for="detalleProducto" class="form-label">Detalle del Producto</label>
                            <input type="text" class="form-control" id="detalleProducto" name="detalleProducto" required>
                            <div class="invalid-feedback">Por favor, ingrese los datos.</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="addProduct()">
                        <i class="fa fa-check" aria-hidden="true"></i> Guardar
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fa fa-times" aria-hidden="true"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para editar producto -->
    <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProductModalLabel">Editar Productos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="editProductsList">
                    <!-- Aquí se generarán los productos dinámicamente -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fa fa-times" aria-hidden="true"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para editar un producto específico -->
    <div class="modal fade" id="editSingleProductModal" tabindex="-1" aria-labelledby="editSingleProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSingleProductModalLabel">Editar Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label for="nombreProductoEdit" class="form-label">Nombre del Producto</label>
                            <input type="text" class="form-control" id="nombreProductoEdit" name="nombreProductoEdit" required>
                            <div class="invalid-feedback">Por favor, ingrese los datos.</div>
                        </div>
                        <div class="mb-3">
                            <label for="costoProductoEdit" class="form-label">Costo del Producto</label>
                            <input type="text" class="form-control" id="costoProductoEdit" name="costoProductoEdit" required>
                            <div class="invalid-feedback">Por favor, ingrese los datos.</div>
                        </div>
                        <div class="mb-3">
                            <label for="cantidadProductoEdit" class="form-label">Cantidad del Producto</label>
                            <input type="number" class="form-control" id="cantidadProductoEdit" name="cantidadProductoEdit" required>
                            <div class="invalid-feedback">Por favor, ingrese los datos.</div>
                        </div>
                        <div class="mb-3">
                            <label for="detalleProductoEdit" class="form-label">Detalle del Producto</label>
                            <input type="text" class="form-control" id="detalleProductoEdit" name="detalleProductoEdit" required>
                            <div class="invalid-feedback">Por favor, ingrese los datos.</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="updateProduct()">
                        <i class="fa fa-check" aria-hidden="true"></i> Actualizar
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fa fa-times" aria-hidden="true"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
    
                       <!-- Step 6 -->
<div class="step" id="step-6">
    <div class="mb-3">
        <label for="foto_comprobante" class="form-label">Foto Comprobante</label>
        <input type="file" class="form-control @error('foto_comprobante') is-invalid @enderror" id="foto_comprobante" name="foto_comprobante" accept=".jpg, .jpeg, .png" required onchange="validateFile()">
        <div class="error-message">Este campo es obligatorio.</div>
        @error('foto_comprobante')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div id="file-error" style="color: red; display: none;">Formato no válido. Vuelve a intentar.</div>
        <span id="file-upload-error" style="color: red; display: none;">Por favor, ingrese la foto de comprobante.</span>
        <img id="foto-preview" src="#" alt="Vista previa" style="display: none; width: 100px; height: auto;">
        <button type="button" class="btn btn-primary" onclick="nextStep(6)">
            <i class="fa fa-arrow-right" aria-hidden="true"></i> Siguiente
        </button>
    </div>
</div>


                        <!-- Step 6 -->
                       <!-- Step 7 -->
<!-- Step 7 -->
<!-- Step 7 -->
<!-- Step 7 -->
<div class="step" id="step-7">
    <H2>Felicidades, <span id="nombreFinal"></span> Para finalizar su pedido, presione el botón de Enviar y Descargar Comprobante</H2>
    <button type="submit" class="btn btn-primary" onclick="submitAndGeneratePDF()">
        <i class="fa fa-file-pdf" aria-hidden="true"></i> Enviar y Descargar Comprobante
    </button>
    <p style="display: none;" >CI: <span id="ciResumen"></span></p>
    <p style="display: none;" >Teléfono: <span id="telefonoResumen"></span></p>
    <p style="display: none;" >Departamento: <span id="departamentoResumen"></span></p>
    <p style="display: none;" >Dirección: <span id="direccionResumen"></span></p>
    <p style="display: none;" >Estado del Pago: <span id="pagoResumen"></span></p>
    <p style="display: none;">Productos: <span id="productosResumen"></span></p>



    <form method="POST" action="{{ route('cliente.storenuevo') }}" enctype="multipart/form-data" id="finalForm" style="display: none;">
        @csrf
        <input type="hidden" name="nombre" id="nombreHidden" value="">
        <input type="hidden" name="ci" id="ciHidden" value="">
        <input type="hidden" name="celular" id="celularHidden" value="">
        <input type="hidden" name="destino" id="destinoHidden" value="">
        <input type="hidden" name="direccion" id="direccionHidden" value="">
        <input type="hidden" name="estado" id="estadoHidden" value="">
        <input type="hidden" name="cantidad_productos" id="cantidad_productosHidden" value="">
        <input type="hidden" name="detalle" id="detalleHidden" value="">
        <input type="hidden" name="productos" id="productosHidden" value="">
        <input type="hidden" name="monto_deposito" id="monto_depositoHidden" value="">
        <input type="hidden" name="monto_enviado_pagado" id="monto_enviado_pagadoHidden" value="">
        <input type="file" name="foto_comprobante" id="foto_comprobante_final" style="display: none;" required>
    </form>
</div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="estiloFormulario/app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

<script>
let currentStep = 1;
let nombre = '';
let ci = '';
let telefono = '';
let departamento = '';
let direccion = '';
let pago = '';
let productos = [];
let currentProductIndex = null;
let totalCost = 0;
let totalQuantity = 0;
let detalles = '';
let productosData = '';

// Function to navigate to the next step with animation and validation
// Function to navigate to the next step with animation and validation
function nextStep(step) {
    // Hide the current step with animation
    const currentStepElement = document.getElementById(`step-${step}`);
    const form = currentStepElement.querySelector('form');

    if (form) {
        const inputs = form.querySelectorAll('input[required], input[type="file"]');
        let valid = true;

        // Validate required inputs
        inputs.forEach(input => {
            if (!input.value.trim()) {
                valid = false;
                input.classList.add('is-invalid'); // Highlight invalid fields
            } else {
                input.classList.remove('is-invalid'); // Remove highlight if valid
            }
        });

        // If invalid, do not proceed to the next step
        if (!valid) {
            return;
        }
    }

    // Specific validation for Step 5 and Step 6
    if (step === 5) {
        const productosTextarea = document.getElementById('productos');
        const productErrorSpan = document.getElementById('product-error');
        if (!productosTextarea.value.trim()) {
            productErrorSpan.style.display = 'block';
            return; // Stay on the current step if no product is added
        } else {
            productErrorSpan.style.display = 'none';
        }
    }

    if (step === 6) {
        const fotoComprobanteInput = document.getElementById('foto_comprobante');
        const fileUploadErrorSpan = document.getElementById('file-upload-error');
        if (!fotoComprobanteInput.files.length) {
            fileUploadErrorSpan.style.display = 'block';
            return; // Stay on the current step if no file is uploaded
        } else {
            fileUploadErrorSpan.style.display = 'none';
        }
    }

    // If all validations pass, proceed to the next step
    currentStepElement.classList.add('animate-out');
    setTimeout(() => {
        currentStepElement.classList.remove('active', 'animate-out');
    }, 500);

    // Show the next step with animation
    currentStep = step + 1; // Move to the next step
    const nextStepElement = document.getElementById(`step-${currentStep}`);
    if (nextStepElement) {
        nextStepElement.classList.add('active', 'animate-in');
        setTimeout(() => {
            nextStepElement.classList.remove('animate-in');
        }, 500);
    }

    // Update the final step with the user's information if necessary
    if (currentStep === 7) {
        const nombreFinal = document.getElementById('nombreFinal');
        const nombreResumen = document.getElementById('nombreResumen');
        const ciResumen = document.getElementById('ciResumen');
        const telefonoResumen = document.getElementById('telefonoResumen');
        const departamentoResumen = document.getElementById('departamentoResumen');
        const direccionResumen = document.getElementById('direccionResumen');
        const pagoResumen = document.getElementById('pagoResumen');
        const productosResumen = document.getElementById('productosResumen');

        if (nombreFinal) {
            nombreFinal.innerText = nombre;
        }
        if (nombreResumen) {
            nombreResumen.innerText = nombre;
        }
        if (ciResumen) {
            ciResumen.innerText = ci;
        }
        if (telefonoResumen) {
            telefonoResumen.innerText = telefono;
        }
        if (departamentoResumen) {
            departamentoResumen.innerText = departamento;
        }
        if (direccionResumen) {
            direccionResumen.innerText = direccion;
        }
        if (pagoResumen) {
            pagoResumen.innerText = pago;
        }
        if (productosResumen) {
            productosResumen.innerText = productos.map(product => `Nombre: ${product.nombre}, Costo: ${product.costo}, Cantidad: ${product.cantidad}, Detalle: ${product.detalle}`).join('\n');
        }

        // Update hidden inputs
        document.getElementById('nombreHidden').value = nombre;
        document.getElementById('ciHidden').value = ci;
        document.getElementById('celularHidden').value = telefono;
        document.getElementById('destinoHidden').value = departamento;
        document.getElementById('direccionHidden').value = direccion;
        document.getElementById('estadoHidden').value = pago;
        document.getElementById('cantidad_productosHidden').value = totalQuantity;
        document.getElementById('detalleHidden').value = detalles;
        document.getElementById('productosHidden').value = productos.map(product => `${product.nombre}`).join('\n');
        document.getElementById('monto_depositoHidden').value = totalCost;
        document.getElementById('monto_enviado_pagadoHidden').value = totalCost;

        // Move the file input to the form
        const fotoComprobanteInput = document.getElementById('foto_comprobante');
        const fotoComprobanteFinalInput = document.getElementById('foto_comprobante_final');
        fotoComprobanteFinalInput.files = fotoComprobanteInput.files;
    }
}

// Function to submit and generate PDF
function submitAndGeneratePDF() {
    const form = document.getElementById('finalForm');
    const formData = new FormData(form);

    // Submit the form via AJAX
    fetch(form.action, {
        method: form.method,
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Generate the PDF with the order ID
            generatePDF(data.orderId);
            Swal.fire({
                title: 'Éxito!',
                text: data.message,
                icon: 'success',
                confirmButtonText: 'Aceptar',
            }).then(() => {
                setTimeout(() => {
                    Swal.close(); 
                    window.location.href = '{{ route("nuevo.index") }}';// Cierra la alerta después de 5 segundos
                }, 5000); // 5000 milisegundos = 5 segundos
            });
        } else {
            // Handle error case if needed
            Swal.fire({
                title: 'Error!',
                text: 'Ocurrió un error al procesar el pedido.',
                icon: 'error',
                confirmButtonText: 'Aceptar',
            });
        }
    })
    .catch(error => {
        console.error('Error submitting form:', error);
        Swal.fire({
            title: 'Error!',
            text: 'Ocurrió un error al procesar el pedido.',
            icon: 'error',
            confirmButtonText: 'Aceptar',
        });
    });
}

// Generate PDF function with order ID
function generatePDF(orderId) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    // Add title
    doc.text('Pedido Detalles', 10, 10);

    // Add user information
    doc.text(`Nombre: ${nombre}`, 10, 20);
    doc.text(`CI: ${ci}`, 10, 30);
    doc.text(`Teléfono: ${telefono}`, 10, 40);
    doc.text(`Departamento: ${departamento}`, 10, 50);
    doc.text(`Dirección: ${direccion}`, 10, 60);
    doc.text(`Estado del Pago: ${pago}`, 10, 70);
    doc.text(`ID del Pedido: ${orderId}`, 10, 80); // Add order ID

    // Add products information with proper spacing
    let y = 90; // Start y-coordinate for products
    doc.text('Productos:', 10, y);
    y += 10; // Increase y-coordinate for the next line

    productos.forEach((product, index) => {
        const productText = `Nombre: ${product.nombre}, Costo: ${product.costo}, Cantidad: ${product.cantidad}, Detalle: ${product.detalle}`;
        doc.text(productText, 10, y);
        y += 10; // Increase y-coordinate for the next product
    });

    // Save the PDF
    doc.save('pedido.pdf');
}
// Generate PDF function with order ID
function generatePDF(orderId) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    // Add title
    doc.text('Pedido Detalles', 10, 10);

    // Add user information
    doc.text(`Nombre: ${nombre}`, 10, 20);
    doc.text(`CI: ${ci}`, 10, 30);
    doc.text(`Teléfono: ${telefono}`, 10, 40);
    doc.text(`Departamento: ${departamento}`, 10, 50);
    doc.text(`Dirección: ${direccion}`, 10, 60);
    doc.text(`Estado del Pago: ${pago}`, 10, 70);
    doc.text(`ID del Pedido: ${orderId}`, 10, 80); // Add order ID

    // Add products information with proper spacing
    let y = 90; // Start y-coordinate for products
    doc.text('Productos:', 10, y);
    y += 10; // Increase y-coordinate for the next line

    productos.forEach((product, index) => {
        const productText = `Nombre: ${product.nombre}, Costo: ${product.costo}, Cantidad: ${product.cantidad}, Detalle: ${product.detalle}`;
        doc.text(productText, 10, y);
        y += 10; // Increase y-coordinate for the next product
    });

    // Save the PDF
    doc.save('pedido.pdf');
}

// Function to select department and navigate to the next step
function selectDepartment(departamentoSeleccionado) {
    departamento = departamentoSeleccionado;
    nextStep(2);
}

// Function to select payment status and navigate to the next step
function selectPayment(pagoSeleccionado) {
    pago = pagoSeleccionado;
    nextStep(4);
}

// Function to add a product
function addProduct() {
    const nombreProducto = document.getElementById('nombreProducto').value;
    const costoProducto = parseFloat(document.getElementById('costoProducto').value);
    const cantidadProducto = parseInt(document.getElementById('cantidadProducto').value);
    const detalleProducto = document.getElementById('detalleProducto').value;

    if (!nombreProducto || !costoProducto || !cantidadProducto || !detalleProducto) {
        alert("Por favor, completa todos los campos obligatorios.");
        return; // Salir de la función si hay campos vacíos
    }
    productos.push({
        nombre: nombreProducto,
        costo: costoProducto,
        cantidad: cantidadProducto,
        detalle: detalleProducto
    });

    totalCost += costoProducto * cantidadProducto;
    totalQuantity += cantidadProducto;
    detalles += `${nombreProducto} - ${detalleProducto}\n`;

    updateProductsList();
    updateProductsTextarea();

    const addProductModal = bootstrap.Modal.getInstance(document.getElementById('addProductModal'));
    addProductModal.hide();

    // Clear the input fields
    document.getElementById('nombreProducto').value = '';
    document.getElementById('costoProducto').value = '';
    document.getElementById('cantidadProducto').value = '';
    document.getElementById('detalleProducto').value = '';
    
    updateTextareaVisibility();
}
function updateTextareaVisibility() {
    const textarea = document.getElementById('productos');
    if (productos.length > 0) {
        textarea.style.display = 'block'; // Show the textarea
    } else {
        textarea.style.display = 'none'; // Hide the textarea
    }
}

function updateProductsList() {
    const editProductsList = document.getElementById('editProductsList');
    editProductsList.innerHTML = '';

    productos.forEach((product, index) => {
        const productDiv = document.createElement('div');
        productDiv.classList.add('d-flex', 'justify-content-between', 'mb-2');
        productDiv.innerHTML = `
            <span>${product.nombre} - ${product.detalle}</span>
            <div>
                <button class="btn btn-sm btn-primary" data-index="${index}" onclick="openEditModal(${index})">Editar</button>
                <button class="btn btn-sm btn-danger" data-index="${index}" onclick="deleteProduct(${index})">Eliminar</button>
            </div>
        `;
        editProductsList.appendChild(productDiv);
    });
}

function updateProductsTextarea() {
    const productosText = productos.map(product => 
        `Nombre: ${product.nombre}, Costo: ${product.costo}, Cantidad: ${product.cantidad}, Detalle: ${product.detalle}`
    ).join('\n');
    
    productosData = productosText; // Update productosData with the formatted text
    document.getElementById('productos').value = productosData; // Update the textarea content
    updateTextareaVisibility(); // Update visibility based on new data
}

function openEditModal(index) {
    currentProductIndex = index;
    const product = productos[index];
    document.getElementById('nombreProductoEdit').value = product.nombre;
    document.getElementById('costoProductoEdit').value = product.costo;
    document.getElementById('cantidadProductoEdit').value = product.cantidad;
    document.getElementById('detalleProductoEdit').value = product.detalle;

    const editSingleProductModal = new bootstrap.Modal(document.getElementById('editSingleProductModal'));
    editSingleProductModal.show();
}

function updateProduct() {
    if (currentProductIndex !== null) {
        const nombreProductoEdit = document.getElementById('nombreProductoEdit').value;
        const costoProductoEdit = parseFloat(document.getElementById('costoProductoEdit').value);
        const cantidadProductoEdit = parseInt(document.getElementById('cantidadProductoEdit').value);
        const detalleProductoEdit = document.getElementById('detalleProductoEdit').value;

        totalCost -= productos[currentProductIndex].costo * productos[currentProductIndex].cantidad;
        totalQuantity -= productos[currentProductIndex].cantidad;
        detalles = detalles.replace(`${productos[currentProductIndex].nombre} - ${productos[currentProductIndex].detalle}\n`, '');
        productos[currentProductIndex] = {
            nombre: nombreProductoEdit,
            costo: costoProductoEdit,
            cantidad: cantidadProductoEdit,
            detalle: detalleProductoEdit
        };
        totalCost += costoProductoEdit * cantidadProductoEdit;
        totalQuantity += cantidadProductoEdit;
        detalles += `${nombreProductoEdit} - ${detalleProductoEdit}\n`;

        updateProductsList();
        updateProductsTextarea();

        const editSingleProductModal = bootstrap.Modal.getInstance(document.getElementById('editSingleProductModal'));
        editSingleProductModal.hide();
    }
}

function deleteProduct(index) {
    if (index >= 0 && index < productos.length) {
        totalCost -= productos[index].costo * productos[index].cantidad;
        totalQuantity -= productos[index].cantidad;
        detalles = detalles.replace(`${productos[index].nombre} - ${productos[index].detalle}\n`, '');
        productos.splice(index, 1);

        updateProductsList();
        updateProductsTextarea();
    }
}

// Event listeners for input fields
document.addEventListener('DOMContentLoaded', function() {
    const nombreInput = document.getElementById('nombre');
    const ciInput = document.getElementById('ci');
    const telefonoInput = document.getElementById('telefono');
    const direccionInput = document.getElementById('direccion');

    nombreInput.addEventListener('input', function() {
        nombre = this.value;
    });

    ciInput.addEventListener('input', function() {
        ci = this.value;
    });

    telefonoInput.addEventListener('input', function() {
        telefono = this.value;
    });

    direccionInput.addEventListener('input', function() {
        direccion = this.value;
    });
});

// Show the products list when the edit modal is shown
document.getElementById('editProductModal').addEventListener('shown.bs.modal', function () {
    updateProductsList();
});
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {


        // Muestra la vista previa de la imagen seleccionada
        const fotoInput = document.getElementById('foto_comprobante');
        const fotoPreview = document.getElementById('foto-preview');

        fotoInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    fotoPreview.src = e.target.result;
                    fotoPreview.style.display = 'block'; // Muestra la imagen
                }
                reader.readAsDataURL(file);
            } else {
                fotoPreview.style.display = 'none'; // Oculta la imagen si no hay archivo
            }
        });
    });
</script>
<script>
function validateFile() {
    const input = document.getElementById('foto_comprobante');
    const fileError = document.getElementById('file-error');
    const filePath = input.value;
    const allowedExtensions = /(\.jpg|\.jpeg|\.png)$/i;

    if (!allowedExtensions.exec(filePath)) {
        fileError.style.display = 'block';
        input.value = ''; // Limpiar el campo
    } else {
        fileError.style.display = 'none';
    }
}
</script>
</body>
</html>