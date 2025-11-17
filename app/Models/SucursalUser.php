<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SucursalUser extends Model
{
    use HasFactory;

    // Aquí puedes agregar cualquier atributo adicional si lo necesitas
    protected $table = 'sucursal_user';  // Si es necesario, puedes especificar el nombre de la tabla, pero Laravel lo infiere

    // Los campos que pueden ser asignados masivamente
    protected $fillable = ['user_id', 'sucursal_id'];

    // Agregar los campos de timestamps si son necesarios (aunque la migración ya los tiene)
    public $timestamps = true;
}
