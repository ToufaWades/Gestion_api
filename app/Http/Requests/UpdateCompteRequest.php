<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'titulaire' => 'sometimes|required|string|max:255',
            'informationsClient' => 'sometimes|required|array',
            'informationsClient.telephone' => [
                'sometimes',
                'required',
                'string',
                'regex:/^\+221(77|78|70|76|75)[0-9]{7}$/',
                Rule::unique('users', 'telephone')->ignore($this->user()->id ?? null),
            ],
            'informationsClient.email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->user()->id ?? null),
            ],
            'informationsClient.password' => [
                'sometimes',
                'required',
                'string',
                'min:10',
                'regex:/^(?=.*[A-Z])(?=.*[a-z].*[a-z])(?=.*[!@#$%^&*(),.?":{}|<>].*[!@#$%^&*(),.?":{}|<>])/',
            ],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check if at least one field is provided
            $hasTitulaire = $this->has('titulaire');
            $hasClientInfo = $this->has('informationsClient') &&
                           ($this->has('informationsClient.telephone') ||
                            $this->has('informationsClient.email') ||
                            $this->has('informationsClient.password'));

            if (!$hasTitulaire && !$hasClientInfo) {
                $validator->errors()->add('general', 'Au moins un champ doit être fourni pour la mise à jour.');
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'titulaire.required' => 'Le titulaire est requis.',
            'titulaire.string' => 'Le titulaire doit être une chaîne de caractères.',
            'titulaire.max' => 'Le titulaire ne peut pas dépasser 255 caractères.',

            'informationsClient.required' => 'Les informations client sont requises.',
            'informationsClient.array' => 'Les informations client doivent être un objet.',

            'informationsClient.telephone.required' => 'Le numéro de téléphone est requis.',
            'informationsClient.telephone.regex' => 'Le numéro de téléphone doit être un numéro sénégalais valide (+22177xxxxxx, etc.).',
            'informationsClient.telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',

            'informationsClient.email.required' => 'L\'email est requis.',
            'informationsClient.email.email' => 'L\'email doit être une adresse email valide.',
            'informationsClient.email.unique' => 'Cet email est déjà utilisé.',

            'informationsClient.password.required' => 'Le mot de passe est requis.',
            'informationsClient.password.min' => 'Le mot de passe doit contenir au moins 10 caractères.',
            'informationsClient.password.regex' => 'Le mot de passe doit commencer par une majuscule, contenir au moins 2 minuscules et 2 caractères spéciaux.',
        ];
    }
}
