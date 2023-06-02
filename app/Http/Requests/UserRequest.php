<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use App\Actions\Fortify\PasswordValidationRules;
use App\Http\Validator;

class UserRequest extends FormRequest
{
    use PasswordValidationRules;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name'=>['required', 'string', 'max:255'],
                    'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                    'password' => $this->passwordRules(),
                    'address' => ['required', 'string'],
                    'roles' => ['required', 'string','max:255', 'in:USER,ADMIN'], //field hanya bisa diisi oleh user dan admin
                    'houseNumber' => ['required', 'string','max:255'],
                    'city' => ['required', 'string','max:255']


        ];
    }
}
