<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterUserRequest extends FormRequest
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
            'im' => 'integer|nullable|unique:users',
            'cin' => 'required|string|max:12',
            'nom_prenom' => 'required|string',
            'date_cin' => 'required|date',
            'lieu_cin' => 'required|string',
            'attribution' => 'required|string',
            'sexe' => 'required|in:M,F',
            'date_entree' => 'required|date',
            'statut' => 'required|string',
            'ref_st' => 'integer|nullable',
            'contact' => 'required|string|max:10',
            'password' => 'required|string|min:6',
            'photo' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'error' => true,
            'message' => 'Erreur de validation',
            'errorsList' => $validator->errors(),
        ], 422));
    }

    public function messages()
    {
        return [
            "im.required"=>"L'im est requis",
            "im.integer"=>"L'im doit être un entier",
            "im.unique"=>"L'im est déjà utilisé",
            "cin.required"=>"Le cin est requis",
            "cin.string"=>"Le cin doit être une chaîne de caractères",
            "cin.max"=>"Le cin ne doit pas dépasser 12 caractères",
            "nom_prenom.required"=>"Le nom et prénom sont requis",
            "nom_prenom.string"=>"Le nom et prénom doivent être une chaîne de caractères",
            "date_cin.required"=>"La date de cin est requise",
            "date_cin.date"=>"La date de cin doit être une date valide",
            "lieu_cin.required"=>"Le lieu de cin est requis",
            "lieu_cin.string"=>"Le lieu de cin doit être une chaîne de caractères",
            "attribution.required"=>"L'attribution est requise",
            "attribution.string"=>"L'attribution doit être une chaîne de caractères",
            "sexe.required"=>"Le sexe est requis",
            "sexe.in"=>"Le sexe doit être M ou F",
            "date_entree.required"=>"La date d'entrée est requise",
            "date_entree.date"=>"La date d'entrée doit être une date valide",
            "statut.required"=>"Le statut est requis",
            "statut.string"=>"Le statut doit être une chaîne de caractères",
            "ref_st.integer"=>"La référence de statut doit être un entier",
            "ref_st.nullable"=>"La référence de statut peut être null",
            "contact.required"=>"Le contact est requis",
            "contact.string"=>"Le contact doit être une chaîne de caractères",
            "contact.max"=>"Le contact ne doit pas dépasser 10 caractères",
            "password.required"=>"Le mot de passe est requis",
            "password.string"=>"Le mot de passe doit être une chaîne de caractères",
            "password.min"=>"Le mot de passe doit avoir au moins 6 caractères",
            "photo.string"=>"La photo doit être une chaîne de caractères",
            "photo.nullable"=>"La photo peut être null"
        ];
    }

}
