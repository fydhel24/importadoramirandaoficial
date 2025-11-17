<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CupoRequest extends FormRequest
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
            'codigo' => 'required|string|max:255',
            'porcentaje' => 'required|numeric|min:0|max:100',
            'estado' => 'required|string|in:activo,inactivo',
            'fecha_inicio' => 'required|date|before:fecha_fin',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            // 'id_user' no es necesario en la validación porque se asigna automáticamente
        ];
    }
}
