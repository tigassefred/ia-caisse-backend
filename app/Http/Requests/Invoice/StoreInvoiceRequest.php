<?php

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
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
              'name' => 'required|string|max:255',
            'client_id'=>"string|nullable",
            'valeur_reduction' => 'required|integer|min:0',
            'reliquat' => 'required|integer|min:0',
            'comments' => 'nullable|string',
            'is10Yaars' => 'required|boolean',
            'isPayDiff' => 'required|boolean',
            'valeur_facture' => 'required|integer|min:0',
            'valeur_apres_reduction' => 'required|integer|min:0',
            'somme_verser' => 'required|integer|min:0',
            'commercial' => 'required|uuid',
            'Paniers' => 'required|array|min:1',
            'Paniers.*.uuid' => 'required|integer',
            'Paniers.*.qte' => 'required|integer|min:0',
            'Paniers.*.name' => 'required|string|max:255',
            'Paniers.*.designation' => 'required|string|max:255',
            'Paniers.*.type' => 'required|string|max:50',
            'Paniers.*.cbm' => 'required|numeric|min:0',
        ];
    }
}
