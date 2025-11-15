<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
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
            'name' => 'required|string|max:255|min:2',
            'email' => 'required|string|email:filter|max:255|unique:customers,email',
            'phone' => 'required|string|max:20|min:10',
            'birth_date' => 'nullable|date|before:today|after:1900-01-01',
            'address' => 'required|string|max:255|min:5',
            'complement' => 'nullable|string|max:100',
            'neighborhood' => 'required|string|max:100|min:2',
            'zip_code' => ['required', 'string', 'regex:/^\d{5}-?\d{3}$/'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.min' => 'O nome deve ter pelo menos 2 caracteres.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'O e-mail deve ser válido.',
            'email.unique' => 'Este e-mail já está sendo utilizado.',
            'phone.required' => 'O telefone é obrigatório.',
            'phone.min' => 'O telefone deve ter pelo menos 10 dígitos.',
            'birth_date.before' => 'A data de nascimento deve ser anterior a hoje.',
            'birth_date.after' => 'A data de nascimento deve ser posterior a 1900.',
            'address.required' => 'O endereço é obrigatório.',
            'address.min' => 'O endereço deve ter pelo menos 5 caracteres.',
            'neighborhood.required' => 'O bairro é obrigatório.',
            'neighborhood.min' => 'O bairro deve ter pelo menos 2 caracteres.',
            'zip_code.required' => 'O CEP é obrigatório.',
            'zip_code.regex' => 'O CEP deve estar no formato 12345-678 ou 12345678.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('zip_code')) {
            $zipCode = preg_replace('/[^0-9]/', '', $this->zip_code);
            if (strlen($zipCode) === 8) {
                $this->merge([
                    'zip_code' => substr($zipCode, 0, 5) . '-' . substr($zipCode, 5)
                ]);
            }
        }
        
        if ($this->has('phone')) {
            $phone = preg_replace('/[^0-9]/', '', $this->phone);
            $this->merge(['phone' => $phone]);
        }
        
        if ($this->has('email')) {
            $this->merge(['email' => strtolower(trim($this->email))]);
        }
        
        if ($this->has('name')) {
            $this->merge(['name' => ucwords(strtolower(trim($this->name)))]);
        }
    }
}
