<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Verificación de Ventas - Cambio</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            padding: 24px;
            line-height: 1.5;
        }

        h2 {
            font-weight: 700;
            font-size: 1.5rem;
            color: #0f172a;
            margin-bottom: 24px;
            text-align: center;
        }

        #lista-ventas {
            max-width: 900px;
            margin: 0 auto;
        }

        .venta {
            background: #fff;
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 16px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
            position: relative;
            opacity: 0;
            transform: translateY(-8px);
            animation: fadeInSlide 0.4s forwards;
            cursor: pointer;
        }

        @keyframes fadeInSlide {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .venta.fuego {
            border-left: 4px solid #ef4444;
            background-color: #fef2f2;
        }

        .venta.caliente {
            border-left: 4px solid #f59e0b;
            background-color: #fffbeb;
        }

        .venta-header {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 12px;
            color: #0f172a;
        }

        .cambio-hora {
            display: flex;
            gap: 12px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .cambio-box,
        .hora-box {
            display: flex;
            flex-direction: column;
            padding: 8px 12px;
            border-radius: 8px;
            min-width: 110px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            flex: 1;
        }

        .cambio.positivo {
            color: #059669;
            font-weight: 700;
            font-size: 2rem;
            line-height: 1.2;
        }

        .cambio.negativo {
            color: #dc2626;
            font-weight: 700;
            font-size: 2rem;
            line-height: 1.2;
        }

        .cambio.cero {
            color: #64748b;
            font-weight: 600;
            font-size: 2rem;
            line-height: 1.2;
        }

        .hora-valor {
            font-weight: 700;
            font-size: 2rem;
            color: #0f172a;
            line-height: 1.2;
        }

        .vendedor-item {
            font-size: 0.95rem;
            margin: 8px 0;
        }

        .vendedor-label {
            font-weight: 600;
            color: #475569;
        }

        .vendedor-nombre {
            font-weight: 600;
            color: #1d4ed8;
        }

        .metodo-pago,
        .pagado-info {
            font-size: 0.9rem;
            color: #475569;
            margin: 6px 0;
        }

        .pagado-info {
            font-weight: 600;
            color: #0f172a;
        }

        /* === BOTÓN DE EXPANSIÓN AL FINAL === */
        .ver-productos {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #f1f5f9;
            color: #3b82f6;
            font-weight: 600;
            font-size: 0.95rem;
            width: fit-content;
        }

        .ver-productos-icon {
            width: 28px;
            height: 28px;
            stroke: #3b82f6;
            stroke-width: 2.5;
            transition: transform 0.3s ease;
            flex-shrink: 0;
        }

        .venta.expandida .ver-productos-icon {
            transform: rotate(180deg);
        }

        .productos-container {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease;
            margin-top: 12px;
            padding-left: 8px;
        }

        .productos-container.expandido {
            max-height: 400px;
        }

        .producto-item {
            margin: 5px 0;
            color: #475569;
            font-size: 0.925rem;
        }

        /* === TOAST === */
        #toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 8px;
            pointer-events: none;
        }

        .toast {
            background: white;
            color: #0f172a;
            padding: 10px 14px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
            animation: slideIn 0.25s forwards;
            pointer-events: auto;
            max-width: 280px;
            border-left: 3px solid #10b981;
        }

        @keyframes slideIn {
            from {
                transform: translateX(120%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
                transform: translateX(0);
            }

            to {
                opacity: 0;
                transform: translateX(120%);
            }
        }

        .toast-icon {
            width: 16px;
            height: 16px;
            color: #10b981;
        }

        .loading,
        .error {
            text-align: center;
            padding: 20px;
            color: #64748b;
        }

        .error {
            color: #dc2626;
        }
    </style>
</head>

<body>
    <h2>Verificación de Ventas</h2>
    <div id="lista-ventas"></div>
    <div id="toast-container"></div>

    <script>
        let ultimaData = null;
        let intervalId = null;
        let primeraCarga = true;

        // === SONIDO DE NOTIFICACIÓN ===
        function reproducirSonidoToast() {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioCtx.createOscillator();
                const gainNode = audioCtx.createGain();

                oscillator.type = 'sine';
                oscillator.frequency.value = 800; // Hz
                gainNode.gain.value = 0.2;

                oscillator.connect(gainNode);
                gainNode.connect(audioCtx.destination);

                oscillator.start();
                gainNode.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.3);
                oscillator.stop(audioCtx.currentTime + 0.3);
            } catch (e) {
                console.warn("No se pudo reproducir sonido:", e);
            }
        }

        function obtenerEstadoPorTiempo(fechaISO) {
            if (!fechaISO) return 'neutro';
            const ahora = new Date();
            const creada = new Date(fechaISO);
            const diffMin = (ahora - creada) / (1000 * 60);
            if (diffMin < 5) return 'fuego';
            if (diffMin < 10) return 'caliente';
            return 'neutro';
        }

        function formatearTiempo(fechaISO) {
            if (!fechaISO) return '—';
            const d = new Date(fechaISO);
            const h = String(d.getHours()).padStart(2, '0');
            const m = String(d.getMinutes()).padStart(2, '0');
            return `${h}:${m}`;
        }

        function mostrarToast(pagado, idVenta) {
            const container = document.getElementById('toast-container');
            if (container.children.length >= 3) {
                const oldest = container.firstElementChild;
                if (oldest) oldest.style.animation = 'fadeOut 0.4s forwards';
                setTimeout(() => {
                    if (oldest && oldest.parentNode) oldest.parentNode.removeChild(oldest);
                }, 400);
            }

            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.innerHTML = `
                <svg class="toast-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Venta #${idVenta}:<br>Pagado Bs. ${pagado.toFixed(2)}
            `;
            container.appendChild(toast);

            // === REPRODUCIR SONIDO ===
            reproducirSonidoToast();

            // === Mostrar 5 segundos antes de desvanecer ===
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.style.animation = 'fadeOut 0.4s forwards';
                    setTimeout(() => {
                        if (toast.parentNode) toast.parentNode.removeChild(toast);
                    }, 400);
                }
            }, 5000); // ← 5 segundos
        }

        function renderVenta(venta) {
            const efectivo = parseFloat(venta.efectivo) || 0;
            const qr = parseFloat(venta.qr) || 0;
            const pagado = parseFloat(venta.pagado) || 0;
            const total = parseFloat(venta.costo_total) || 0;
            const cambioReal = pagado - (efectivo + qr);
            const cambio = cambioReal < 0 ? 0 : cambioReal;

            const estadoTiempo = obtenerEstadoPorTiempo(venta.created_at);
            const tiempoFormateado = formatearTiempo(venta.created_at);
            const nombreVendedor = venta.user?.name || '—';

            const metodoPagoParts = [];
            if (efectivo > 0) metodoPagoParts.push(`Efectivo: Bs. ${efectivo.toFixed(2)}`);
            if (qr > 0) metodoPagoParts.push(`QR: Bs. ${qr.toFixed(2)}`);
            const metodoPagoStr = metodoPagoParts.length ? metodoPagoParts.join(' • ') : 'Sin pago registrado';

            let claseCambio = 'cero';
            if (cambio > 0) claseCambio = 'positivo';
            else if (cambio < 0) claseCambio = 'negativo';

            return `
                <div class="venta ${estadoTiempo === 'neutro' ? '' : estadoTiempo}">
                    <div class="venta-header">Venta #${venta.id || 'N/A'} — ${venta.nombre_cliente || 'Cliente'}</div>
                    <div class="cambio-hora">
                        <div class="cambio-box">
                            <span style="font-size:0.8rem;color:#64748b;">Cambio Bs.</span>
                            <span class="cambio ${claseCambio}">${cambio.toFixed(2)}</span>
                        </div>
                        <div class="hora-box">
                            <span style="font-size:0.8rem;color:#64748b;">Hora</span>
                            <span class="hora-valor">${tiempoFormateado}</span>
                        </div>
                    </div>
                    <div class="vendedor-item">
                        <span class="vendedor-label">Vendedor:</span>
                        <span class="vendedor-nombre">${nombreVendedor}</span>
                    </div>
                    <div class="metodo-pago">${metodoPagoStr}</div>
                    <div class="pagado-info">Pagado: Bs. ${pagado.toFixed(2)}</div>
                    <div class="ver-productos">
                        <svg class="ver-productos-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                        Ver productos
                    </div>
                    <div class="productos-container">
                        <ul>${
                            Array.isArray(venta.venta_productos) && venta.venta_productos.length > 0
                                ? venta.venta_productos.map(vp => {
                                      const nombre = vp.producto?.nombre || 'Producto eliminado';
                                      const cantidad = vp.cantidad || 0;
                                      const precio = parseFloat(vp.precio_unitario) || 0;
                                      return `<li class="producto-item">${cantidad} × ${nombre} — Bs. ${precio.toFixed(2)}</li>`;
                                  }).join('')
                                : '<li class="producto-item">Sin productos</li>'
                        }</ul>
                    </div>
                </div>
            `;
        }

        function actualizarVentasNuevas(nuevasVentas) {
            const contenedor = document.getElementById('lista-ventas');
            const idsExistentes = new Set(
                Array.from(contenedor.querySelectorAll('.venta-header'))
                    .map(el => el.textContent.match(/Venta #(\d+)/)?.[1])
                    .filter(Boolean)
            );

            const nuevas = nuevasVentas.filter(v => !idsExistentes.has(String(v.id)));
            if (nuevas.length > 0) {
                nuevas.reverse().forEach(venta => {
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = renderVenta(venta);
                    const nodo = tempDiv.firstElementChild;
                    contenedor.insertBefore(nodo, contenedor.firstChild);
                    if (!primeraCarga) {
                        const pagado = parseFloat(venta.pagado) || 0;
                        mostrarToast(pagado, venta.id);
                    }
                });

                const todos = contenedor.querySelectorAll('.venta');
                if (todos.length > 20) {
                    for (let i = 20; i < todos.length; i++) {
                        todos[i].remove();
                    }
                }
            } else if (ultimaData === null) {
                contenedor.innerHTML = nuevasVentas.map(v => renderVenta(v)).join('');
            }
            primeraCarga = false;
        }

        function compararVentas(actual, previa) {
            if (!Array.isArray(previa)) return true;
            if (actual.length !== previa.length) return true;
            for (let i = 0; i < actual.length; i++) {
                if (actual[i].id !== previa[i].id || actual[i].created_at !== previa[i].created_at) {
                    return true;
                }
            }
            return false;
        }

        document.addEventListener('click', e => {
            const venta = e.target.closest('.venta');
            if (venta) {
                venta.classList.toggle('expandida');
                venta.querySelector('.productos-container').classList.toggle('expandido');
            }
        });

        function cargarVentas() {
            axios.get('{{ route('verificacion.validar') }}')
                .then(response => {
                    const ventas = Array.isArray(response.data) ? response.data : [];
                    if (!compararVentas(ventas, ultimaData)) return;
                    actualizarVentasNuevas(ventas);
                    ultimaData = ventas.map(v => ({ ...v }));
                })
                .catch(error => {
                    console.error('Error al cargar ventas:', error);
                    const contenedor = document.getElementById('lista-ventas');
                    if (!contenedor.querySelector('.error')) {
                        contenedor.innerHTML = '<p class="error">Error al cargar los datos.</p>';
                    }
                });
        }

        document.addEventListener('DOMContentLoaded', () => {
            cargarVentas();
            intervalId = setInterval(cargarVentas, 5000);
        });
    </script>
</body>

</html>
