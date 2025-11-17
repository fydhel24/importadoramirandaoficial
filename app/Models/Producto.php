<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'precio_descuento',
        'stock',
        'estado',
        'fecha',
        'id_cupo',
        'id_tipo',
        'id_categoria',
        'id_marca',
        'estado_producto',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria');
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'id_marca');
    }

    public function tipo()
    {
        return $this->belongsTo(Tipo::class, 'id_tipo');
    }

    public function cupo()
    {
        return $this->belongsTo(Cupo::class, 'id_cupo');
    }

    public function fotos()
    {
        return $this->belongsToMany(Foto::class, 'catalogos', 'id_producto', 'id_foto');
    }
    // Agregar la relación con Inventario
    public function inventarios()
    {
        return $this->hasMany(Inventario::class, 'id_producto');
    }
    // Dentro del modelo Producto
    public function getStockActual()
    {
        // Esto es solo un ejemplo, puedes modificarlo para que se ajuste a tu lógica de negocio
        return $this->stock;  // Si tienes el stock directamente en el modelo Producto
    }

    public function getStockSucursal()
    {
        // Si tienes una relación con la tabla de Sucursales o Inventarios
        return $this->sucursales()->where('sucursal_id', 1)->first()->stock;  // Ejemplo de lógica
    }
    public function descontarStockSucursal($cantidad, $id_sucursal)
    {
        $inventario = $this->inventarios()->where('id_sucursal', $id_sucursal)->first();

        if (!$inventario) {
            throw new \Exception('No se encontró inventario para este producto en la sucursal');
        }

        if ($inventario->cantidad < $cantidad) {
            throw new \Exception('No hay suficiente stock en esta sucursal');
        }

        $inventario->cantidad -= $cantidad;
        $inventario->save();
    }
    //eliminacion de almacen 
 // Método para agregar stock en una sucursal específica
    public function agregarStockSucursal($cantidad, $id_sucursal)
    {
        $inventario = $this->inventarios()->where('id_sucursal', $id_sucursal)->first();

        if (!$inventario) {
            // Crear un nuevo registro de inventario si no existe
            $this->inventarios()->create([
                'id_sucursal' => $id_sucursal,
                'cantidad' => $cantidad,
            ]);
        } else {
            $inventario->cantidad += $cantidad;
            $inventario->save();
        }
    }
    public function getTotalStockAcrossBranches()
    {
        $totalStock = 0;
        foreach ($this->inventarios as $inventario) {
            $totalStock += $inventario->cantidad;
        }
        return $totalStock;
    }

    public function updateStock()
    {
        $totalStockAcrossBranches = $this->getTotalStockAcrossBranches();
        $this->stock = $this->stock - $totalStockAcrossBranches;
        $this->save();
    }
    public function ventaProductos()
    {
        return $this->hasMany(VentaProducto::class, 'id_producto');
    }
        public function inventarioEnSucursal($idSucursal) 
    { 
        return $this->inventarios()->where('id_sucursal', $idSucursal)->first();
    } 
    // En el modelo Producto
    public function precioProductos()
    {
        return $this->hasMany(PrecioProducto::class, 'id_producto', 'id');
    }
    public function predicciones()
    {
        return $this->hasMany(Prediccion::class);
    }
}
