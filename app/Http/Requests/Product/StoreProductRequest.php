<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'productName' => ['required', 'string', 'max:255'],
            'productPrice' => ['required', 'numeric', 'min:0.01', 'max:999999.99', 'regex:/^\\d{1,6}(\\.\\d{1,2})?$/'],
            'productDescription' => ['required', 'string'],
            'productQuantity' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'productImage' => ['required', 'image', 'mimes:jpeg,jpg,png', 'max:5120'],
        ];
    }

    public function messages()
    {
        return [
            'productImage.image' => 'The product image must be a valid image.',
            'productImage.mimes' => 'The product image must be a JPG, JPEG, or PNG file.',
            'productImage.max' => 'The product image may not be larger than 5 MB.',
        ];
    }
}
