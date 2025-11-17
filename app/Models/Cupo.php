<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Class Cupo
 *
 * @property $id
 * @property $codigo
 * @property $porcentaje
 * @property $estado
 * @property $created_at
 * @property $updated_at
 *
 * @property Producto[] $productos
 * @property Venta[] $ventas
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Cupo extends Model
{

    protected $perPage = 20;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['codigo', 'porcentaje', 'estado', 'fecha_inicio', 'fecha_fin', 'id_user'];

    /**
     * Los atributos que deberían ser tratados como fechas.
     *
     * @var array
     */
    protected $dates = ['fecha_inicio', 'fecha_fin'];  // Aseguramos que las fechas sean tratadas como instancias de Carbon


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productos()
    {
        return $this->hasMany(\App\Models\Producto::class, 'id', 'id_cupo');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ventas()
    {
        return $this->hasMany(\App\Models\Venta::class, 'id', 'id_cupo');
    }

    /**
     * Relación inversa con el modelo User
     * Un 'Cupo' pertenece a un 'User'.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'id_user');
    }

    /**
     * Obtener la fecha de inicio en la zona horaria de Bolivia (si es necesario)
     * 
     * @param string|null $value
     * @return \Carbon\Carbon|null
     */
    public function getFechaInicioAttribute($value)
    {
        // Devolver la fecha en la zona horaria de Bolivia
        return $value ? Carbon::parse($value)->setTimezone('America/La_Paz') : null;
    }

    /**
     * Obtener la fecha de fin en la zona horaria de Bolivia (si es necesario)
     * 
     * @param string|null $value
     * @return \Carbon\Carbon|null
     */
    public function getFechaFinAttribute($value)
    {
        // Devolver la fecha en la zona horaria de Bolivia
        return $value ? Carbon::parse($value)->setTimezone('America/La_Paz') : null;
    }

    /**
     * Establecer la fecha de inicio para ser guardada en la zona horaria correcta.
     * 
     * @param string $value
     * @return void
     */
    public function setFechaInicioAttribute($value)
    {
        // Asegurarse de que la fecha se guarde en la zona horaria correcta de Bolivia
        $this->attributes['fecha_inicio'] = Carbon::parse($value)->setTimezone('America/La_Paz')->toDateTimeString();
    }

    /**
     * Establecer la fecha de fin para ser guardada en la zona horaria correcta.
     * 
     * @param string $value
     * @return void
     */
    public function setFechaFinAttribute($value)
    {
        // Asegurarse de que la fecha se guarde en la zona horaria correcta de Bolivia
        $this->attributes['fecha_fin'] = Carbon::parse($value)->setTimezone('America/La_Paz')->toDateTimeString();
    }
}
