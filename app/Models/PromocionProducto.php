<?php 
 
namespace App\Models; 
 
use Illuminate\Database\Eloquent\Factories\HasFactory; 
use Illuminate\Database\Eloquent\Model; 
 
class PromocionProducto extends Model 
{ 
    use HasFactory; 
 
    // Nombre de la tabla 
    protected $table = 'promocion_producto'; 
     
    // Los atributos que se pueden asignar masivamente 
    protected $fillable = [ 
        'id_promocion', 
        'id_producto', 
        'cantidad', 
        'precio_unitario', 
    ]; 
 
    // Relación con el modelo Promocione 
    public function promocion() 
    { 
        return $this->belongsTo(Promocione::class, 'id_promocion'); 
    } 
 
    // Relación con el modelo Producto 
    public function producto() 
    { 
        return $this->belongsTo(Producto::class, 'id_producto'); 
    } 
}