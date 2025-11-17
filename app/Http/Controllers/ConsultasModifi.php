<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class ConsultasModifi extends Controller
{

public function updateStockForAllProducts()
{
    $productos = Producto::all();

    foreach ($productos as $producto) {
        $producto->updateStock();
    }

    return 'Stock updated successfully for all products.';
}
}
