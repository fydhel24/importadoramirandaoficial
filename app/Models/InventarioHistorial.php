<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventarioHistorial extends Model
{
    use HasFactory;
    protected $table = 'inventariohistorial';
   
    protected $fillable = [
        'id_sucursal_origen',
        'id_sucursal',
        'id_user',
        'id_user_destino',
        'fecha_envio',
        'estado', //estado, pendiente - enviado, enviado por defecto, en el historial que se vea solo los nviaodos, 
 
    ];

    // Relación con los productos enviados (detalles del envío)
    public function productos()
    {
        return $this->hasMany(InventarioProducto::class, 'id_inventariohistorial');
    }

    // Relación con la sucursal de origen
    public function sucursalOrigen()
    {
        return $this->belongsTo(Sucursale::class, 'id_sucursal_origen');
    }

    // Relación con la sucursal de destino
    public function sucursalDestino()
    {
        return $this->belongsTo(Sucursale::class, 'id_sucursal');
    }

    // Relación con el usuario que envió
    public function usuarioOrigen()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    // Relación con el usuario destinatario
    public function usuarioDestino()
    {
        return $this->belongsTo(User::class, 'id_user_destino');
    }
}
