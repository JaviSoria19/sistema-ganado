<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductoValidation extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $idProducto = $this->route('producto'); // si tu parámetro en la ruta es {producto}

        return [
            'idEmpresa'        => ['required', 'exists:empresas,idEmpresa'],
            'idMarca'          => ['required', 'exists:marcas,idMarca'],
            'idAbastecimiento' => ['required', 'exists:abastecimientos,idAbastecimiento'],
            'nombreProducto'   => ['required', 'string', 'min:3', 'max:255'],
            'codigoProducto'   => [
                'required',
                'string',
                'max:100',
                Rule::unique('productos', 'codigoProducto')->ignore($idProducto, 'idProducto'),
            ],
            'costoBaseUSD'      => ['required', 'numeric', 'min:0', 'max:99999.99'],
            'traspasoPorcentaje' => ['required', 'numeric', 'min:0', 'max:999.99'],
            'transporteUSD'     => ['required', 'numeric', 'min:0', 'max:99999.99'],
        ];
    }

    public function messages(): array
    {
        return [
            'idEmpresa.required'        => 'Debe seleccionar una empresa.',
            'idEmpresa.exists'          => 'La empresa seleccionada no existe en el sistema.',

            'idMarca.required'          => 'Debe seleccionar una marca.',
            'idMarca.exists'            => 'La marca seleccionada no existe en el sistema.',

            'idAbastecimiento.required' => 'Debe seleccionar un abastecimiento.',
            'idAbastecimiento.exists'   => 'El abastecimiento seleccionado no existe en el sistema.',

            'nombreProducto.required'   => 'El nombre del producto es obligatorio.',
            'nombreProducto.string'     => 'El nombre del producto debe ser texto.',
            'nombreProducto.min'        => 'El nombre del producto debe tener al menos :min caracteres.',
            'nombreProducto.max'        => 'El nombre del producto no puede exceder de :max caracteres.',

            'codigoProducto.required'   => 'El código del producto es obligatorio.',
            'codigoProducto.string'     => 'El código del producto debe ser texto.',
            'codigoProducto.max'        => 'El código del producto no puede exceder de :max caracteres.',
            'codigoProducto.unique'     => 'Este código de producto ya está registrado.',

            'costoBaseUSD.required'     => 'El costo base es obligatorio.',
            'costoBaseUSD.numeric'      => 'El costo base debe ser un número.',
            'costoBaseUSD.min'          => 'El costo base no puede ser negativo.',
            'costoBaseUSD.max'          => 'El costo base no puede superar los :max.',

            'traspasoPorcentaje.required' => 'El porcentaje de traspaso es obligatorio.',
            'traspasoPorcentaje.numeric'  => 'El porcentaje de traspaso debe ser un número.',
            'traspasoPorcentaje.min'      => 'El porcentaje de traspaso no puede ser negativo.',
            'traspasoPorcentaje.max'      => 'El porcentaje de traspaso no puede superar :max.',

            'transporteUSD.required'    => 'El costo de transporte es obligatorio.',
            'transporteUSD.numeric'     => 'El costo de transporte debe ser un número.',
            'transporteUSD.min'         => 'El costo de transporte no puede ser negativo.',
            'transporteUSD.max'         => 'El costo de transporte no puede superar los :max.',
        ];
    }
}
