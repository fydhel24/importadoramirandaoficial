<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudTrabajo extends Model
{
    use HasFactory;

     // 💥 Solución: indicamos el nombre real de la tabla
     protected $table = 'solicitudes_trabajo';
     
    protected $fillable = ['nombre', 'ci', 'celular', 'cv_pdf'];
}
