@forelse($productos as $item)
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            @if($item->producto->fotos->isNotEmpty())
                <img 
                    src="{{ asset('storage/' . $item->producto->fotos->first()->foto) }}" 
                    class="card-img-top"
                    alt="{{ $item->producto->nombre }}"
                    style="height: 180px; object-fit: cover;"
                    loading="lazy"
                    onerror="this.closest('.card').querySelector('.placeholder-img').style.display='block'; this.remove();"
                >
            @endif

            @if($item->producto->fotos->isEmpty())
                <div class="placeholder-img bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                    <i class="fas fa-box-open fa-2x text-muted"></i>
                </div>
            @endif

            <div class="card-body d-flex flex-column">
                <h6 class="card-title">{{ $item->producto->nombre }}</h6>
                <p class="text-muted small flex-grow-1">{{ Str::limit($item->producto->descripcion, 60) }}</p>
                <p class="mb-1"><strong>Precio:</strong> Bs {{ number_format($item->producto->precio, 2) }}</p>
                <p class="mb-2"><strong>Stock:</strong> {{ $item->cantidad }}</p>

                <button 
                    type="button"
                    x-data
                    @click="$dispatch('agregar-al-carrito', {
                        id: {{ $item->producto->id }},
                        nombre: '{{ addslashes($item->producto->nombre) }}',
                        precio: {{ $item->producto->precio }},
                        stock: {{ $item->cantidad }}
                    })"
                    class="btn btn-sm {{ $item->cantidad > 0 ? 'btn-success' : 'btn-outline-secondary' }}"
                    :disabled="{{ $item->cantidad <= 0 ? 'true' : 'false' }}"
                >
                    {{ $item->cantidad > 0 ? 'Agregar' : 'Sin stock' }}
                </button>
            </div>
        </div>
    </div>
@empty
    <div class="col-12">
        <div class="alert alert-warning text-center py-4">
            <i class="fas fa-exclamation-circle me-2"></i>No hay productos en esta sucursal.
        </div>
    </div>
@endforelse