document.addEventListener("alpine:init", () => {
    Alpine.store("carrito", {
        items: (() => {
            try {
                const data = localStorage.getItem("carrito_ventas");
                return data ? JSON.parse(data) : [];
            } catch (e) {
                return [];
            }
        })(),

        guardar() {
            localStorage.setItem("carrito_ventas", JSON.stringify(this.items));
        },

        get totalItems() {
            return this.items.reduce((sum, item) => sum + item.cantidad, 0);
        },

        agregar(producto) {
            if (producto.stock <= 0) return;

            const item = this.items.find((i) => i.id === producto.id);
            if (item) {
                item.cantidad++;
            } else {
                this.items.push({
                    id: producto.id,
                    nombre: producto.nombre,
                    precio: producto.precio,
                    cantidad: 1,
                });
            }
            this.guardar();

            // 🔊 Sonido
            const audio = new Audio("/sounds/timbre.mp3"); // Asegúrate de tener este archivo
            audio.play().catch((e) => console.warn("Audio play failed:", e));

            // 🍞 Notificación tipo toast (reemplaza Swal por Sonner u otro si usas)
            Swal.fire({
                title: "Producto agregado",
                text: `${producto.nombre} agregado al carrito`,
                icon: "success",
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true,
            });
        },

        quitar(id) {
            this.items = this.items.filter((i) => i.id !== id);
            this.guardar();
        },
    });

    Alpine.data("productosApp", (sucursalId) => ({
        query: "",
        sugerencias: [],
        inputFocused: false,
        debounceTimer: null,

        init() {
            // 🚫 Ya NO inicializamos ni limpiamos por sucursal aquí
            this.loadProductos();
            this.$watch("query", () => {
                if (this.query.length < 2) this.sugerencias = [];
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => {
                    this.loadProductos(1);
                    if (this.query.length >= 2) this.buscarSugerencias();
                }, 350);
            });

            this.$el.addEventListener("agregar-al-carrito", (e) => {
                Alpine.store("carrito").agregar(e.detail);
            });
        },

        async loadProductos(page = 1) {
            const url = new URL(
                `/ventas/productos/${sucursalId}`,
                window.location.origin
            );
            if (this.query.length >= 2)
                url.searchParams.set("search", this.query);
            url.searchParams.set("page", page);

            try {
                const response = await axios.get(url.toString(), {
                    headers: { "X-Requested-With": "XMLHttpRequest" },
                });
                document.getElementById("productos-list").innerHTML =
                    response.data.html;
                document.getElementById("pagination-links").innerHTML =
                    response.data.pagination;
            } catch (err) {
                console.error("Error al cargar productos:", err);
            }
        },

        async buscarSugerencias() {
            try {
                const res = await axios.get(
                    `/ventas/sugerencias/${sucursalId}`,
                    { params: { q: this.query } }
                );
                this.sugerencias = res.data;
            } catch (err) {
                console.error("Error en sugerencias:", err);
            }
        },

        selectSuggestion(item) {
            this.query = item.nombre;
            this.sugerencias = [];
            this.inputFocused = false;
        },
    }));
});

document.addEventListener("click", (e) => {
    const link = e.target.closest(".pagination a");
    if (!link) return;
    e.preventDefault();
    const url = new URL(link.href);
    const page = url.searchParams.get("page") || 1;
    const alpineEl = document.querySelector("[x-data]");
    alpineEl?.__x?.loadProductos(parseInt(page));
});
