<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Start your development with FoodHut landing page.">
    <meta name="author" content="Devcrud">
    <title>Pedidos a traves de BC&PLUS | Importadora Miranda</title>
    <link rel="icon" href="{{ asset('assets/imgs/main4.png') }}" type="image/x-icon">
   <!-- Font Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendors/themify-icons/css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/animate/animate.css') }}">

<!-- Cargar desde CDN, esto no necesita cambios -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Georgia:wght@400;700&display=swap" rel="stylesheet">

<!-- Bootstrap + FoodHut main styles -->
    <link rel="stylesheet" href="{{ asset('assets/css/foodhut.css') }}">

    <style>
        body {
            background: rgb(146,40,144);
            background
            : linear-gradient(90deg, rgba(146,40,144,1) 36%, rgba(21,32,176,1) 76%);
            margin: 0;
            height: 100vh;
            color: #fff; /* Default text color */
            font-family: 'Arial', sans-serif; /* Change to desired font */
        }
        h1, h3 {
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.6);
            margin: 20px 0;
            transition: transform 0.3s, color 0.3s;
        }
        h1 {
            font-size: 3em; /* Adjust as needed */
            animation: fadeInDown 1s ease; /* Animation for h1 */
        }
        h3 {
            font-size: 1.8em; /* Adjust as needed */
            animation: fadeInUp 1s ease; /* Animation for h3 */
            color: #FFF; /* Different color for emphasis */
        }
        .highlight {
            background-color: #FFF;
            padding: 10px;
            border-radius: 5px;
            animation: pulse 1.5s infinite;
            color: #000; /* Text color in highlight */
            font-weight: bold;
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            10% { transform: scale(1.05); }
        }
        p {
            font-size: 1.2em;
            color: #e0e0e0; /* Light grey for readability */
            line-height: 1.6;
            margin-top: 20px;
            animation: fadeIn 1.5s ease;
        }
        /* Estilos generales para la animación */
        .animated-table {
            opacity: 0;
            transform: translateY(50px);
            transition: opacity 0.3s ease-out, transform 0.3s ease-out;
        }

        .animated-table.visible {
            opacity: 1;
            transform: translateY(0);
        }
/* Establece el tamaño del contenedor del carrusel */
#headerCarousel {
    width: 100%; /* Puedes cambiar esto a un valor fijo si prefieres */
    height: 500px; /* Define una altura específica */
    overflow: hidden;
}

/* Ajuste para las imágenes del carrusel */
.carousel-inner {
    height: 100%; /* Las imágenes ocuparán todo el alto del contenedor */
}

.carousel-item {
    height: 100%;
}

.carousel-img {
    height: 100%;
    object-fit: cover; /* Ajusta la imagen para que se cubra todo el área sin deformarse */
    width: 100%; /* Ocupar el ancho completo del carrusel */
}

/* Ajustes para hacer que el carrusel sea responsivo si se usan valores fijos */
@media (max-width: 768px) {
    #headerCarousel {
        height: 300px; /* Reduce el tamaño del carrusel en pantallas más pequeñas */
    }
}
/* Ajustes para las imágenes del carrusel */
.carousel-img {
    transition: transform 1s ease, opacity 0.8s ease;
}

.carousel-item.active .carousel-img {
    transform: scale(1.1); /* Efecto zoom suave */
    opacity: 0.9; /* Efecto de transparencia sutil */
}

/* Estilos animados para los textos */
.carousel-caption h5 {
    font-size: 2rem;
    font-weight: bold;
    letter-spacing: 1px;
    text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.7);
}

.animated-text {
    opacity: 0;
    transition: opacity 0.5s ease, transform 0.8s ease;
}

.carousel-item.active .animated-text {
    opacity: 1;
    transform: translateY(-20px); /* Desplaza el texto hacia arriba */
}

/* Animaciones suaves */
@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInLeft {
    from {
        opacity: 0;
        transform: translateX(-50px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes fadeInRight {
    from {
        opacity: 0;
        transform: translateX(50px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Para activar animaciones */
.carousel-caption.animated {
    animation-duration: 1.2s;
    animation-fill-mode: both;
}
.nav-item {
    background: rgb(238,174,202);
background: radial-gradient(circle, rgba(238,174,202,1) 0%, rgba(148,187,233,1) 100%);border-radius: 5px; /* Opcional: agrega un borde redondeado */
    margin: 5px; /* Espacio entre los elementos */
}

.nav-link {
    color: white; /* Cambia el color del texto */
    padding: 10px 15px; /* Espaciado interno */
    text-decoration: none; /* Sin subrayado */
    display: block; /* Hace que el área clickeable sea más grande */
}

.nav-link.active {
    font-weight: bold; /* Resalta el enlace activo */
}

.custom-navbar {
    transition: background-color 0.3s, padding 0.3s; /* Transiciones suaves */
    padding: 15px 20px; /* Espaciado inicial */
}

.custom-navbar.scrolled {
    background-color: rgba(0, 0, 0, 0.8); /* Fondo al hacer scroll */
    padding: 10px 20px; /* Menos espaciado al hacer scroll */
}

.navbar-nav .nav-link {
    transition: color 0.3s; /* Transición de color */
}



.animated-toggler {
    display: flex;
    flex-direction: column;
    justify-content: space-around;
    height: 24px; /* Altura total de las líneas */
}

.animated-toggler span {
    display: block;
    width: 30px; /* Ancho de las líneas */
    height: 3px; /* Grosor de las líneas */
    background-color: white; /* Color de las líneas */
    transition: transform 0.3s; /* Transición suave */
}

/* Cambios al estado de 'toggle' */
.navbar-toggler.collapsed .animated-toggler span:nth-child(1) {
    transform: translateY(0) rotate(0);
}
.navbar-toggler.collapsed .animated-toggler span:nth-child(2) {
    opacity: 1;
}
.navbar-toggler.collapsed .animated-toggler span:nth-child(3) {
    transform: translateY(0) rotate(0);
}

/* Para pantallas más pequeñas */
@media (max-width: 768px) {
    .navbar-nav {
        text-align: center; /* Centramos el texto en móviles */
    }
}
/* Añade este CSS a tu archivo de estilos */
.custom-navbar {
    font-family: 'Georgia', serif; /* Cambia 'Georgia' por cualquier fuente elegante que prefieras */
}

.brand-txt {
    font-size: 1.5rem; /* Aumenta el tamaño del texto de la marca */
}
.button-hover {
    padding: 10px 20px;
    color: white;
    text-decoration: none;
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.button-hover:hover {
    background: rgb(20,78,139);
background: radial-gradient(circle, rgba(20,78,139,1) 0%, rgba(202,207,212,1) 100%);color: black;
    border-radius: 5px;
    transition: all 0.3s ease;
}


.nav-item {
    margin: 0 25px; /* Espaciado entre los elementos */
}



    </style>
</head>
<body data-spy="scroll" data-target=".navbar" data-offset="40" id="home">
    <!-- Navbar -->
   <nav class="custom-navbar navbar navbar-expand-lg navbar-dark fixed-top" id="navbar">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav">
            <li class="">
                <a class="nav-link button-hover"  style="color: white;" href="#home">Inicio</a>
            </li>
            <li class="">
                <a class="nav-link button-hover" style="color: white;" href="#about">Tarifario de Envios</a>
            </li>
        </ul>
        <a class="navbar-brand m-auto" href="#">
        <img src="{{ asset('assets/imgs/main4.png') }}" class="brand-img" alt="">
            <span class="brand-txt" style="color: white;">Importadora Miranda</span>
        </a>
        <ul class="navbar-nav">
            <li class="">
                <a class="nav-link button-hover" style="color: white;" href="#blog">Rangos de Envio</a>
            </li>
            <li class="">
                <a class="nav-link button-hover"  style="color: white;" href="#contact">Nuestras Sucursales</a>
            </li>   
        </ul>
    </div>
</nav>
    
    
    <!-- header -->
    <header>
        <div id="headerCarousel" class="carousel slide" data-ride="carousel">
          <ol class="carousel-indicators">
            <li data-target="#headerCarousel" data-slide-to="0" class="active"></li>
            <li data-target="#headerCarousel" data-slide-to="1"></li>
            <li data-target="#headerCarousel" data-slide-to="2"></li>
            <li data-target="#headerCarousel" data-slide-to="3"></li> <!-- Nuevo indicador -->
          </ol>
          <div class="carousel-inner">
        <div class="carousel-item active">
            <img src="{{ asset('assets/imgs/main.jpg') }}" class="d-block w-100 carousel-img" alt="Image 1">
            <div class="carousel-caption animated fadeInDown d-none d-md-block">
                <h5 class="animated-text">Sucursal 1</h5>
            </div>
        </div>
        <div class="carousel-item">
            <img src="{{ asset('assets/imgs/i2.jpg') }}" class="d-block w-100 carousel-img" alt="Image 2">
            <div class="carousel-caption animated fadeInUp d-none d-md-block">
                <h5 class="animated-text">Sucursal 2</h5>
            </div>
        </div>
        <div class="carousel-item">
            <img src="{{ asset('assets/imgs/i7.jpg') }}" class="d-block w-100 carousel-img" alt="Image 3">
            <div class="carousel-caption animated fadeInLeft d-none d-md-block">
                <h5 class="animated-text">Sucursal 3</h5>
            </div>
        </div>
        <div class="carousel-item"> <!-- Nueva imagen -->
            <img src="{{ asset('assets/imgs/i8.jpg') }}" class="d-block w-100 carousel-img" alt="Image 4">
            <div class="carousel-caption animated fadeInRight d-none d-md-block">
                <h5 class="animated-text">Sucursal 4</h5>
            </div>
        </div>
          </div>
          <a class="carousel-control-prev" href="#headerCarousel" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
          </a>
          <a class="carousel-control-next" href="#headerCarousel" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
          </a>
        </div>
      </header>
      

<BR>

    <!--  About Section  -->
    <div id="about" class="container-fluid wow fadeIn" data-wow-duration="1.5s">
        <center><h1>IMPORTADORA MIRANDA</h1></center>
        <center><h3>"A un Click del Producto que Necesitas"</h3></center>
        <center><h1 class="highlight">Pedidos a través de BC&PLUS</h1></center>
    </div>
    <div id="about" class="container-fluid wow fadeIn" id="about" data-wow-duration="1.5s">

    <h2 class="section-secondary-title mt-5"><center>TARIFARIO NACIONAL</center></h2>
      <table class="table table-striped text-center animated-table">
         <thead>
            <tr>
               <th scope="col">#</th>
               <th colspan="3"><center>SERVICIO NORMAL</center></th>
            </tr>
         </thead>
         <tbody>
            <tr>
               <th scope="row">Destino</th>
               <td scope="row">Primer Kilo</td>
               <td scope="row">Kilo Adicional</td>
               <td scope="row">Tiempo de Entrega</td>
            </tr>
            <tr>
               <th scope="row">Cochabamba</th>
               <td>15.00Bs</td>
               <td>10.00Bs</td>
               <td>24 Horas</td>
            </tr>
            <tr>
               <th scope="row">Santa Cruz</th>
               <td>15.00Bs</td>
               <td>10.00Bs</td>
               <td>24 Horas</td>
            </tr>
            <tr>
               <th scope="row">Oruro</th>
               <td>15.00Bs</td>
               <td>10.00Bs</td>
               <td>24 Horas</td>
            </tr>
            <tr>
               <th scope="row">Sucre</th>
               <td>15.00Bs</td>
               <td>10.00Bs</td>
               <td>24 - 48 Horas</td>
            </tr>
            <tr>
               <th scope="row">Tarija</th>
               <td>15.00Bs</td>
               <td>10.00Bs</td>
               <td>24 - 48 Horas</td>
            </tr>
            <tr>
               <th scope="row">Potosi</th>
               <td>15.00Bs</td>
               <td>10.00Bs</td>
               <td>24 Horas</td>
            </tr>
            <tr>
               <th scope="row">Trinidad</th>
               <td>40.00Bs</td>
               <td>20.00Bs</td>
               <td>24 - 48 Horas</td>
            </tr>
            <tr>
               <th scope="row">Cobija</th>
               <td>40.00Bs</td>
               <td>20.00Bs</td>
               <td>24 - 48 Horas</td>
            </tr>
            <tr>
               <th scope="row">Provincias</th>
               <td>40.00Bs</td>
               <td>20.00Bs</td>
               <td>48 - 72 Horas</td>
            </tr>
         </tbody>
      </table>

      <h2 class="section-secondary-title mt-5"><center>TARIFARIO PROVINCIAS</center></h2>
      <table class="table table-striped text-center animated-table">
         <thead>
            <tr>
               <th scope="col">DESTINOS</th>
               <th scope="col">Sobres y Paquetes</th>
               <th scope="col">Kg Adicional</th>
               <th scope="col">Tiempo de Transito</th>            
            </tr>
         </thead>
         <tbody>
            <tr>
               <th scope="row">Camiri</th>
               <td>40.00Bs</td>
               <td>20.00Bs</td>
               <td>72 Horas</td>
            </tr>
            <tr>
               <th scope="row">Robore</th>
               <td>40.00Bs</td>
               <td>20.00Bs</td>
               <td>72 Horas</td>
            </tr>
            <tr>
               <th scope="row">Puerto Suarez</th>
               <td>40.00Bs</td>
               <td>20.00Bs</td>
               <td>72 Horas</td>
            </tr>
            <tr>
               <th scope="row">Riberalta</th>
               <td>40.00Bs</td>
               <td>20.00Bs</td>
               <td>72 Horas</td>
            </tr>
            <tr>
               <th scope="row">Rurrenabaque</th>
               <td>40.00Bs</td>
               <td>20.00Bs</td>
               <td>72 Horas</td>
            </tr>
            <tr>
               <th scope="row">Yacuiba</th>
               <td>40.00Bs</td>
               <td>20.00Bs</td>
               <td>72 Horas</td>
            </tr>
            <tr>
               <th scope="row">Tupiza</th>
               <td>40.00Bs</td>
               <td>20.00Bs</td>
               <td>72 Horas</td>
            </tr>
            <tr>
               <th scope="row">Villamontes</th>
               <td>40.00Bs</td>
               <td>20.00Bs</td>
               <td>72 Horas</td>
            </tr>
            <tr>
               <th scope="row">Bermejo</th>
               <td>40.00Bs</td>
               <td>20.00Bs</td>
               <td>72 Horas</td>
            </tr>
         </tbody>
      </table>

      <h2 class="section-secondary-title mt-5"><center>TARIFARIO LOCAL</center></h2>
      <table class="table table-striped text-center animated-table">
         <thead>
            <tr>
               <th scope="col">#</th>
               <th colspan="3"><center>SERVICIO NORMAL</center></th>
            </tr>
         </thead>
         <tbody>
            <tr>
               <th scope="row">DESTINO</th>
               <th scope="row">Kilogramo</th>
               <th scope="row">Kilo Adicional</th>
               <th scope="row">Tiempo de Entrega</th>
            </tr>
            <tr>
               <th scope="row">Zona Central</th>
               <td>7.00Bs</td>
               <td>7.00Bs</td>
               <td>24 Horas</td>
            </tr>
            <tr>
               <th scope="row">El Alto</th>
               <td>15.00Bs</td>
               <td>7.00Bs</td>
               <td>24 Horas</td>
            </tr>
            <tr>
               <th scope="row">Zona Miraflores</th>
               <td>7.00Bs</td>
               <td>10.00Bs</td>
               <td>24 Horas</td>
            </tr>
            <tr>
               <th scope="row">Zona Sur</th>
               <td>7.00Bs</td>
               <td>10.00Bs</td>
               <td>24 Horas</td>
            </tr>
            <tr>
               <th scope="row">Zona Sopocachi</th>
               <td>7.00Bs</td>
               <td>7.00Bs</td>
               <td>24 Horas</td>
            </tr>
         </tbody>
      </table>

</div>


    <!-- BLOG Section  -->
    <div id="blog" class="container-fluid py-5 text-center wow fadeIn" style="
    background: rgb(75,178,180);
    background: linear-gradient(90deg, rgba(75,178,180,1) 0%, rgba(0,15,173,1) 34%, rgba(99,38,190,1) 61%, rgba(226,18,64,1) 100%);
">
        <h2 class="section-title py-5">Rangos de Envio</h2>
         <p class="highlight">Recuerda que los tiempos de entrega pueden variar segun la ubicacion y el tiempo de envio</p>
        <div class="row justify-content-center">
            <div class="col-sm-7 col-md-4 mb-5"><br>
            <ul class="nav justify-content-center">
    <li class="nav-item">
        <a class="nav-link active" id="pills-home-tab" data-toggle="pill" href="#foods" role="tab" aria-controls="pills-home" aria-selected="true"> La Paz - El Alto </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="pills-profile-tab" data-toggle="pill" href="#juices" role="tab" aria-controls="pills-profile" aria-selected="false"> Otros Departamentos </a>
    </li>
</ul>


            </div>
        </div>
        <div class="tab-content" id="pills-tabContent">
          <div class="tab-pane fade show active" id="foods" role="tabpanel" aria-labelledby="pills-home-tab">
    <div class="row justify-content-center">
        <div class="col-md-10 mx-auto"> <!-- Ajusta a col-md-10 o el tamaño que desees -->
            <div class="card bg-transparent border">
                <div class="card-body">
                    <h4 class="pt20 pb20">COSTO DE ENVIO</h4>
                    <h1 class="text-center mb-4">
 <a href="#" style="background: rgb(48,49,112); background: linear-gradient(276deg, rgba(48,49,112,1) 0%, rgba(30,25,182,1) 51%, rgba(49,35,110,1) 100%); color: #ffffff; text-decoration: none; padding: 10px 15px; border-radius: 5px; display: inline-block; margin-right: 10px;">
        La Paz - 7 Bs | El Alto - 15 Bs
    </a>

                    </h1>
                    <p class="text-white">Cuenta con Servicio de Delivery hasta su Domicilio</p>
                </div>
                <div class="map-container">
                    <iframe src="https://www.google.com/maps/d/embed?mid=1a6BAI32YHPh0Hc7GtBBlA3wRpmBlC85P&hl=es&ehbc=2E312F" width="100%" height="500"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>





            <div class="tab-pane fade" id="juices" role="tabpanel" aria-labelledby="pills-profile-tab">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-transparent border my-3 my-md-0">
                            <div class="card-body">
                                                               <h4 class="pt20 pb20">COSTO DE ENVIO</h4>
                                <h1 class="text-center mb-4"><a href="#" class="badge badge-primary" style="background-color: rgba(255, 255, 255, 0.2); color: white; text-decoration: none; padding: 10px 15px; border-radius: 5px; margin-right: 10px;">
        Santa Cruz - 15 Bs
    </a></h1>
                            
                              
                            </div>
                            <iframe src="https://www.google.com/maps/d/embed?mid=1MB2nGuQGyBhjAZUxXOoYhfO2hlYQYIA&ehbc=2E312F" width="640" height="480"></iframe>
                            
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-transparent border my-3 my-md-0">
                            <div class="card-body">
                                                               <h4 class="pt20 pb20">COSTO DE ENVIO </h4>
                                <h1 class="text-center mb-4"><a href="#" class="badge badge-primary" style="background-color: rgba(255, 255, 255, 0.2); color: white; text-decoration: none; padding: 10px 15px; border-radius: 5px; margin-right: 10px;">
        Tarija - 15 Bs
    </a></h1>
                            

                            </div>
                             <iframe src="https://www.google.com/maps/d/embed?mid=1w_sdHFToJy5caFqPtDIQfC8Q7mBp8M0&ehbc=2E312F" width="640" height="480"></iframe>
                            
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-transparent border my-3 my-md-0">
                            <div class="card-body">
                                                               <h4 class="pt20 pb20">COSTO DE ENVIO</h4> 
                                <h1 class="text-center mb-4"><a href="#" class="badge badge-primary" style="background-color: rgba(255, 255, 255, 0.2); color: white; text-decoration: none; padding: 10px 15px; border-radius: 5px; margin-right: 10px;">
        Oruro - 15 Bs
    </a></h1>
                                

                            </div>
                            <iframe src="https://www.google.com/maps/d/embed?mid=1JcqsIT3MbbzRaO4jehXq4thNulDzpvY&hl=es_419&ehbc=2E312F" width="640" height="480"></iframe>
                            
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-transparent border my-3 my-md-0">
                            <div class="card-body">
                                                               <h4 class="pt20 pb20">COSTO DE ENVIO</h4>
                                <h1 class="text-center mb-4"><a href="#" class="badge badge-primary" style="background-color: rgba(255, 255, 255, 0.2); color: white; text-decoration: none; padding: 10px 15px; border-radius: 5px; margin-right: 10px;">
        Cochabamba - 15 Bs
    </a></h1>
                                

                            </div>
                            <iframe src="https://www.google.com/maps/d/embed?mid=1dNsmJ-9aX8CZntkwMOgyTjzcXrzlp8Qe&ehbc=2E312F" width="640" height="480"></iframe>
                        </div>
                    </div>
                      
                </div>
            </div>
        </div>
    </div>

    
    <!-- CONTACT Section  -->
    <div id="contact" class="container-fluid text-light border-top wow fadeIn" style="background: rgb(0,0,0); background: linear-gradient(90deg, rgba(0,0,0,1) 0%, rgba(9,9,121,1) 24%, rgba(74,5,177,1) 59%, rgba(0,212,255,1) 100%);">
        <style>
            .custom-text {
                color: white; /* Color del texto */
                font-family: Arial, sans-serif; /* Fuente */
                font-size: 18px; /* Tamaño del texto */
            }
        
            .custom-text a {
                color: white; /* Color del enlace */
                text-decoration: none; /* Sin subrayado por defecto */
                transition: color 0.3s ease, text-decoration 0.3s ease; /* Transición suave */
            }
        
            .custom-text a:hover {
                color: #080808; /* Cambia el color del texto al pasar el mouse */
                text-decoration: wavy; /* Agrega subrayado al pasar el mouse */
            }
        </style>
       <div class="row">
        <div class="col-md-6 px-4 my-4">
            <iframe src="https://www.google.com/maps/d/embed?mid=1tL_B9SRx7HKvtBFDET2LeznuYcR18Qo&ehbc=2E312F" width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        </div>
    
        <div class="col-md-6 px-5 has-height-lg middle-items">
            <h3 style="color: white;">VISITA NUESTRAS SUCURSALES</h3>
            
            <div class="text-muted">
                <p class="custom-text">
                    <span class="ti-location-pin pr-3"></span>
                    <a href="https://www.tiktok.com/@importadoramiranda777/video/7397830715041221894"  target="_blank">SUCURSAL 1 - Caparazón Mall Center Planta Baja, Local Nº29 Garaje al lado de los cajeros, Pleno Puente Vita</a>
                </p>
                <p class="custom-text">
                    <span class="ti-location-pin pr-3"></span>
                    <a href="https://www.tiktok.com/@importadoramiranda777/video/7397869859373108485" target="_blank">SUCURSAL 2 - Plaza Bonita local 13 Cerca al puente vita, Av. Buenos Aires</a>
                </p>
                <p class="custom-text">
                    <span class="ti-location-pin pr-3"></span>
                    <a href="https://www.tiktok.com/@importadoramiranda777/video/7397944655444249862" target="_blank">SUCURSAL 3 - Galería las Vegas, Puesto Nº3</a>
                </p>
                <p class="custom-text">
                    <span class="ti-location-pin pr-3"></span>
                    <a href="https://www.tiktok.com/@importadoramiranda777/video/7397944655444249862" target="_blank">SUCURSAL 4 - Uyustus Frente al Banco Fie, Av. Buenos Aires</a>
                </p>
                
                <p class="custom-text">
                    <span class="ti-support pr-3"></span>
                    <a href="tel:+59170621016">+591 70621016</a>
                </p>
                <p class="custom-text">
                    <span class="ti-email pr-3"></span>
                    <a href="mailto:importadoramiranda.com">importadoramiranda.com</a>
                </p>
            </div>
        </div>
    </div>
    
            <CENTER><h3 class="mt-4">Síguenos en Nuestras Redes Sociales:</h3>
                <div>
                    <a href="https://www.facebook.com/profile.php?id=100063558189871" target="_blank" class="text-light pr-3"><i class="fab fa-facebook fa-2x"></i></a>
                    <a href="https://api.whatsapp.com/send?phone=59170621016" target="_blank" class="text-light pr-3"><i class="fab fa-whatsapp fa-2x"></i></a>
                    <a href="https://www.tiktok.com/@importadoramiranda777" target="_blank" class="text-light pr-3"><i class="fab fa-tiktok fa-2x"></i></a>
                    <a href="https://importadoramiranda777.sumerlabs.com/" target="_blank" class="text-light pr-3"><i class="fas fa-file-alt fa-2x"></i></a>
                    <a href="https://www.instagram.com/importadoramiranda777/" target="_blank" class="text-light pr-3"><i class="fab fa-instagram fa-2x"></i></a>
                </div></CENTER>
        </div>
    </div>
</div>

    
<div class="bg-dark text-light text-center border-top wow fadeIn" style="
background: rgb(75, 178, 180);
background: linear-gradient(90deg, rgba(75, 178, 180, 1) 7%, rgba(0, 15, 173, 1) 41%, rgba(99, 38, 190, 1) 61%, rgba(226, 18, 64, 1) 100%);
"> <p class="mb-0 py-3 text-muted small" style="color: white;">&copy; IMPORTADORA MIRANDA <script>document.write(new Date().getFullYear())</script> <i class="ti-heart text-danger"></i> By <i>DevD</i></p>
    </div>
    <!-- end of page footer -->

	<!-- core  -->
    <script src="{{ asset('assets/vendors/jquery/jquery-3.4.1.js') }}"></script>
<script src="{{ asset('assets/vendors/bootstrap/bootstrap.bundle.js') }}"></script>
<script src="{{ asset('assets/vendors/bootstrap/bootstrap.affix.js') }}"></script>
<script src="{{ asset('assets/vendors/wow/wow.js') }}"></script>
<!-- Google Maps -->
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCtme10pzgKSPeJVJrG1O3tjR6lk98o4w8&callback=initMap"></script>
<!-- FoodHut js -->
<script src="{{ asset('assets/js/foodhut.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const tables = document.querySelectorAll('.animated-table');
    
            // Función para verificar si un elemento está visible en pantalla
            const isElementInViewport = (el) => {
                const rect = el.getBoundingClientRect();
                return (
                    rect.top >= 0 &&
                    rect.left >= 0 &&
                    rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                    rect.right <= (window.innerWidth || document.documentElement.clientWidth)
                );
            };
    
            // Función para aplicar la clase 'visible' cuando el elemento esté en el viewport
            const checkVisibility = () => {
                tables.forEach(table => {
                    if (isElementInViewport(table)) {
                        table.classList.add('visible');
                    }
                });
            };
    
            // Chequear visibilidad al cargar la página
            checkVisibility();
    
            // Chequear visibilidad al hacer scroll
            window.addEventListener('scroll', checkVisibility);
        });
       // Función para animar el toggler
document.querySelector('.navbar-toggler').addEventListener('click', function() {
    var toggler = document.querySelector('.animated-toggler');
    toggler.classList.toggle('active');
});

// JavaScript para el cambio de navbar al hacer scroll
window.onscroll = function() {
    var navbar = document.getElementById("navbar");

    if (window.pageYOffset > 50) {
        navbar.classList.remove("navbar-top");
        navbar.classList.add("navbar-scroll");
    } else {
        navbar.classList.remove("navbar-scroll");
        navbar.classList.add("navbar-top");
    }
};

window.onload = function() {
    var navbar = document.getElementById("navbar");
    navbar.classList.add("navbar-top");
};
window.addEventListener('scroll', function() {
    const navbar = document.getElementById('navbar');
    if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
});



    </script>
    
</body>
</html>
