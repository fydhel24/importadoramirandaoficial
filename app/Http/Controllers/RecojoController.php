<?php

namespace App\Http\Controllers;

use App\Models\Cupo;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\Sucursale;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Http\Request;
use Fpdf\Fpdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecojoController extends Controller
{
    public function index()
    {
        $ventas = Venta::where('estado', 'recojo')
            ->with(['ventaProductos.producto']) // carga productos y sus datos
            ->get();


        $sucursales = Sucursale::all();
        $usuarios = User::all();
        $cupos = Cupo::all();
        return view('ventarecojo.recojo', compact('ventas', 'sucursales', 'usuarios', 'cupos'));
    }
    public function indexmoderno()
    {
        $sucursales = Sucursale::all();
        $usuarios = User::all();
        $cupos = Cupo::all();
        return view('ventarecojo.recojomoderno', compact('sucursales', 'usuarios', 'cupos'));
    }
    public function indexmodernocola()
    {
        $sucursales = Sucursale::all();
        $usuarios = User::all();
        $cupos = Cupo::all();
        return view('ventarecojo.recojo1moderno', compact('sucursales', 'usuarios', 'cupos'));
    }
    public function getVentasModernas()
    {
         $ventas = Venta::where('estado', 'RESERVA')
            ->with(['ventaProductos.producto']) // carga productos y sus datos
            ->get();


        // Devuelve los datos en formato JSON
        return response()->json([
            'ventas' => $ventas
        ]);
    }

    public function ver()
    {
        $ventas = Venta::where('estado', 'recojo')
            ->with(['ventaProductos.producto']) // carga productos y sus datos
            ->get();


        // Devuelve los datos en formato JSON
        return response()->json([
            'ventas' => $ventas
        ]);
    }

    public function update(Request $request, Venta $venta)
    {
        // **INICIALIZAR TODAS LAS VARIABLES AL PRINCIPIO**
        $efectivo = 0;
        $qr = 0;
        $pagado = 0;
        $pagadoqr = 0; // <--- ¡AQUÍ ESTÁ LA SOLUCIÓN! Inicializa $pagadoqr a 0

        // Determinar valores según el tipo de pago
        $tipoPago = $request->tipo_pago;

        if ($tipoPago === 'Efectivo') {
            $efectivo = floatval($request->efectivo);
            $pagado = $efectivo;
            $qr = 0; // Asegúrate de que QR sea 0 si el pago es solo Efectivo
        } elseif ($tipoPago === 'QR') {
            $qr = floatval($request->qr);
            $pagado = $qr;
            $efectivo = 0; // Asegúrate de que Efectivo sea 0 si el pago es solo QR
            $pagadoqr = $qr; // Asigna el valor de QR para el comprobante
        } elseif ($tipoPago === 'Efectivo y QR') {
            $efectivo = floatval($request->efectivo);
            $qr = floatval($request->qr);
            $pagado = $efectivo + $qr; // <--- CORRECCIÓN LÓGICA: Suma ambos para el total pagado
            $pagadoqr = $qr; // Asigna el valor de QR para el comprobante
        }
        $costoTotal = $venta->costo_total;
        $cambio = $pagado > $costoTotal ? round($pagado - $costoTotal, 2) : 0;

        // 1. Actualizar la venta sin modificar 'costo_total'
        $venta->update([
            'fecha'          => $request->input('fecha', now()), // Considera si 'fecha' debe actualizarse a 'now()' o mantener la original
            'nombre_cliente' => $request->nombre_cliente,
            'ci'             => $request->ci,
            'descuento'      => $request->descuento, // Asegúrate de que 'descuento' se envíe desde el formulario si es editable
            'tipo_pago'      => $tipoPago,
            'efectivo'       => $efectivo,
            'qr'             => $qr,
            'pagado'         => $pagado,
            'estado'         => 'NORMAL' // 👈 Se fuerza automáticamente. Considera si esto es lo deseado o si debería ser 'Completado'/'Pendiente' basado en $pagado vs $costoTotal
        ]);

        // 2. Construir parámetros para el comprobante
        // Cargar la relación ventaProductos después de la actualización si es necesario para asegurar los datos más recientes
        $venta->load('ventaProductos.producto'); // Carga la relación para asegurar que 'producto' esté disponible
        $productos = $venta->ventaProductos->map(function ($vp) { // Usa $venta->ventaProductos directamente
            return [
                'id' => $vp->producto->id ?? null, // Agrega null coalescing para productos eliminados
                'cantidad' => $vp->cantidad,
                'precio' => $vp->precio_unitario,
                'total' => $vp->cantidad * $vp->precio_unitario
            ];
        })->toArray();

        // 3. Crear query string
        $params = [
            'nombre_cliente' => $venta->nombre_cliente,
            'costo_total'    => $venta->costo_total,
            'productos'      => json_encode($productos),
            'ci'             => $venta->ci,
            'descuento'      => $venta->descuento,
            'pagado'         => $pagado,
            'pagadoqr'       => $pagadoqr, // Ahora $pagadoqr siempre estará definida
            'cambio'         => $cambio,
            'tipo_pago'      => $venta->tipo_pago,
            'garantia'       => $venta->garantia ?? null, // Agrega null coalescing si 'garantia' puede ser nula
            'id_sucursal'    => $venta->id_sucursal,
            'id_user'        => $venta->id_user,
        ];
        $query = http_build_query($params);
        $pdfUrl = route('ventas.nota') . '?' . $query;

        // Si es AJAX
        if ($request->ajax()) {
            // **IMPORTANTE: Devuelve también el objeto 'venta' actualizado para que el frontend pueda refrescar la tabla**
            return response()->json([
                'success' => true, // Añade 'success: true' para que el JS sepa que fue exitoso
                'message' => 'Venta actualizada exitosamente.',
                'venta' => $venta->fresh(), // Devuelve la venta recién actualizada de la DB
                'pdfUrl' => $pdfUrl, // Envía el URL del PDF generado
            ]);
        }

        // Si no es AJAX, redireccionar normalmente
        return redirect()->to($pdfUrl);
    }

    public function nota(Request $request)
    {
        // Obtener los parámetros de la consulta
        $nombreCliente = $request->query('nombre_cliente');
        $costoTotal = $request->query('costo_total');
        $productosJson = $request->query('productos');
        $ci = $request->query('ci'); // Obtener el CI
        $descuento = $request->query('descuento', 0); // Obtener el descuento
        $pagado = $request->query('pagado', 0); // Obtener el monto pagado
        $pagadoqr = $request->query('pagadoqr', 0); // Obtener el monto pagado
        $cambio = $request->query('cambio', 0); // Obtener el cambio
        $tipopago = $request->query('tipo_pago');
        $garantia = $request->query('garantia');
        $id_sucursal = $request->query('id_sucursal'); // Capturar garantía
        $id_user = $request->query('id_user'); // Cambiar query por input


        // Verificar si la sucursal existe
        $sucursal = Sucursale::find($id_sucursal);
        if (!$sucursal) {
            return response()->json(['error' => 'Sucursal no encontrada'], 404);
        }

        // Obtener el vendedor
        $user = User::find($id_user);


        // Verificar si el vendedor existe
        if (!$user) {
            return response()->json(['error' => 'Vendedor no encontrado'], 404);
        }
        // Decodificar el JSON de productos
        $pedidos = json_decode($productosJson, true);

        // Inicializar un array para almacenar los productos detallados
        $productosDetalles = [];

        // Buscar cada producto en la base de datos y agregar los detalles
        foreach ($pedidos as $pedido) {
            $producto = Producto::find($pedido['id']); // Busca el producto por ID

            if ($producto) {
                $productosDetalles[] = [
                    'cantidad' => $pedido['cantidad'],
                    'nombre' => $producto->nombre, // Obtener el nombre del producto
                    'precio' => $pedido['precio'], // Usar el precio editado
                    'total' => $pedido['total'] // Total editable
                ];
            }
        }

        // Crear el PDF
        $pdf = new FPDF('P', 'mm', [80, 200]);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->AddPage();
        $marginTop = 5;

        // Preparar los datos del pedido
        $pedido = [
            'nombre_cliente' => $nombreCliente,
            'nit' => $ci, // Asigna el CI al campo NIT

            'fecha' => date('Y/m/d'),
            'productos' => $productosDetalles, // Usar los detalles de los productos
            'subtotal' => $costoTotal,
            'descuento' => $descuento, // Agregar descuento
            'total' => $costoTotal - $descuento, // Calcular total menos descuento
            'pagado' => $pagado, // Agregar monto pagado
            'pagadoqr' => $pagadoqr, // Agregar monto pagado
            'cambio' => $cambio, // Agregar cambio
            'monto_a_pagar' => $costoTotal, // Mantener monto a pagar como total
            'forma_pago' => $tipopago,
            'garantia' => $garantia,
            'id_sucursal' => $id_sucursal,
            'nombre_sucursal' => $sucursal->nombre, // Agregar nombre de la sucursal
            'id_user' => $id_user,
            'nombre_vendedor' => $user->name
        ];
        if ($tipopago == "Efectivo y QR") {
            $this->datosqye($pdf, $pedido, $marginTop);
        } else {
            $this->datos($pdf, $pedido, $marginTop);
        }
        // Llamar a la función para agregar los datos al PDF

        $pdf->Output('I', 'pedidos.pdf'); // 'I' para visualizar en el navegador
    }


    public function datosqye($pdf, $pedido, $marginTop)
    {
        $pdf->SetY($marginTop);

        // Logo centrado
        $pdf->Image('images/logo.png', 30, 5, 20, 20, 'PNG'); // Ajusta la ruta y tamaño del logo
        $pdf->Ln(17); // Espacio debajo del logo

        // Cabecera
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(0, 4, utf8_decode("IMPORTADORA MIRANDA S.A."), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 4, utf8_decode("A un Click del Producto que Necesita!!"), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 4, utf8_decode("Telefono: 70621016"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("Direccion: Caparazon Mall Center, Planta Baja, Local Nro29"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode($pedido['nombre_sucursal']), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("Fecha: " . date('Y/m/d H:i:s')), 0, 1, 'C');


        $pdf->Cell(0, 4, utf8_decode("Codigo de Venta:IMP" . date('Y/m/d')), 0, 1, 'C');

        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea horizontal
        $pdf->Ln(2);


        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 4, utf8_decode("Forma de Pago: " . $pedido['forma_pago']), 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 8);
        // Información de la factura
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(0, 4, utf8_decode("COMPRA DE PRODUCTO"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode(strtoupper($pedido['garantia'])), 0, 1, 'C');

        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea horizontal
        $pdf->Ln(2);

        $pdf->Cell(0, 4, utf8_decode("Cliente: " . $pedido['nombre_cliente']), 0, 1, 'L');
        $pdf->Cell(0, 4, utf8_decode("CI / NIT: " . $pedido['nit']), 0, 1, 'L'); // Mostrar el CI aquí
        $pdf->Cell(0, 4, utf8_decode("Fecha: " . $pedido['fecha']), 0, 1, 'L');
        $pdf->Cell(0, 4, utf8_decode("Vendedor: " . $pedido['nombre_vendedor']), 0, 1, 'L');

        /*    vendedor quiero que se vea aqui */


        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea horizontal
        $pdf->Ln(2);

        // Detalle de productos
        $pdf->SetFont('Arial', 'B', 8);
        // Cabecera
        $pdf->Cell(10, 6, utf8_decode("Cant."), 1, 0, 'C');
        $pdf->Cell(30, 6, utf8_decode("Desc."), 1, 0, 'C');
        $pdf->Cell(10, 6, utf8_decode("P.Unit"), 1, 0, 'C');
        $pdf->Cell(15, 6, utf8_decode("Subtotal"), 1, 1, 'C');

        // Productos
        $pdf->SetFont('Arial', '', 6);

        if (is_array($pedido['productos'])) {
            foreach ($pedido['productos'] as $producto) {
                $pdf->Cell(10, 4, utf8_decode($producto['cantidad']), 1, 0, 'C');

                // Ajustar el tamaño de la fuente según la longitud del nombre del producto
                $nombre = utf8_decode($producto['nombre'] ?? 'Sin descripción');
                $maxCaracteres = 20; // Número de caracteres antes de reducir el tamaño

                if (strlen($nombre) > $maxCaracteres) {
                    $pdf->SetFont('Arial', '', 5); // Disminuye el tamaño de fuente si el texto es muy largo
                } else {
                    $pdf->SetFont('Arial', '', 7); // Tamaño normal
                }

                $pdf->Cell(30, 4, $nombre, 1, 0, 'L');

                $pdf->SetFont('Arial', '', 6);
                $pdf->Cell(10, 4, utf8_decode($producto['precio']), 1, 0, 'R'); // Precio unitario
                $pdf->Cell(15, 4, utf8_decode($producto['total']), 1, 1, 'R'); // Total del producto
            }
        } else {
            $pdf->Cell(0, 10, 'No hay productos disponibles.', 0, 1, 'C');
        }

        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea horizontal
        $pdf->Ln(2);

        // Subtotal y total
        $pdf->SetFont('Arial', 'B', 8);

        // Calculate the subtotal
        $subtotal = array_sum(array_column($pedido['productos'], 'total')) + $pedido['descuento'];

        $pdf->Cell(0, 4, utf8_decode("PRECIO ORIGINAL: " . number_format($subtotal, 2)), 0, 1, 'R');
        $pdf->Cell(0, 4, utf8_decode("DESCUENTO: " . number_format($pedido['descuento'], 2)), 0, 1, 'R');
        $pdf->Cell(0, 4, utf8_decode("TOTAL: " . number_format($pedido['subtotal'], 2)), 0, 1, 'R');
        $pdf->Cell(0, 4, utf8_decode("PAGADO EFECTIVO: " . number_format($pedido['pagado'], 2)), 0, 1, 'R');
        $pdf->Cell(0, 4, utf8_decode("PAGADO QR: " . number_format($pedido['pagadoqr'], 2)), 0, 1, 'R');
        $pdf->Cell(0, 4, utf8_decode("CAMBIO: " . number_format($pedido['cambio'], 2)), 0, 1, 'R');
        $pdf->Cell(0, 4, utf8_decode("MONTO A PAGAR: " . number_format($pedido['monto_a_pagar'], 2)), 0, 1, 'R');

        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea horizontal
        $pdf->Ln(2);

        // Subtotal y total
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(0, 4, utf8_decode("NOTA IMPORTANTE"), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(0, 4, utf8_decode("Los productos en PROMOCION NO CUENTAN CON NINGUN"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("TIPO DE GARANTIA, ya que se encuentran en precio de REMATE."), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("Si su producto llegara a contar con algun defecto de FABRICA"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("si quiere cambiarlo debe cancelar el producto al precio normal."), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("y debe traerlo como maximo al dia siguente por la tarde con su"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("NOTA DE VENTA de lo contrario"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("pierde derecho a cualquier RECLAMO"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("GRACIAS POR SU COMPRA :D !!!"), 0, 1, 'C');
    }
    public function datos($pdf, $pedido, $marginTop)
    {
        $pdf->SetY($marginTop);

        // Logo centrado
        $pdf->Image('images/logo.png', 30, 5, 20, 20, 'PNG'); // Ajusta la ruta y tamaño del logo
        $pdf->Ln(17); // Espacio debajo del logo

        // Cabecera
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(0, 4, utf8_decode("IMPORTADORA MIRANDA S.A."), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 4, utf8_decode("A un Click del Producto que Necesita!!"), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 4, utf8_decode("Telefono: 70621016"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("Direccion: Caparazon Mall Center, Planta Baja, Local Nro29"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode($pedido['nombre_sucursal']), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("Fecha: " . date('Y/m/d H:i:s')), 0, 1, 'C');


        $pdf->Cell(0, 4, utf8_decode("Codigo de Venta:IMP" . date('Y/m/d')), 0, 1, 'C');

        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea horizontal
        $pdf->Ln(2);


        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 4, utf8_decode("Forma de Pago: " . $pedido['forma_pago']), 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 8);
        // Información de la factura
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(0, 4, utf8_decode("COMPRA DE PRODUCTO"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode(strtoupper($pedido['garantia'])), 0, 1, 'C');

        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea horizontal
        $pdf->Ln(2);

        $pdf->Cell(0, 4, utf8_decode("Cliente: " . $pedido['nombre_cliente']), 0, 1, 'L');
        $pdf->Cell(0, 4, utf8_decode("CI / NIT: " . $pedido['nit']), 0, 1, 'L'); // Mostrar el CI aquí
        $pdf->Cell(0, 4, utf8_decode("Fecha: " . $pedido['fecha']), 0, 1, 'L');
        $pdf->Cell(0, 4, utf8_decode("Vendedor: " . $pedido['nombre_vendedor']), 0, 1, 'L');

        /*    vendedor quiero que se vea aqui */


        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea horizontal
        $pdf->Ln(2);

        // Detalle de productos
        $pdf->SetFont('Arial', 'B', 8);
        // Cabecera
        $pdf->Cell(10, 6, utf8_decode("Cant."), 1, 0, 'C');
        $pdf->Cell(30, 6, utf8_decode("Desc."), 1, 0, 'C');
        $pdf->Cell(10, 6, utf8_decode("P.Unit"), 1, 0, 'C');
        $pdf->Cell(15, 6, utf8_decode("Subtotal"), 1, 1, 'C');

        // Productos
        $pdf->SetFont('Arial', '', 6);

        if (is_array($pedido['productos'])) {
            foreach ($pedido['productos'] as $producto) {
                $pdf->Cell(10, 4, utf8_decode($producto['cantidad']), 1, 0, 'C');

                // Ajustar el tamaño de la fuente según la longitud del nombre del producto
                $nombre = utf8_decode($producto['nombre'] ?? 'Sin descripción');
                $maxCaracteres = 20; // Número de caracteres antes de reducir el tamaño

                if (strlen($nombre) > $maxCaracteres) {
                    $pdf->SetFont('Arial', '', 5); // Disminuye el tamaño de fuente si el texto es muy largo
                } else {
                    $pdf->SetFont('Arial', '', 7); // Tamaño normal
                }

                $pdf->Cell(30, 4, $nombre, 1, 0, 'L');

                $pdf->SetFont('Arial', '', 6);
                $pdf->Cell(10, 4, utf8_decode($producto['precio']), 1, 0, 'R'); // Precio unitario
                $pdf->Cell(15, 4, utf8_decode($producto['total']), 1, 1, 'R'); // Total del producto
            }
        } else {
            $pdf->Cell(0, 10, 'No hay productos disponibles.', 0, 1, 'C');
        }

        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea horizontal
        $pdf->Ln(2);

        // Subtotal y total
        $pdf->SetFont('Arial', 'B', 8);

        // Calculate the subtotal
        $subtotal = array_sum(array_column($pedido['productos'], 'total')) + $pedido['descuento'];

        $pdf->Cell(0, 4, utf8_decode("PRECIO ORIGINAL: " . number_format($subtotal, 2)), 0, 1, 'R');
        $pdf->Cell(0, 4, utf8_decode("DESCUENTO: " . number_format($pedido['descuento'], 2)), 0, 1, 'R');
        $pdf->Cell(0, 4, utf8_decode("TOTAL: " . number_format($pedido['subtotal'], 2)), 0, 1, 'R');
        $pdf->Cell(0, 4, utf8_decode("PAGADO: " . number_format($pedido['pagado'], 2)), 0, 1, 'R');
        $pdf->Cell(0, 4, utf8_decode("CAMBIO: " . number_format($pedido['cambio'], 2)), 0, 1, 'R');
        $pdf->Cell(0, 4, utf8_decode("MONTO A PAGAR: " . number_format($pedido['monto_a_pagar'], 2)), 0, 1, 'R');

        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea horizontal
        $pdf->Ln(2);

        // Subtotal y total
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(0, 4, utf8_decode("NOTA IMPORTANTE"), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(0, 4, utf8_decode("Los productos en PROMOCION NO CUENTAN CON NINGUN"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("TIPO DE GARANTIA, ya que se encuentran en precio de REMATE."), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("Si su producto llegara a contar con algun defecto de FABRICA"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("si quiere cambiarlo debe cancelar el producto al precio normal."), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("y debe traerlo como maximo al dia siguente por la tarde con su"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("NOTA DE VENTA de lo contrario"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("pierde derecho a cualquier RECLAMO"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("GRACIAS POR SU COMPRA :D !!!"), 0, 1, 'C');
    }

    public function pdf(Request $request)
    {
        // Obtener los parámetros de la consulta
        $nombreCliente = $request->query('nombre_cliente');
        $costoTotal = $request->query('costo_total');
        $productosJson = $request->query('productos');
        $ci = $request->query('ci'); // Obtener el CI
        $descuento = $request->query('descuento', 0); // Obtener el descuento
        $pagado = $request->query('pagado', 0); // Obtener el monto pagado
        $pagadoqr = $request->query('pagadoqr', 0); // Obtener el monto pagado
        $cambio = $request->query('cambio', 0); // Obtener el cambio
        $tipopago = $request->query('tipo_pago');
        $garantia = $request->query('garantia');
        $id_sucursal = $request->query('id_sucursal'); // Capturar garantía
        $id_user = $request->query('id_user'); // Cambiar query por input


        // Verificar si la sucursal existe
        $sucursal = Sucursale::find(1);
        if (!$sucursal) {
            return response()->json(['error' => 'Sucursal no encontrada'], 404);
        }

        // Obtener el vendedor
        $user = User::find($id_user);


        // Verificar si el vendedor existe
        if (!$user) {
            return response()->json(['error' => 'Vendedor no encontrado'], 404);
        }
        // Decodificar el JSON de productos
        $pedidos = json_decode($productosJson, true);

        // Inicializar un array para almacenar los productos detallados
        $productosDetalles = [];

        // Buscar cada producto en la base de datos y agregar los detalles
        foreach ($pedidos as $pedido) {
            $producto = Producto::find($pedido['id']); // Busca el producto por ID

            if ($producto) {
                $productosDetalles[] = [
                    'cantidad' => $pedido['cantidad'],
                    'nombre' => $producto->nombre, // Obtener el nombre del producto
                    'precio' => $pedido['precio'], // Usar el precio editado
                    'total' => $pedido['total'] // Total editable
                ];
            }
        }

        // Crear el PDF
        $pdf = new FPDF('P', 'mm', [80, 200]);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->AddPage();
        $marginTop = 5;

        // Preparar los datos del pedido
        $pedido = [
            'nombre_cliente' => $nombreCliente,
            'nit' => $ci, // Asigna el CI al campo NIT

            'fecha' => date('Y/m/d'),
            'productos' => $productosDetalles, // Usar los detalles de los productos
            'subtotal' => $costoTotal,
            'descuento' => $descuento, // Agregar descuento
            'total' => $costoTotal - $descuento, // Calcular total menos descuento
            'pagado' => $pagado, // Agregar monto pagado
            'pagadoqr' => $pagadoqr, // Agregar monto pagado
            'cambio' => $cambio, // Agregar cambio
            'monto_a_pagar' => $costoTotal, // Mantener monto a pagar como total
            'forma_pago' => $tipopago,
            'garantia' => $garantia,
            'id_sucursal' => $id_sucursal,
            'nombre_sucursal' => $sucursal->nombre, // Agregar nombre de la sucursal
            'id_user' => $id_user,
            'nombre_vendedor' => $user->name
        ];
        if ($tipopago == "Efectivo y QR") {
            $this->datosqye1($pdf, $pedido, $marginTop);
        } else {
            $this->datos1($pdf, $pedido, $marginTop);
        }
        // Llamar a la función para agregar los datos al PDF

        $pdf->Output('I', 'pedidos.pdf'); // 'I' para visualizar en el navegador
    }


    public function datosqye1($pdf, $pedido, $marginTop)
    {
        $pdf->SetY($marginTop);

        // Logo centrado
        $pdf->Image('images/logo.png', 30, 5, 20, 20, 'PNG'); // Ajusta la ruta y tamaño del logo
        $pdf->Ln(17); // Espacio debajo del logo

        // Cabecera
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(0, 4, utf8_decode("IMPORTADORA MIRANDA S.A."), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 4, utf8_decode("A un Click del Producto que Necesita!!"), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 4, utf8_decode("Telefono: 70621016"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("Direccion: Caparazon Mall Center, Planta Baja, Local Nro29"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode($pedido['nombre_sucursal']), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("Fecha: " . date('Y/m/d H:i:s')), 0, 1, 'C');


        $pdf->Cell(0, 4, utf8_decode("Codigo de Venta:IMP" . date('Y/m/d')), 0, 1, 'C');

        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea horizontal
        $pdf->Ln(2);


        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 4, utf8_decode("Forma de Pago: " . $pedido['forma_pago']), 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 8);
        // Información de la factura
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(0, 4, utf8_decode("COMPRA DE PRODUCTO"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode(strtoupper($pedido['garantia'])), 0, 1, 'C');

        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea horizontal
        $pdf->Ln(2);

        $pdf->Cell(0, 4, utf8_decode("Cliente: " . $pedido['nombre_cliente']), 0, 1, 'L');
        $pdf->Cell(0, 4, utf8_decode("CI / NIT: " . $pedido['nit']), 0, 1, 'L'); // Mostrar el CI aquí
        $pdf->Cell(0, 4, utf8_decode("Fecha: " . $pedido['fecha']), 0, 1, 'L');
        $pdf->Cell(0, 4, utf8_decode("Vendedor: " . $pedido['nombre_vendedor']), 0, 1, 'L');

        /*    vendedor quiero que se vea aqui */


        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea horizontal
        $pdf->Ln(2);

        // Detalle de productos
        $pdf->SetFont('Arial', 'B', 8);
        // Cabecera
        $pdf->Cell(10, 6, utf8_decode("Cant."), 1, 0, 'C');
        $pdf->Cell(30, 6, utf8_decode("Desc."), 1, 0, 'C');
        $pdf->Cell(10, 6, utf8_decode("P.Unit"), 1, 0, 'C');
        $pdf->Cell(15, 6, utf8_decode("Subtotal"), 1, 1, 'C');

        // Productos
        $pdf->SetFont('Arial', '', 6);

        if (is_array($pedido['productos'])) {
            foreach ($pedido['productos'] as $producto) {
                $pdf->Cell(10, 4, utf8_decode($producto['cantidad']), 1, 0, 'C');

                // Ajustar el tamaño de la fuente según la longitud del nombre del producto
                $nombre = utf8_decode($producto['nombre'] ?? 'Sin descripción');
                $maxCaracteres = 20; // Número de caracteres antes de reducir el tamaño

                if (strlen($nombre) > $maxCaracteres) {
                    $pdf->SetFont('Arial', '', 5); // Disminuye el tamaño de fuente si el texto es muy largo
                } else {
                    $pdf->SetFont('Arial', '', 7); // Tamaño normal
                }

                $pdf->Cell(30, 4, $nombre, 1, 0, 'L');

                $pdf->SetFont('Arial', '', 6);
                $pdf->Cell(10, 4, utf8_decode($producto['precio']), 1, 0, 'R'); // Precio unitario
                $pdf->Cell(15, 4, utf8_decode($producto['total']), 1, 1, 'R'); // Total del producto
            }
        } else {
            $pdf->Cell(0, 10, 'No hay productos disponibles.', 0, 1, 'C');
        }

        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea horizontal
        $pdf->Ln(2);

        // Subtotal y total
        $pdf->SetFont('Arial', 'B', 8);

        // Calculate the subtotal
        $subtotal = array_sum(array_column($pedido['productos'], 'total')) + $pedido['descuento'];

        $pdf->Cell(0, 4, utf8_decode("PRECIO ORIGINAL: " . number_format($subtotal, 2)), 0, 1, 'R');
        $pdf->Cell(0, 4, utf8_decode("DESCUENTO: " . number_format($pedido['descuento'], 2)), 0, 1, 'R');
        $pdf->Cell(0, 4, utf8_decode("TOTAL: " . number_format($pedido['subtotal'], 2)), 0, 1, 'R');
        $pdf->Cell(0, 4, utf8_decode("PAGADO EFECTIVO: " . number_format($pedido['pagado'], 2)), 0, 1, 'R');
        $pdf->Cell(0, 4, utf8_decode("PAGADO QR: " . number_format($pedido['pagadoqr'], 2)), 0, 1, 'R');
        $pdf->Cell(0, 4, utf8_decode("CAMBIO: " . number_format($pedido['cambio'], 2)), 0, 1, 'R');
        $pdf->Cell(0, 4, utf8_decode("MONTO A PAGAR: " . number_format($pedido['monto_a_pagar'], 2)), 0, 1, 'R');

        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea horizontal
        $pdf->Ln(2);

        // Subtotal y total
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(0, 4, utf8_decode("NOTA IMPORTANTE"), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(0, 4, utf8_decode("Los productos en PROMOCION NO CUENTAN CON NINGUN"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("TIPO DE GARANTIA, ya que se encuentran en precio de REMATE."), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("Si su producto llegara a contar con algun defecto de FABRICA"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("si quiere cambiarlo debe cancelar el producto al precio normal."), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("y debe traerlo como maximo al dia siguente por la tarde con su"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("NOTA DE VENTA de lo contrario"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("pierde derecho a cualquier RECLAMO"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("GRACIAS POR SU COMPRA :D !!!"), 0, 1, 'C');
    }
    public function datos1($pdf, $pedido, $marginTop)
    {
        $pdf->SetY($marginTop);

        // Logo centrado
        $pdf->Image('images/logo.png', 30, 5, 20, 20, 'PNG'); // Ajusta la ruta y tamaño del logo
        $pdf->Ln(17); // Espacio debajo del logo

        // Cabecera
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(0, 4, utf8_decode("IMPORTADORA MIRANDA S.A."), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 4, utf8_decode("A un Click del Producto que Necesita!!"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode("Fecha: " . date('Y/m/d H:i:s')), 0, 1, 'C');

        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea horizontal
        $pdf->Ln(2);


        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 4, utf8_decode("Forma de Pago: " . $pedido['forma_pago']), 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 8);
        // Información de la factura
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(0, 4, utf8_decode("COMPRA DE PRODUCTO"), 0, 1, 'C');
        $pdf->Cell(0, 4, utf8_decode(strtoupper($pedido['garantia'])), 0, 1, 'C');

        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea horizontal
        $pdf->Ln(2);

        $pdf->Cell(0, 4, utf8_decode("Cliente: " . $pedido['nombre_cliente']), 0, 1, 'L');
        $pdf->Cell(0, 4, utf8_decode("CI / NIT: " . $pedido['nit']), 0, 1, 'L'); // Mostrar el CI aquí
        $pdf->Cell(0, 4, utf8_decode("Fecha: " . $pedido['fecha']), 0, 1, 'L');

        /*    vendedor quiero que se vea aqui */


        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea horizontal
        $pdf->Ln(2);

        // Detalle de productos
        $pdf->SetFont('Arial', 'B', 8);
        // Cabecera
        $pdf->Cell(10, 6, utf8_decode("Cant."), 1, 0, 'C');
        $pdf->Cell(30, 6, utf8_decode("Desc."), 1, 0, 'C');
        $pdf->Cell(10, 6, utf8_decode("P.Unit"), 1, 0, 'C');
        $pdf->Cell(15, 6, utf8_decode("Subtotal"), 1, 1, 'C');

        // Productos
        $pdf->SetFont('Arial', '', 6);

        if (is_array($pedido['productos'])) {
            foreach ($pedido['productos'] as $producto) {
                $pdf->Cell(10, 4, utf8_decode($producto['cantidad']), 1, 0, 'C');

                // Ajustar el tamaño de la fuente según la longitud del nombre del producto
                $nombre = utf8_decode($producto['nombre'] ?? 'Sin descripción');
                $maxCaracteres = 20; // Número de caracteres antes de reducir el tamaño

                if (strlen($nombre) > $maxCaracteres) {
                    $pdf->SetFont('Arial', '', 5); // Disminuye el tamaño de fuente si el texto es muy largo
                } else {
                    $pdf->SetFont('Arial', '', 7); // Tamaño normal
                }

                $pdf->Cell(30, 4, $nombre, 1, 0, 'L');

                $pdf->SetFont('Arial', '', 6);
                $pdf->Cell(10, 4, utf8_decode($producto['precio']), 1, 0, 'R'); // Precio unitario
                $pdf->Cell(15, 4, utf8_decode($producto['total']), 1, 1, 'R'); // Total del producto
            }
        } else {
            $pdf->Cell(0, 10, 'No hay productos disponibles.', 0, 1, 'C');
        }

        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea horizontal
        $pdf->Ln(2);

        // Subtotal y total
        $pdf->SetFont('Arial', 'B', 8);

        // Calculate the subtotal
        $subtotal = array_sum(array_column($pedido['productos'], 'total')) + $pedido['descuento'];

        $pdf->Cell(0, 4, utf8_decode("PRECIO ORIGINAL: " . number_format($subtotal, 2)), 0, 1, 'R');
        $pdf->Cell(0, 4, utf8_decode("DESCUENTO: " . number_format($pedido['descuento'], 2)), 0, 1, 'R');
        $pdf->Cell(0, 4, utf8_decode("TOTAL: " . number_format($pedido['subtotal'], 2)), 0, 1, 'R');
        $pdf->Cell(0, 4, utf8_decode("PAGADO: " . number_format($pedido['pagado'], 2)), 0, 1, 'R');
        $pdf->Cell(0, 4, utf8_decode("CAMBIO: " . number_format($pedido['cambio'], 2)), 0, 1, 'R');
        $pdf->Cell(0, 4, utf8_decode("MONTO A PAGAR: " . number_format($pedido['monto_a_pagar'], 2)), 0, 1, 'R');

        // Línea separadora
        $pdf->Ln(2);
        $pdf->Cell(0, 0, '', 'T'); // Línea horizontal
        $pdf->Ln(2);

        // Subtotal y total
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(0, 4, utf8_decode("GRACIAS POR SU COMPRA :D !!!"), 0, 1, 'C');
    }

    public function productosnuevos(Request $request, $id, $idventa)
    {
        $idventa1 = $idventa;

        // Log de lo que llega en la solicitud (request)
        Log::info('Datos de la solicitud', [
            'request_data' => $request->all(),
            'id' => $id,
            'idventa' => $idventa
        ]);

        // Busca la venta por su ID (aunque no la devolvemos en el JSON)
        $venta = Venta::with(['ventaProductos.producto']) // Carga los productos relacionados
            ->find($idventa);

        Log::info('Venta encontrada', ['venta' => $venta]);

        // Busca la sucursal
        $sucur = Sucursale::find($id);

        // Inicializamos la consulta base para los productos
        $productosQuery = Inventario::where('id_sucursal', $id)
            ->join('productos', 'productos.id', '=', 'inventario.id_producto')
            ->with(['producto.categoria', 'producto.marca', 'producto.fotos'])
            ->orderByDesc('inventario.favorito')
            ->orderByDesc('productos.estado')
            ->orderBy('productos.created_at', 'desc');

        // Si hay un término de búsqueda, filtramos los productos
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $productosQuery->whereHas('producto', function ($query) use ($search) {
                $query->where('nombre', 'like', "%$search%");
            });
        }

        // Ejecutamos la consulta y paginamos los productos
        $productos = $productosQuery->paginate(9);

        // Incluir al usuario autenticado
        $loggedUser = auth()->user();
        if ($loggedUser) {
            // Añadimos el usuario autenticado a la respuesta
            $loggedUser = $loggedUser->only(['id', 'name', 'email']); // Solo los datos básicos
        }

        // Recorremos los productos para obtener los datos adicionales
        foreach ($productos as $inventario) {
            $inventario->producto->stock_actual = $inventario->producto->getStockActual();
            $inventario->producto->stock_sucursal = $inventario->cantidad;
        }

        // Si es una solicitud AJAX, retornamos los productos, el usuario autenticado, el idventa1 y la sucursal
        return response()->json([
            'usuario' => $loggedUser,  // Incluimos el usuario autenticado
            'idventa1' => $idventa1,  // Agregamos el idventa1
            'sucursale' => $sucur,     // Agregamos la sucursal
            'productos' => $productos,
        ]);
    }

    public function verid($id)
    {
        // Busca una venta por su ID, cargando los productos relacionados y el usuario asociado
        $venta = Venta::with(['ventaProductos.producto', 'user']) // Carga los productos y el usuario
            ->find($id);

        // Verifica si la venta fue encontrada
        if ($venta) {
            return response()->json([
                'venta' => $venta,
                'nombre_usuario' => $venta->user ? $venta->user->name : null // Agrega el nombre del usuario
            ]);
        } else {
            return response()->json([
                'error' => 'Venta no encontrada'
            ], 404);
        }
    }
}
