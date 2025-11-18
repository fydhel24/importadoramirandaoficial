<div class="row mb-3">
    <div class="col-md-6">
        <a href="{{ route('ventas.index') }}" class="btn btn-secondary">← Volver</a>
    </div>
    <div class="col-md-6 text-right">
        <div class="position-relative">
            <input 
                type="text"
                x-model="query"
                @focus="inputFocused = true"
                @blur="setTimeout(() => inputFocused = false, 150)"
                class="form-control"
                placeholder="Buscar productos..."
                autocomplete="off"
            >
            <div 
                x-show="sugerencias.length > 0 && inputFocused"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="position-absolute bg-white border rounded shadow w-100 mt-1"
                style="max-height: 250px; overflow-y: auto; z-index: 1050;">
                <template x-for="item in sugerencias" :key="item.id">
                    <div 
                        class="d-flex align-items-center p-2 border-bottom cursor-pointer"
                        style="height: 44px;"
                        @click="selectSuggestion(item)">
                        <span class="text-truncate" x-text="item.nombre"></span>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>