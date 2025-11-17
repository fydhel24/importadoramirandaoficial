<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Inventario
 *
 * @property $id
 * @property $id_producto
 * @property $id_sucursal
 * @property $cantidad
 * @property $created_at
 * @property $updated_at
 * @property $id_user
 * @property $id_sucursal_origen
 * @property $id_user_destino
 * @property $transfer_date
 *
 * @property Producto $producto
 * @property Sucursale $sucursale
 * @property Sucursale $sucursalOrigen
 * @property User $user
 * @property User $userDestino
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Inventario extends Model
{
    // Establecer el número de resultados por página (paginación)
    protected $perPage = 20;

    // Especificar la tabla que se utilizará
    protected $table = 'inventario';

    /**
     * Los atributos que pueden ser asignados masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_producto', 
        'id_sucursal', // Sucursal de destino
        'cantidad', 
        'id_user', // Usuario
        'id_sucursal_origen', // Sucursal de origen
        'id_user_destino', // Usuario de destino
        'transfer_date', // Fecha y hora de la transferencia 
    ];

   

    /**
     * Relación con el modelo Producto
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function producto()
    {
        return $this->belongsTo(\App\Models\Producto::class, 'id_producto', 'id');
    }
   


    /**
     * Relación con el modelo Sucursale (Sucursal de destino)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sucursale()
    {
        return $this->belongsTo(\App\Models\Sucursale::class, 'id_sucursal', 'id');
    }

    /**
     * Relación con el modelo Sucursale (Sucursal de origen)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sucursalOrigen()
    {
        return $this->belongsTo(\App\Models\Sucursale::class, 'id_sucursal_origen', 'id');
    }

    /**
     * Relación con el modelo User (Usuario que realiza la operación)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'id_user', 'id');
    }

    /**
     * Relación con el modelo User (Usuario de destino)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userDestino()
    {
        return $this->belongsTo(\App\Models\User::class, 'id_user_destino', 'id');
    }  

}