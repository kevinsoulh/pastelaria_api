<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
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
        // Get customer ID from route parameter
        $customerId = $this->route('customer') ?? $this->route()->parameter('id');
        
        return [
            'name' => 'sometimes|required|string|max:255|min:2',
            'email' => [
                'sometimes',
                'required',
                'string',
                'email:filter',
                'max:255',
                Rule::unique('customers')->ignore($customerId)
            ],
            'phone' => 'sometimes|required|string|max:20|min:10',
            'birth_date' => 'sometimes|nullable|date|before:today|after:1900-01-01',
            'address' => 'sometimes|required|string|max:255|min:5',
            'complement' => 'sometimes|nullable|string|max:100',
            'neighborhood' => 'sometimes|required|string|max:100|min:2',
            'zip_code' => ['sometimes', 'required', 'string', 'regex:/^\d{5}-?\d{3}$/'],
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
            'email.unique' => 'Este e-mail já está sendo utilizado por outro cliente.',
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
        // Clean and format the zip code
        if ($this->has('zip_code')) {
            $zipCode = preg_replace('/[^0-9]/', '', $this->zip_code);
            if (strlen($zipCode) === 8) {
                $this->merge([
                    'zip_code' => substr($zipCode, 0, 5) . '-' . substr($zipCode, 5)
                ]);
            }
        }
        
        // Clean and format the phone
        if ($this->has('phone')) {
            $phone = preg_replace('/[^0-9]/', '', $this->phone);
            $this->merge(['phone' => $phone]);
        }
    }
}
