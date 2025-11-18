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
                    onerror="this.closest('.card').querySelector('.placeholder-img').style.display='block'; this.remove();">
            @endif

            @if($item->producto->fotos->isEmpty())
                <div class="placeholder-img bg-light text-center py-5" style="height: 180px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-box-open fa-2x text-muted"></i>
                </div>
            @endif

            <div class="card-body d-flex flex-column">
                <h6 class="card-title">{{ $item->producto->nombre }}</h6>
                <p class="text-muted small flex-grow-1">{{ Str::limit($item->producto->descripcion, 60) }}</p>
                <p class="mb-1"><strong>Precio:</strong> Bs {{ number_format($item->producto->precio, 2) }}</p>
                <p class="mb-0"><strong>Stock:</strong> {{ $item->cantidad }}</p>
            </div>
        </div>
    </div>
@empty
    <div class="col-12">
        <div class="alert alert-warning text-center">
            <i class="fas fa-exclamation-circle"></i> No hay productos en esta sucursal.
        </div>
    </div>
@endforelse