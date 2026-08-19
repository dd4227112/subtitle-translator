<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WebLoginRequest extends FormRequest
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
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'login.required' => 'Please enter your email or phone number.',
            'password.required' => 'Password is required.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'login' => trim($this->login ?? ''),
        ]);
    }

    /**
     * Determine whether the login field is an email or phone number.
     */
    public function loginField(): string
    {
        return filter_var($this->input('login'), FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
    }
}
