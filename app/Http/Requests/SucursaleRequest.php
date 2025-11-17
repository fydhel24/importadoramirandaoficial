<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SucursaleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Si necesitas autorización, cámbialo a false según tus necesidades
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre'    => 'required|string|max:255',  // El nombre es obligatorio y debe ser una cadena
            'direccion' => 'required|string|max:255',  // La dirección es obligatoria y debe ser una cadena
            'celular'   => 'required|string|max:20',   // El celular es obligatorio y debe ser una cadena de máximo 20 caracteres
            'estado'    => 'required|string|in:activo,inactivo,pendiente', // El estado debe ser uno de los tres valores posibles
            'logo'      => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048', // El logo es opcional, pero si se proporciona, debe ser una imagen válida
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.required'    => 'El campo nombre es obligatorio.',
            'direccion.required' => 'El campo dirección es obligatorio.',
            'celular.required'   => 'El campo celular es obligatorio.',
            'estado.required'    => 'El campo estado es obligatorio.',
            'logo.image'         => 'El archivo de logo debe ser una imagen válida.',
            'logo.mimes'         => 'El logo debe ser de tipo: jpg, jpeg, png, gif.',
            'logo.max'           => 'El logo no debe superar los 2MB.',
        ];
    }
}
