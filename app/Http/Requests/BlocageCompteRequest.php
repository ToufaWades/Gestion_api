<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BlocageCompteRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'date_debut_blocage' => ['required', 'date'],
            'date_fin_blocage' => ['required', 'date', 'after:date_debut_blocage'],
            'motif_blocage' => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'date_debut_blocage.required' => 'La date de début du blocage est requise.',
            'date_fin_blocage.required' => 'La date de fin du blocage est requise.',
            'date_fin_blocage.after' => 'La date de fin doit être postérieure à la date de début.',
            'motif_blocage.required' => 'Le motif de blocage est requis.',
        ];
    }
}
