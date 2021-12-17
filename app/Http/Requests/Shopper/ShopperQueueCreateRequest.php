<?php

namespace App\Http\Requests\Shopper;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreCreateRequest
 * @package App\Http\Requests\Store
 */
class ShopperQueueCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // not required
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required'
        ];
    }
}
