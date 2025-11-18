@forelse($productos as $item)
    <div class="col-md-4 mb-4">
        <div class="position-relative rounded-4 overflow-hidden shadow-sm border"
            style="width: 100%; height: 470px; background: white;">
            @if($item->producto->fotos->isNotEmpty())
                <div class="w-100 h-100 position-absolute top-0 start-0 opacity-15"
                    style="background-image: url('{{ asset('storage/' . $item->producto->fotos->first()->foto) }}'); background-size: cover; background-position: center; z-index: 0;">
                </div>
            @endif

            <!-- Cinta con gradiente -->
            <div class="position-absolute top-0 start-0 w-100 py-2 text-center" 
                style="background: linear-gradient(135deg, #ffffff 0%, #cc9efd 25%, #a582ff 50%, #3b78d8 75%, #3b78d8 100%); z-index: 2;">
                <h5 class="text-white fw-bold mb-0" style="font-size: 1.6rem; text-shadow: 0 1px 3px rgba(0,0,0,0.5);">
                    {{ $item->producto->nombre }}
                </h5>
            </div>

            <div class="position-relative z-1 d-flex flex-column justify-content-between h-100 pt-16 px-4 pb-4">
                <div class="d-flex justify-content-end">
                    <div class="d-flex flex-column align-items-end gap-3">
                        <span class="badge rounded-pill px-4 py-2" style="background-color: #dbeafe; color: #1d4ed8; font-weight: 700; font-size: 1.1rem;">
                            {{ $item->cantidad }} en stock
                        </span>
                        <span class="badge rounded-pill px-4 py-2" style="background-color: #e0f2fe; color: #0ea5e9; font-weight: 700; font-size: 1.1rem;">
                            {{ $item->producto->categoria?->categoria ?? '—' }}
                        </span>
                        <span class="badge rounded-pill px-4 py-2" style="background-color: #cffafe; color: #0891b2; font-weight: 700; font-size: 1.1rem;">
                            {{ $item->producto->marca?->marca ?? '—' }}
                        </span>
                    </div>
                </div>

                <div class="mt-auto">
                    <p class="badge rounded-pill px-4 py-2 mb-3" style="background-color: #bae6fd; color: #0284c7; font-weight: 700; font-size: 1.25rem; width: fit-content;">
                        Bs. {{ number_format($item->producto->precio, 2, ',', '.') }}
                    </p>
                    <button 
                        type="button"
                        x-data
                        @click="$dispatch('agregar-al-carrito', {
                            id: {{ $item->producto->id }},
                            nombre: '{{ addslashes($item->producto->nombre) }}',
                            precio: {{ $item->producto->precio }},
                            stock: {{ $item->cantidad }}
                        })"
                        class="btn fw-bold text-white rounded-pill w-100 py-3 position-relative overflow-hidden"
                        style="
                            background: linear-gradient(135deg, #ffffff 0%, #cc9efd 25%, #a582ff 50%, #3b78d8 75%, #3b78d8 100%);
                            border: none;
                            font-size: 1.3rem;
                            font-weight: 800;
                            letter-spacing: 0.5px;
                            transition: transform 0.3s ease, box-shadow 0.3s ease;
                        "
                        onmouseover="this.style.transform='scale(1.03)'; this.style.boxShadow='0 6px 16px rgba(59, 120, 216, 0.4)';"
                        onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none';"
                        {{ $item->cantidad <= 0 ? 'disabled' : '' }}
                    >
                        {{ $item->cantidad > 0 ? 'VENDER' : 'SIN STOCK' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="col-12">
        <div class="text-center py-5">
            <div class="bg-light rounded-3 d-inline-flex p-4 mb-3">
                <i class="fas fa-inbox fa-2x text-muted"></i>
            </div>
            <p class="text-muted mb-0" style="font-size: 1.25rem;">No hay productos en esta sucursal.</p>
        </div>
    </div>
@endforelse

<style scoped>
.z-1 { z-index: 1; }
.pt-16 { padding-top: 4rem; }
</style>