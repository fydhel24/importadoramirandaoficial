<?php 
 
 namespace App\Models; 
  
 use Illuminate\Database\Eloquent\Factories\HasFactory; 
 use Illuminate\Database\Eloquent\Model; 
  
 class Promocione extends Model 
 { 
     use HasFactory; 
  
     // Indicar el nombre de la tabla 
     protected $table = 'promociones'; 
  
     // Columnas que son asignables masivamente 
     protected $fillable = [ 
         'nombre', 
         'precio_promocion', 
         'id_sucursal', 
         'id_usuario', 
         'fecha_inicio', 
         'fecha_fin', 
         'estado', 
     ]; 
  
     // Relación con productos 
     public function productos() 
     { 
         return $this->belongsToMany(Producto::class, 'promocion_producto', 'id_promocion', 
 'id_producto') 
                     ->withPivot('cantidad', 'precio_unitario'); // Incluimos los campos de la tabla pivote 
     } 
      
     // Relación con la sucursal 
     public function sucursal() 
     { 
         return $this->belongsTo(Sucursale::class, 'id_sucursal'); 
     } 
  
     // Relación con el usuario 
     public function usuario() 
     { 
         return $this->belongsTo(User::class, 'id_usuario'); 
     } 
 }
  