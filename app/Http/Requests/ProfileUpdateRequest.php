<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="ProfileUpdateRequest",
 *     type="object",
 *     title="Profile Update Request",
 *     description="Request for updating user profile"
 * )
 */
class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            /**
             * @OA\Property(
             *     property="name",
             *     type="string",
             *     description="User's name",
             *     maxLength=255
             * )
             */
            'name' => ['required', 'string', 'max:255'],

            /**
             * @OA\Property(
             *     property="email",
             *     type="string",
             *     format="email",
             *     description="User's email address",
             *     maxLength=255
             * )
             */
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
        ];
    }
}
