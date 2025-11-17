<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventarioProducto extends Model
{
    use HasFactory;

    protected $table = 'inventarioproducto';
      public $timestamps = false; // Esto desactiva los timestamps automáticos
    protected $fillable = [
        'id_inventariohistorial',
        'id_producto',
        'cantidad',
        'cantidad_antes', // Nuevo campo
        'cantidad_despues', // Nuevo campo
    ];

    // Relación con el producto
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    // Relación con el historial
    public function historial()
    {
        return $this->belongsTo(InventarioHistorial::class, 'id_inventariohistorial');
    }
    //ANTES Y DESPUES, 
}
