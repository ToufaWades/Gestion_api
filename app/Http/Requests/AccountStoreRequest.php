<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AccountStoreRequest extends FormRequest
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
            'type' => 'required|string|in:cheque,epargne',
            'soldeInitial' => 'required|numeric|min:10000',
            'devise' => 'required|string|in:FCFA,XOF',
            'solde' => 'required|numeric|min:0',
            'client' => 'required|array',
            'client.id' => 'nullable|integer|exists:clients,id',
            'client.titulaire' => 'required|string|max:255',
            'client.nci' => 'required|string|max:50',
            'client.email' => 'required|email|unique:users,email',
            'client.telephone' => [
                'required',
                'string',
                'unique:users,telephone',
                'regex:/^\+221(77|78|70|76|75)[0-9]{7}$/',
            ],
            'client.adresse' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Le type de compte est requis',
            'soldeInitial.required' => 'Le solde initial est requis',
            'soldeInitial.min' => 'Le solde initial doit être supérieur ou égal à 10000',
            'devise.required' => 'La devise est requise',
            'solde.required' => 'Le solde est requis',
            'client.required' => 'Les informations du client sont requises',
            'client.titulaire.required' => 'Le nom du titulaire est requis',
            'client.nci.required' => 'Le NCI est requis',
            'client.email.required' => 'L\'email est requis',
            'client.email.unique' => 'Cet email est déjà utilisé',
            'client.telephone.required' => 'Le numéro de téléphone est requis',
            'client.telephone.unique' => 'Ce numéro de téléphone est déjà utilisé',
            'client.telephone.regex' => 'Le numéro de téléphone doit être un numéro sénégalais valide (+22177xxxxxx, etc.)',
            'client.adresse.required' => 'L\'adresse est requise',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'message' => 'Les données fournies sont invalides',
                'details' => $validator->errors()->toArray(),
            ],
        ], 400));
    }
}
