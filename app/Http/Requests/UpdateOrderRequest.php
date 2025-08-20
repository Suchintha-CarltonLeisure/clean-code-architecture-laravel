<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'customer_name' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'sometimes|string|uuid',
            'items.*.product_name' => 'required|string',
            'items.*.product_sku' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.description' => 'nullable|string',
        ];
    }
}
