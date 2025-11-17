@extends('adminlte::page')

@section('title', 'Panel de Administración')

@section('content_header')
<div class="header-box p-4 mb-4">
    <div class="d-flex flex-column flex-md-row align-items-center justify-content-between">
        <div class="text-center text-md-start mb-3 mb-md-0">
            <h1 class="header-title mb-2">Listado de Semanas</h1>
            <p class="header-subtitle mb-0">¡Bienvenidos al sistema de Importadora Miranda!</p>
        </div>
        <div class="search-box">
            <input type="text" id="searchInput" class="form-control shadow-sm" placeholder="Buscar semanas...">
        </div>
    </div>
</div>


@stop

@section('content')
@php
    $ordered = $semanas->sortByDesc('created_at'); // Ordenar por fecha de creación descendente
@endphp

<div class="container mt-4">

    <!-- Contenedor de tarjetas -->
    <div class="row" id="semanasContainer">
        @foreach($ordered as $semana)
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-4 semana-card" data-nombre="{{ strtolower($semana->nombre) }}" 
             data-fecha="{{ strtolower($semana->fecha) }}">
            <div class="card border-light shadow-lg modern-card" style="border-radius: 15px; background-color: #fdfdfd;">
                <div class="card-body text-center">
                    <h5 class="card-title text-primary" style="font-weight: bold; font-size: 1.2rem;">{{ $semana->nombre }}</h5>
                    <p class="card-text text-muted" style="font-size: 1rem;">{{ $semana->fecha }}</p>
                    <a href="{{ route('orden.pedidos', $semana->id) }}" class="btn btn-primary modern-btn" style="border-radius: 25px; padding: 10px 20px; font-size: 1rem;">Ver Pedidos</a>
                </div>
                <div class="card-footer text-muted text-center" style="background-color: #f0f8ff; font-size: 0.9rem; border-radius: 0 0 15px 15px;">
                    Última actualización: {{ $semana->updated_at->format('d M Y') }}
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Paginador Dinámico -->
    <div class="d-flex justify-content-center mt-4">
        <nav id="paginationNav">
            <ul class="pagination" style="border-radius: 15px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                <!-- Aquí se llenará dinámicamente el paginador -->
            </ul>
        </nav>
    </div>
</div>

@section('css')
<style>
    /* Variables CSS para colores lila y azul */
    :root {
        --primary-purple: #8B5CF6;
        --secondary-purple: #A78BFA;
        --primary-blue: #3B82F6;
        --secondary-blue: #60A5FA;
        --gradient-main: linear-gradient(135deg, #8B5CF6 0%, #3B82F6 100%);
        --shadow-main: 0 10px 15px -3px rgba(139, 92, 246, 0.1), 0 4px 6px -2px rgba(139, 92, 246, 0.05);
        --shadow-hover: 0 20px 25px -5px rgba(139, 92, 246, 0.2);
    }

    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
    }

    /* Contenedor principal */
    .container {
        max-width: 1400px;
    }

    /* Campo de búsqueda mejorado */
    #searchInput {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 2px solid transparent;
        box-shadow: var(--shadow-main);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    #searchInput:focus {
        border-color: var(--primary-purple);
        box-shadow: var(--shadow-hover);
        transform: translateY(-2px);
        outline: none;
    }

    .header-box {
    background: linear-gradient(135deg, #8B5CF6 0%, #3B82F6 100%);
    border-radius: 20px;
    box-shadow: var(--shadow-main);
    color: white;
}

.header-title {
    font-size: 2.5rem;
    font-weight: 900;
    text-shadow: 2px 2px 5px rgba(0,0,0,0.3);
}

.header-subtitle {
    font-size: 1.1rem;
    font-weight: 500;
}

.search-box input {
    border-radius: 30px;
    padding: 12px 20px;
    font-size: 1rem;
    min-width: 250px;
    border: none;
    transition: all 0.3s ease;
}

.search-box input:focus {
    outline: none;
    transform: translateY(-2px) scale(1.02);
    box-shadow: var(--shadow-hover);
}


    /* Tarjetas modernas */
    .modern-card {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        height: 280px;
        display: flex;
        flex-direction: column;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .modern-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--gradient-main);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .modern-card:hover::before {
        transform: scaleX(1);
    }

    .modern-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: var(--shadow-hover);
    }

    .modern-card .card-body {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 1.5rem;
    }

    .modern-card .card-title {
        background: var(--gradient-main);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 1rem;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .modern-card .card-text {
        margin-bottom: 1.5rem;
        font-weight: 500;
    }

    /* Botón moderno */
    .modern-btn {
        background: var(--gradient-main);
        border: none;
        box-shadow: 0 8px 16px rgba(139, 92, 246, 0.3);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        font-weight: 600;
    }

    .modern-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s ease;
    }

    .modern-btn:hover::before {
        left: 100%;
    }

    .modern-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 30px rgba(139, 92, 246, 0.4);
        background: var(--gradient-main);
        border-color: transparent;
    }

    /* Footer de tarjeta */
    .modern-card .card-footer {
        background: linear-gradient(135deg, #f0f8ff 0%, #e6f3ff 100%);
        border-top: 1px solid rgba(139, 92, 246, 0.1);
        padding: 0.8rem;
    }

    /* Animaciones de entrada */
    .semana-card {
        animation: cardSlideIn 0.6s ease-out forwards;
        opacity: 0;
        transform: translateY(30px);
    }

    @keyframes cardSlideIn {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Paginación moderna */
    .pagination {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 50px;
        padding: 10px;
        box-shadow: var(--shadow-main);
        border: none;
    }

    .pagination .page-item .page-link {
        border: none;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 2px;
        background: transparent;
        color: var(--primary-purple);
        font-weight: 600;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .pagination .page-item .page-link::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: var(--gradient-main);
        border-radius: 50%;
        transform: scale(0);
        transition: transform 0.3s ease;
        z-index: -1;
    }

    .pagination .page-item .page-link:hover::before,
    .pagination .page-item.active .page-link::before {
        transform: scale(1);
    }

    .pagination .page-item .page-link:hover,
    .pagination .page-item.active .page-link {
        color: white;
        transform: translateY(-2px);
    }

    .pagination .page-item.disabled .page-link {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Responsive mejorado */
    @media (max-width: 1200px) {
        .col-xl-3 {
            flex: 0 0 25%;
            max-width: 25%;
        }
    }

    @media (max-width: 992px) {
        .col-lg-4 {
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
        }
    }

    @media (max-width: 768px) {
        .col-md-6 {
            flex: 0 0 50%;
            max-width: 50%;
        }
        
        .modern-card {
            height: auto;
            min-height: 260px;
        }
        
        #searchInput {
            width: 80% !important;
        }
    }

    @media (max-width: 576px) {
        .col-sm-12 {
            flex: 0 0 100%;
            max-width: 100%;
        }
        
        #searchInput {
            width: 95% !important;
        }
    }

    /* Efectos adicionales */
    .semana-card[style*="display: none"] {
        animation: cardSlideOut 0.3s ease-in forwards;
    }

    @keyframes cardSlideOut {
        to {
            opacity: 0;
            transform: translateY(-20px) scale(0.95);
        }
    }

    /* Animación de carga inicial */
    .semana-card:nth-child(1) { animation-delay: 0.1s; }
    .semana-card:nth-child(2) { animation-delay: 0.2s; }
    .semana-card:nth-child(3) { animation-delay: 0.3s; }
    .semana-card:nth-child(4) { animation-delay: 0.4s; }
    .semana-card:nth-child(5) { animation-delay: 0.5s; }
    .semana-card:nth-child(6) { animation-delay: 0.6s; }
    .semana-card:nth-child(7) { animation-delay: 0.7s; }
    .semana-card:nth-child(8) { animation-delay: 0.8s; }
</style>
@endsection

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchInput');
        const semanasContainer = document.getElementById('semanasContainer');
        const cards = Array.from(semanasContainer.getElementsByClassName('semana-card'));
        const paginationNav = document.getElementById('paginationNav');
        const itemsPerPage = 12; // 4 tarjetas por fila, 3 filas = 12 por página
        let currentPage = 1;
        let filteredCards = [...cards]; // Copia inicial para búsquedas

        // Función para mostrar tarjetas de la página actual
        function renderPage(page = 1) {
            const start = (page - 1) * itemsPerPage;
            const end = start + itemsPerPage;

            // Ocultar todas las tarjetas
            cards.forEach(card => card.style.display = 'none');

            // Mostrar solo las tarjetas en el rango actual
            const visibleCards = filteredCards.slice(start, end);
            visibleCards.forEach(card => card.style.display = '');

            // Actualizar paginador
            renderPagination(filteredCards.length, itemsPerPage, page);
        }

        // Función para renderizar el paginador
        function renderPagination(totalItems, perPage, current) {
            const totalPages = Math.ceil(totalItems / perPage);
            const paginationList = paginationNav.querySelector('ul');
            paginationList.innerHTML = ''; // Limpiar paginador

            if (totalPages <= 1) return; // No mostrar paginador si hay una página o menos

            // Botón "Anterior"
            const prevButton = document.createElement('li');
            prevButton.className = `page-item ${current === 1 ? 'disabled' : ''}`;
            prevButton.innerHTML = `<a class="page-link" href="#"><</a>`;
            prevButton.addEventListener('click', (e) => {
                e.preventDefault();
                if (current > 1) renderPage(current - 1);
            });
            paginationList.appendChild(prevButton);

            // Números de página
            for (let i = 1; i <= totalPages; i++) {
                const pageItem = document.createElement('li');
                pageItem.className = `page-item ${i === current ? 'active' : ''}`;
                pageItem.innerHTML = `<a class="page-link" href="#">${i}</a>`;
                pageItem.addEventListener('click', (e) => {
                    e.preventDefault();
                    renderPage(i);
                });
                paginationList.appendChild(pageItem);
            }

            // Botón "Siguiente"
            const nextButton = document.createElement('li');
            nextButton.className = `page-item ${current === totalPages ? 'disabled' : ''}`;
            nextButton.innerHTML = `<a class="page-link" href="#">></a>`;
            nextButton.addEventListener('click', (e) => {
                e.preventDefault();
                if (current < totalPages) renderPage(current + 1);
            });
            paginationList.appendChild(nextButton);
        }

        // Función para filtrar tarjetas
        function filterCards() {
            const filter = searchInput.value.toLowerCase();

            // Filtrar tarjetas
            filteredCards = cards.filter(card => {
                const nombre = card.dataset.nombre;
                const fecha = card.dataset.fecha;
                return nombre.includes(filter) || fecha.includes(filter);
            });

            // Actualizar paginador y mostrar página 1
            renderPage(1);
        }

        // Event Listener para el buscador
        searchInput.addEventListener('input', function () {
            filterCards();
        });

        // Renderizar la primera página al cargar
        renderPage(currentPage);

        // Efectos adicionales para el buscador
        searchInput.addEventListener('focus', function() {
            this.style.transform = 'translateY(-2px) scale(1.02)';
        });

        searchInput.addEventListener('blur', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
</script>
@endsection

@endsection
