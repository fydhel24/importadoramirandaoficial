<div class="modal fade" id="venderModal" tabindex="-1" aria-labelledby="venderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="venderModalLabel">Completar Venta</h5>
                
            </div>
            <div class="modal-body">
                <form action="{{ route('control.fin') }}" method="POST" id="ventaForm">
                    @csrf
                    <input type="hidden" name="id_sucursal" id="modal-id-sucursal">
                    <input type="hidden" name="productos" id="modal-productos"> <!-- JSON con los productos -->
                    <input type="hidden" name="costo_total" id="modal-costo-total"> <!-- Costo total -->
            
                    <div class="mb-3">
                        <label for="nombre_cliente" class="form-label">Nombre del Cliente</label>
                        <input type="text" class="form-control" id="nombre_cliente" name="nombre_cliente" required>
                    </div>
            
                    <div class="mb-3">
                        <label for="ci" class="form-label">CI / NIT</label>
                        <input type="text" class="form-control" id="ci" name="ci">
                    </div>
            
                    <div class="mb-3">
                        <label for="total_sin_descuento" class="form-label">Total Sin Descuento (Bs)</label>
                        <input type="text" class="form-control" id="total_sin_descuento" readonly>
                    </div>
            
                    <div class="mb-3">
                        <label for="monto_pagado" class="form-label">Monto Pagado (Bs)</label>
                        <input type="number" class="form-control" id="monto_pagado" name="monto_pagado" required>
                    </div>
            
                    <!-- Campo para el Cambio -->
                    <div class="mb-3">
                        <label for="cambio" class="form-label">Cambio (Bs)</label>
                        <input type="text" class="form-control" id="cambio" readonly>
                    </div>
            
                    <div class="mb-3">
                        <label class="form-label">Método de Pago</label>
                        <div class="modal-radio">
                            <input type="radio" id="pago_efectivo" name="tipo_pago" value="efectivo" checked>
                            <label for="pago_efectivo">
                                <i class="fas fa-money-bill-wave"></i> Efectivo
                            </label>
                        </div>
                        <div class="modal-radio">
                            <input type="radio" id="pago_transferencia" name="tipo_pago" value="transferencia">
                            <label for="pago_transferencia">
                                <i class="fas fa-qrcode"></i> Transferencia QR
                            </label>
                            <button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#qrModal">Ver QR</button>
                        </div>
                    </div>
            
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Confirmar Venta</button>
                    </div>
                </form>
            </div>
            
        </div>
    </div>
</div>

<!-- Modal para mostrar el QR -->
<div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qrModalLabel">Transferencia QR</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img src="{{ asset('images/QR.jpeg') }}" alt="QR de Transferencia" class="img-fluid">
            </div>
        </div>
    </div>
</div>
<script>
  document.getElementById('ventaForm').addEventListener('submit', function (event) {
    event.preventDefault();

    const formData = new FormData(this);

    fetch('{{ route('control.generarNotaVenta') }}', {
        method: 'POST',
        body: formData,
    })
    .then(response => response.blob())
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'nota_venta.pdf';
        document.body.appendChild(a);
        a.click();
        a.remove();
        window.URL.revokeObjectURL(url);
    })
    .catch(error => {
        console.error('Error al generar el PDF:', error);
    });
});

    </script>
    
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const montoPagadoInput = document.getElementById("monto_pagado");
        const totalSinDescuentoInput = document.getElementById("total_sin_descuento");
        const cambioInput = document.getElementById("cambio");

        // Calcular el cambio automáticamente al ingresar el monto pagado
        montoPagadoInput.addEventListener("input", function () {
            const montoPagado = parseFloat(montoPagadoInput.value) || 0;
            const totalSinDescuento = parseFloat(totalSinDescuentoInput.value) || 0;

            // Calcular el cambio si el monto pagado es mayor o igual al total
            if (montoPagado >= totalSinDescuento) {
                const cambio = montoPagado - totalSinDescuento;
                cambioInput.value = cambio.toFixed(2);
            } else {
                cambioInput.value = "0.00"; // Mostrar cambio como 0 si el monto pagado es insuficiente
            }
        });
    });
</script>
