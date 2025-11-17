<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PedidoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
			'nombre' => 'required|string',
			'ci' => 'required|string',
			'celular' => 'required|string',
			'destino' => 'required|string',
			'direccion' => 'required|string',
			'estado' => 'required|string',
			'cantidad_productos' => 'required',
			'detalle' => 'required|string',
			'productos' => 'required|string',
			'monto_deposito' => 'required',
			'monto_enviado_pagado' => 'required',
			'fecha' => 'required',
			'id_semana' => 'required',
			'foto_comprobante' => 'string',
			'codigo' => 'string',
			'estado_pedido' => 'required|string',
        ];
    }
}
