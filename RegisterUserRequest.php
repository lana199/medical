<?php

namespace App\Http\Requests;

use App\Enums\GenderEnum;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;


class RegisterUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'name' => ['required', 'string'],
            'mobile' => ['required', 'string'],
            'location' => ['required', 'string'],
            'image_path' => ['required','image'],
            'gender'=>['required','integer',Rule::in(GenderEnum::getValues())],
            'age'=>['required','integer','max:100'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException( response()->json($validator->getMessageBag()));
    }
}
