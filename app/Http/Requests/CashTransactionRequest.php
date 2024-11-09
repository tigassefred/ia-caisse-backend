<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CashTransactionRequest extends FormRequest
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

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'failled',
            'message' => $validator->errors()->first(),
        ], 200));
    }
}
