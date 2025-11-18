document.addEventListener('alpine:init', () => {
    Alpine.data('productosApp', (sucursalId) => ({
        query: '',
        sugerencias: [],
        inputFocused: false,
        debounceTimer: null,

        init() {
            this.loadProductos();
            this.$watch('query', () => {
                if (this.query.length < 2) {
                    this.sugerencias = [];
                }
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => {
                    this.loadProductos(1);
                    if (this.query.length >= 2) {
                        this.buscarSugerencias();
                    }
                }, 350);
            });
        },

        async loadProductos(page = 1) {
            const url = new URL(`/ventas/productos/${sucursalId}`, window.location.origin);
            if (this.query.length >= 2) {
                url.searchParams.set('search', this.query);
            }
            url.searchParams.set('page', page);

            try {
                const response = await axios.get(url.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                document.getElementById('productos-list').innerHTML = response.data.html;
                document.getElementById('pagination-links').innerHTML = response.data.pagination;
            } catch (err) {
                console.error('Error al cargar productos:', err);
            }
        },

        async buscarSugerencias() {
            try {
                const res = await axios.get(`/ventas/sugerencias/${sucursalId}`, {
                    params: { q: this.query }
                });
                this.sugerencias = res.data;
            } catch (err) {
                console.error('Error en sugerencias:', err);
            }
        },

        selectSuggestion(item) {
            this.query = item.nombre;
            this.sugerencias = [];
            this.inputFocused = false;
        }
    }));
});

// Soporte para paginación AJAX
document.addEventListener('click', (e) => {
    const link = e.target.closest('.pagination a');
    if (!link) return;
    e.preventDefault();
    const url = new URL(link.href);
    const page = url.searchParams.get('page') || 1;
    const alpineEl = document.querySelector('[x-data]');
    alpineEl?.__x?.loadProductos(parseInt(page));
});