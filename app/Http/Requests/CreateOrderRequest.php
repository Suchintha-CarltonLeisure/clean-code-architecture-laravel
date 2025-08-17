<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Domain\Order\ValueObjects\CustomerName;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => 'required|string|min:2|max:100|regex:/^[a-zA-Z\s\'-]+$/',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string|min:1|max:255',
            'items.*.product_sku' => 'required|string|min:1|max:100',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.description' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.regex' => 'Customer name can only contain letters, spaces, hyphens, and apostrophes.',
        ];
    }

    public function getCustomerName(): CustomerName
    {
        return CustomerName::fromString($this->input('customer_name'));
    }
}
