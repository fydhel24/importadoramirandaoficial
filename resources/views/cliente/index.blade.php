<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Pedido</title>
    <link rel="icon" href="https://importadoramiranda.com/images/logo.png" type="image/png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Abel|Abril+Fatface|Alegreya|Arima+Madurai|Dancing+Script|Dosis|Merriweather|Oleo+Script|Overlock|PT+Serif|Pacifico|Playball|Playfair+Display|Share|Unica+One|Vibur">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        body,
        .wrapper,
        .content-wrapper {
            background: rgb(63,94,251);
            background: radial-gradient(circle, rgba(63,94,251,1) 18%, rgba(252,70,107,1) 100%);
            opacity: .95;
            align-items: center; /* Centra verticalmente */
        }
        
        .loading {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            transition: background-color 0.3s, border-color 0.3s;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .logo {
            max-width: 150px;
            height: auto;
            margin-bottom: 20px;
        }
        .form-control, .input-group-text {
            transition: border-color 0.3s;
        }
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }
        .example-text {
            opacity: 0.5; /* Ajusta el valor de opacidad según lo que necesites */
        }
        .input-celeste {
    background-color: #b3e0ff !important; /* Color celeste */
    color: #000 !important; /* Color del texto */
    border: 1px solid #007bff; /* Opcional: agregar un borde celeste */
    cursor: not-allowed; /* Cambia el cursor para indicar que está deshabilitado */
}
.input-group-append {
    display: flex;
    justify-content: center; /* Centra los botones horizontalmente */
    margin-top: 10px; /* Espaciado superior opcional */
}

    </style>
   
</head>
<body>
    
        
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-12 text-center">
                            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo">
                            <h1 class="m-0">Crear Pedido</h1>
                        </div>
                    </div>
                </div>
            </div>

            <section class="content">
                <div class="container-fluid">
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Ingrese los detalles del pedido</h3>
                                </div>
                                <div class="card-body">
                                    <form id="pedido-form" action="{{ route('cliente.store') }}" method="POST">
                                        @csrf
                                        <div class="form-group">
                                            <label for="nombre" class="required">Nombre <span class="example-text">(ej. Juan Pablo Perez Veliz)</span></label>
                                            <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ingrese el nombre" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="ci" class="required">CI <span class="example-text">(ej. 75395145 LP)</span></label>
                                            <input type="text" class="form-control" id="ci" name="ci" placeholder="Ingrese el CI" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="celular" class="required">Celular <span class="example-text">(ej. 69745234)</span></label>
                                            <input type="text" class="form-control" id="celular" name="celular" placeholder="Ingrese el celular" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="productos">Destino <span class="example-text">(RECUERDE QUE LOS PEDIDOS SE RECEPCIONAN HASTA LAS 11:00 AM)</span></label>
                                            <input type="text"  class="form-control input-celeste" id="destino" name="destino" readonly placeholder="No hay destino agregado" style="display: none;">
                                            <div class="input-group">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-primary" id="add-destination">
                                                        <i class="fas fa-map-marker-alt"></i> Agregar Destino
                                                    </button> </div>
                                            </div>
                                        </div>
                                        
                                        
                                        <div class="form-group">
                                            <label for="direccion" class="required">Dirección <span class="example-text">(COCHABAMBA-SACABA AV. TUNEL TRES CUADRAS ANTES
                                                DE LLEGAR AL TERCER SEMáFORO HACIA SACABA)</span></label>
                                            <input type="text" class="form-control" id="direccion" name="direccion" placeholder="Ingrese la dirección" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="estado" class="required">Tipo de pago de Envio</label>
                                            <select class="form-control" id="estado" name="estado" required>
                                                <option value="" disabled selected>Seleccione el estado</option>
                                                <option value="Por Cobrar">Por Cobrar si no cancelo la entrega</option>
                                                <option value="Pagado">Pagado si ya cancelo la entrega</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group"  style="display: none;">
                                            <label for="cantidad_productos" class="required">Cantidad de Productos <span class="example-text">(ej. 3)</span></label>
                                            <input type="number" class="form-control" id="cantidad_productos" name="cantidad_productos" placeholder="Ingrese la cantidad de productos" required>
                                        </div>
                                        
                                        
                                       
                                        <div class="form-group">
                                            <label for="productos">Productos</label>
                                            <div class="input-group">
                                                <textarea class="form-control input-celeste" id="productos" name="productos" placeholder="Ingrese los productos" readonly style="display: none;"></textarea>
                                            </div>
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-primary" id="add-product">
                                                    <i class="fas fa-plus"></i> Agregar
                                                </button>
                                                <button type="button" class="btn btn-secondary" id="edit-product">
                                                    <i class="fas fa-edit"></i> Editar
                                                </button>  </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="detalle">Detalle <span class="example-text">(ej. 1 DAME LOS DATELLES DE CADA PRODUCTO)</span></label>
                                            <textarea class="form-control" id="detalle" name="detalle" placeholder="Ingrese detalles adicionales"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="monto_enviado_pagado" class="required">Monto Enviado/Pagado</label>
                                            <input type="number" class="form-control input-celeste" id="monto_enviado_pagado" name="monto_enviado_pagado" placeholder="Monto" readonly required>
                                        </div>
                                        <div class="form-group">
                                            <label for="monto_deposito" class="required">Monto Total del deposito o QR enviado</label>
                                            <input type="number" class="form-control" id="monto_deposito" name="monto_deposito" placeholder="Ingrese el monto de depósito" required>
                                        </div>
                                        <button type="button" id="submit-button" class="btn btn-primary btn-block">  <i class="fas fa-shopping-cart"></i> Crear Pedido</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
       
    

    <div class="loading">
        <div class="spinner-border" role="status">
            <span class="sr-only">Cargando...</span>
        </div>
    </div>

<!-- Modal Principal -->
<div class="modal fade" id="mainModal" tabindex="-1" role="dialog" aria-labelledby="mainModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mainModalLabel">Seleccionar Tarifario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Por favor, elija el tipo de tarifario que desea consultar:</p>
                <div class="d-flex flex-column">
                    <button type="button" class="btn btn-primary mb-2" id="nationalTariff">TARIFARIO NACIONAL</button>
                    <button type="button" class="btn btn-secondary mb-2" id="provincialTariff">TARIFARIO PROVINCIAS</button>
                    <button type="button" class="btn btn-success" id="localTariff">TARIFARIO LOCAL</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Destinos -->
<div class="modal fade" id="destinationModal" tabindex="-1" role="dialog" aria-labelledby="destinationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="destinationModalLabel">Seleccionar Destino</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Seleccione un destino de la lista a continuación:</p>
                <div id="destinationButtons" class="d-flex flex-column"></div>
            </div>
        </div>
    </div>
</div>


<!-- Modal de Servicio -->
<div class="modal fade" id="serviceModal" tabindex="-1" role="dialog" aria-labelledby="serviceModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="serviceModalLabel">Seleccionar Servicio</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
               
                <div id="serviceCost" class="mt-3"></div>
                <div class="d-flex flex-row">
                    <button type="button" class="btn btn-info " id="airService">Por Avión - A domicilio</button>
                    <button type="button" class="btn btn-danger" id="busService">Por Bus - Recojo por transportadora</button>
                </div>
                <button type="button" class="btn btn-success mt-3" id="finishButton" style="display: none;">Terminar</button>

            </div>
        </div>
    </div>
</div>


    <!-- Modals for Product Addition and Editing -->
    <div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-labelledby="productModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalLabel">Agregar Producto</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="producto_nombre">Nombre del Producto</label>
                        <input type="text" class="form-control" id="producto_nombre" placeholder="Ingrese el nombre del producto">
                    </div>
                    <div class="form-group">
                        <label for="producto_costo">Costo del Producto</label>
                        <input type="number" class="form-control" id="producto_costo" placeholder="Ingrese el costo del producto">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="save-product">Agregar Producto</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-labelledby="editProductModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProductModalLabel">Editar Productos</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul id="product-list" class="list-group"></ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="update-products">Actualizar Productos</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirmar Datos</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="data-preview"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">No, Editar</button>
                    <button type="button" class="btn btn-primary" id="confirm-save">Sí, Guardar</button>
                </div>
                
            </div>
        </div>
    </div>
    
    <!-- Modals for pdf -->
    <div class="modal fade" id="downloadModal" tabindex="-1" role="dialog" aria-labelledby="downloadModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="downloadModalLabel">Opciones de Descarga</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿Qué te gustaría hacer?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="download-pdf">Descargar PDF</button>
                    <button type="button" class="btn btn-secondary" id="finish-and-refresh">Terminar y Cargar Página</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script>
/////////////////////
const tarifarios = {
    national: {
        "Cochabamba": { costo2: "15.00Bs-20.00Bs", normal: "40.00Bs", tiempo: "24 Horas" },
        "Santa Cruz": { costo2: "15.00Bs-20.00Bs", normal: "40.00Bs", tiempo: "24 Horas" },
        "Oruro": { costo2: "15.00Bs-20.00Bs", normal: "40.00Bs", tiempo: "24 Horas" },
        "Sucre": { costo2: "15.00Bs-20.00Bs", normal: "40.00Bs", tiempo: "24 - 48 Horas" },
        "Tarija": { costo2: "15.00Bs-20.00Bs", normal: "40.00Bs", tiempo: "24 - 48 Horas" },
        "Potosi": { costo2: "15.00Bs-20.00Bs", normal: "40.00Bs", tiempo: "24 Horas" },
        "Trinidad": { costo2: "40.00Bs", normal: "40.00Bs", tiempo: "24 - 48 Horas" },
        "Cobija": { costo2: "40.00Bs", normal: "40.00Bs", tiempo: "24 - 48 Horas" },
        "Provincias": { costo2: "40.00Bs", normal: "40.00Bs", tiempo: "48 - 72 Horas" }
    },
    provincial: {
        "Camiri": { costo: "40.00Bs",costo2:"20.00 - 25.00", tiempo: "72 Horas" },
        "Robore": { costo: "40.00Bs",costo2:"20.00 - 25.00", tiempo: "72 Horas" },
        "Puerto Suarez": { costo: "40.00Bs", costo2:"20.00 - 25.00",tiempo: "72 Horas" },
        "Riberalta": { costo: "40.00Bs", costo2:"20.00 - 25.00",tiempo: "72 Horas" },
        "Rurrenabaque": { costo: "40.00Bs", costo2:"20.00 - 25.00",tiempo: "72 Horas" },
        "Yacuiba": { costo: "40.00Bs",costo2:"20.00 - 25.00", tiempo: "72 Horas" },
        "Tupiza": { costo: "40.00Bs",costo2:"20.00 - 25.00", tiempo: "72 Horas" },
        "Villamontes": { costo: "40.00Bs",costo2:"20.00 - 25.00", tiempo: "72 Horas" },
        "Bermejo": { costo: "40.00Bs",costo2:"20.00 - 25.00", tiempo: "72 Horas" }
    },
    local: {
        "Zona Central": { normal: "7.00Bs", contraentrega: "5.00Bs", tiempo: "24 Horas" },
        "El Alto": { normal: "15.00Bs", contraentrega: "5.00Bs", tiempo: "24 Horas" },
        "Zona Miraflores": { normal: "7.00Bs", contraentrega: "5.00Bs", tiempo: "24 Horas" },
        "Zona Sur": { normal: "7.00Bs", contraentrega: "5.00Bs", tiempo: "24 Horas" },
        "Zona Sopocachi": { normal: "7.00Bs", contraentrega: "5.00Bs", tiempo: "24 Horas" }
    }
};

document.getElementById('add-destination').addEventListener('click', function() {
    $('#mainModal').modal('show');
});

let currentTarifarioType = 'national';

document.getElementById('nationalTariff').addEventListener('click', function() {
    currentTarifarioType = 'national';
    loadDestinations(currentTarifarioType);
    $('#mainModal').modal('hide');
    $('#destinationModal').modal('show');
});

document.getElementById('provincialTariff').addEventListener('click', function() {
    currentTarifarioType = 'provincial';
    loadDestinations(currentTarifarioType);
    $('#mainModal').modal('hide');
    $('#destinationModal').modal('show');
});

document.getElementById('localTariff').addEventListener('click', function() {
    currentTarifarioType = 'local';
    loadDestinations(currentTarifarioType);
    $('#mainModal').modal('hide');
    $('#destinationModal').modal('show');
});

function loadDestinations(type) {
    const destinations = {
        national: ['Cochabamba', 'Santa Cruz', 'Oruro', 'Sucre', 'Tarija', 'Potosi', 'Trinidad', 'Cobija', 'Provincias'],
        provincial: ['Camiri', 'Robore', 'Puerto Suarez', 'Riberalta', 'Rurrenabaque', 'Yacuiba', 'Tupiza', 'Villamontes', 'Bermejo'],
        local: ['Zona Central', 'El Alto', 'Zona Miraflores', 'Zona Sur', 'Zona Sopocachi']
    };

    const destinationButtons = document.getElementById('destinationButtons');
    destinationButtons.innerHTML = '';

    destinations[type].forEach(destination => {
        const button = document.createElement('button');
        button.textContent = destination;
        button.className = 'btn btn-secondary mb-2';
        button.addEventListener('click', function() {
            $('#destinationModal').modal('hide');
            showServiceModal(destination);
        });
        destinationButtons.appendChild(button);
    });
}

let selectedDestination; // Variable global para almacenar el destino seleccionado

function showServiceModal(destination) {
    const tarifarioType = currentTarifarioType;
    selectedDestination = destination;
    const destinationData = tarifarios[tarifarioType][destination];

    document.getElementById('serviceModalLabel').textContent = `Seleccionar Servicio para ${destination}`;
    $('#serviceModal').modal('show');

    // Si no hay datos, mostrar un mensaje amigable
    if (!destinationData) {
        document.getElementById('serviceCost').innerHTML = `
            <p>No hay datos disponibles para ${destination}.</p>
        `;
        document.getElementById('airService').style.display = 'none';
        document.getElementById('busService').style.display = 'none';
        // Ocultar el input si no hay datos
        document.getElementById('destino').style.display = 'none';
        return;
    }

    // Si es tarifario local
    if (tarifarioType === 'local') {
        document.getElementById('serviceCost').innerHTML = `
            <p>Costos para ${destination}:</p>
            <p>Costo: ${destinationData.normal}</p>
            <p>Tiempo de Entrega: ${destinationData.tiempo}</p>
        `;
        document.getElementById('airService').style.display = 'none';
        document.getElementById('busService').style.display = 'none';
        document.getElementById('finishButton').style.display = 'block';
    } else {
        // Mostrar servicios disponibles si los datos existen
        const busAvailable = destinationData.contraentrega !== "NO"; // Verificar si hay contraentrega
        document.getElementById('serviceCost').innerHTML = `
            <p>Costos para ${destination}:</p>
            <p>Por Avión - A domicilio: ${destinationData.normal || destinationData.costo}</p>
            <p>Por Bus - Recogo por transportadora: ${destinationData.costo2 || 'No disponible'}</p>
            <p>Tiempo de Entrega: ${destinationData.tiempo || "N/A"}</p>
        `;
        document.getElementById('airService').style.display = 'block';
        document.getElementById('busService').style.display = busAvailable ? 'block' : 'none';
        document.getElementById('finishButton').style.display = 'none'; 
    }
}

document.getElementById('finishButton').addEventListener('click', function() {
    const destinationData = tarifarios[currentTarifarioType][selectedDestination];
    let cost;
    
    if (destinationData) {
        // Si es tarifario local, usa el costo normal
        if (currentTarifarioType === 'local') {
            cost = destinationData.normal;
        } else {
            // Si es tarifario nacional o provincial, verifica qué servicio se eligió
            cost = document.getElementById('airService').style.display === 'block' ? destinationData.normal : destinationData.costo2;
        }
        
        // Mostrar los datos en el input
        document.getElementById('destino').value = `${selectedDestination}: ${cost}`;
        document.getElementById('destino').style.display = 'block'; // Mostrar el input
        $('#serviceModal').modal('hide'); // Cerrar el modal
    } else {
        alert("No se pudo guardar los datos, destino no encontrado.");
    }
});


document.getElementById('airService').addEventListener('click', function() {
    const destinationData = tarifarios[currentTarifarioType][selectedDestination];
    if (destinationData) {
        const cost = destinationData.normal || destinationData.costo;
        document.getElementById('destino').value = `${selectedDestination}: ${cost}`;
        document.getElementById('destino').style.display = 'block'; // Mostrar el input
        $('#serviceModal').modal('hide');
    } else {
        alert("Datos del destino no encontrados.");
        document.getElementById('destino').style.display = 'none'; // Esconder el input si no hay datos
    }
});

// Evento para la selección de "Por Bus"
document.getElementById('busService').addEventListener('click', function() {
    const destinationData = tarifarios[currentTarifarioType][selectedDestination];
    if (destinationData) {
        const cost = destinationData.contraentrega || destinationData.costo2;
        if (cost !== "NO") {
            document.getElementById('destino').value = `${selectedDestination}: ${cost}`;
            document.getElementById('destino').style.display = 'block'; // Mostrar el input
            $('#serviceModal').modal('hide');
        } else {
            alert("El servicio de contraentrega no está disponible para este destino.");
            document.getElementById('destino').style.display = 'none'; // Esconder el input si no hay datos
        }
    } else {
        alert("Datos del destino no encontrados.");
        document.getElementById('destino').style.display = 'none'; // Esconder el input si no hay datos
    }
});



document.addEventListener('DOMContentLoaded', function() {
    let productos = [];
    let totalCost = 0; // Aquí puedes manejar el total si es necesario

    const container = document.getElementById('productos-container');

 

});
/////////////////////

function updateProductsDisplay() {
    document.getElementById('productos').value = productos.map(p => `${p.nombre} - Bs${p.costo.toFixed(2)}`).join('\n');
    document.getElementById('cantidad_productos').value = productos.length; // Actualiza la cantidad de productos
    document.getElementById('monto_enviado_pagado').value = totalCost.toFixed(2);
}

            document.getElementById('add-product').addEventListener('click', function() {
                $('#productModal').modal('show');
            });

            let productos = []; // Asegúrate de inicializarlo aquí
let totalCost = 0;

document.getElementById('save-product').addEventListener('click', function() {
    const nombre = document.getElementById('producto_nombre').value;
    const costo = parseFloat(document.getElementById('producto_costo').value);

    if (nombre && !isNaN(costo)) {
        productos.push({ nombre, costo });
        totalCost += costo;
        updateProductsDisplay(); // Actualiza el campo de cantidad y productos
          // Mostrar el textarea al agregar el primer producto
          document.getElementById('productos').style.display = 'block';
        $('#productModal').modal('hide');
        document.getElementById('producto_nombre').value = '';
        document.getElementById('producto_costo').value = '';
    } else {
        Swal.fire({
            title: 'Error',
            text: 'Por favor, complete todos los campos de producto.',
            icon: 'error'
        });
    }
});


document.getElementById('edit-product').addEventListener('click', function() {
    mostrarProductosEnModal();
    $('#editProductModal').modal('show');
});

// Función para mostrar los productos en el modal de edición
function mostrarProductosEnModal() {
    const productList = document.getElementById('product-list');
    productList.innerHTML = '';

    productos.forEach((producto, index) => {
        const listItem = document.createElement('li');
        listItem.className = 'list-group-item';
        listItem.innerHTML = `
            <input type="text" class="form-control mb-1" value="${producto.nombre}" data-index="${index}" data-field="nombre">
            <input type="number" class="form-control" value="${producto.costo.toFixed(2)}" data-index="${index}" data-field="costo">
            <button type="button" class="btn btn-danger btn-sm float-right delete-product" data-index="${index}">Eliminar</button>
        `;
        productList.appendChild(listItem);
    });

    // Agregar evento a los botones de eliminar
    const deleteButtons = document.querySelectorAll('.delete-product');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const index = this.getAttribute('data-index');
            eliminarProducto(index);
        });
    });
}

function eliminarProducto(index) {
    totalCost -= productos[index].costo;
    productos.splice(index, 1);
    updateProductsDisplay();
    
    // Vuelve a mostrar los productos en el modal
    mostrarProductosEnModal();

    Swal.fire({
        title: 'Producto Eliminado',
        text: 'El producto ha sido eliminado correctamente.',
        icon: 'success'
    });
}


            document.getElementById('update-products').addEventListener('click', function() {
                const inputs = document.querySelectorAll('#product-list input');
                inputs.forEach(input => {
                    const index = input.getAttribute('data-index');
                    const field = input.getAttribute('data-field');
                    if (field === 'nombre') {
                        productos[index].nombre = input.value;
                    } else if (field === 'costo') {
                        const costo = parseFloat(input.value);
                        totalCost -= productos[index].costo;
                        productos[index].costo = costo;
                        totalCost += costo;
                    }
                });

                updateProductsDisplay();
                $('#editProductModal').modal('hide');
            });

            document.getElementById('submit-button').addEventListener('click', function(event) {
                event.preventDefault();
                const form = document.getElementById('pedido-form');
                const requiredFields = form.querySelectorAll('[required]');
                let allFieldsFilled = true;

                requiredFields.forEach(field => {
                    if (!field.value) {
                        allFieldsFilled = false;
                        field.classList.add('is-invalid');
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });

                if (!allFieldsFilled) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Por favor, complete todos los campos requeridos.',
                        icon: 'error'
                    });
                    return;
                }

                const formData = new FormData(form);
                let dataPreview = '<ul>';
                formData.forEach((value, key) => {
                    if (key !== '_token' && value) {
                        dataPreview += `<li><strong>${key.replace('_', ' ').toUpperCase()}:</strong> ${value}</li>`;
                    }
                });
                dataPreview += '</ul>';

                document.getElementById('data-preview').innerHTML = dataPreview;
                $('#confirmModal').modal('show');
            });

            document.getElementById('confirm-save').addEventListener('click', async function() {
    const loading = document.querySelector('.loading');
    loading.style.display = 'flex';
    
    const form = document.getElementById('pedido-form');
    
    // Generar PDF
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF();
    const dataContent = document.getElementById('data-preview');

    try {
        const canvas = await html2canvas(dataContent);
        const imgData = canvas.toDataURL('image/png');
        pdf.addImage(imgData, 'PNG', 10, 10, 190, 0);
        pdf.save('detalles_pedido.pdf'); // Descarga el PDF

        // Enviar el formulario después de descargar el PDF
        form.submit(); 
    } catch (err) {
        console.error('Error al capturar el contenido:', err);
        Swal.fire({
            title: 'Error',
            text: 'No se pudo generar el PDF. Intenta nuevamente.',
            icon: 'error'
        });
    } finally {
        loading.style.display = 'none'; // Ocultar el loading
        Swal.fire({
            title: '¡Éxito!',
            text: 'Los datos se han guardado correctamente.',
            icon: 'success'
        }).then(() => {
            // Espera un poco antes de recargar la página
            setTimeout(() => {
                window.location.reload(); // Recargar la página
            }, 2000); // 2 segundos de espera
        });
    }
});


document.getElementById('finish-and-refresh').addEventListener('click', function() {
    location.reload(); // Recarga la página
});

            document.getElementById('download-details').addEventListener('click', async function() {
                const { jsPDF } = window.jspdf;

                const pdf = new jsPDF();
                const dataContent = document.getElementById('data-preview');

                try {
                    const canvas = await html2canvas(dataContent);
                    const imgData = canvas.toDataURL('image/png');
                    pdf.addImage(imgData, 'PNG', 10, 10, 190, 0);
                    pdf.save('detalles_pedido.pdf');
                } catch (err) {
                    console.error('Error al capturar el contenido:', err);
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudo generar el PDF. Intenta nuevamente.',
                        icon: 'error'
                    });
                }
            });

    </script>
</body>
</html>
