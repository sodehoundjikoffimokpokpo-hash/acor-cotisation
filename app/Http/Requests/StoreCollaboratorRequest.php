<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreCollaboratorRequest extends FormRequest
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
            'firstname' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username|max:255',
            'password' => ['required', 'string', Password::min(8)->letters()->numbers()],
            'role' => 'required|in:secretary,employee,admin',
            'title' => 'nullable|string|max:255',
            'country_id' => 'nullable|exists:countries,id',
        ];
    }
}
