# API: Create Order

## Endpoint

```
POST /api/orders
```

## Description

Creates a new order with customer information and order items.

## Request Headers

```
Content-Type: application/json
Accept: application/json
```

## Request Body

```json
{
    "customer_name": "John Doe",
    "items": [
        {
            "product_name": "MacBook Pro 16\"",
            "product_sku": "MBP-16-001",
            "quantity": 1,
            "unit_price": 2499.99,
            "description": "Apple MacBook Pro 16-inch with M3 Pro chip"
        },
        {
            "product_name": "Magic Mouse 2",
            "product_sku": "MM-002",
            "quantity": 2,
            "unit_price": 79.99,
            "description": "Wireless Magic Mouse 2"
        }
    ]
}
```

## Field Specifications

### Root Level

| Field           | Type   | Required | Validation                                           | Description          |
| --------------- | ------ | -------- | ---------------------------------------------------- | -------------------- |
| `customer_name` | string | ✅       | 2-100 chars, letters/spaces/hyphens/apostrophes only | Customer's full name |
| `items`         | array  | ✅       | Min 1 item                                           | Array of order items |

### Items Array

| Field          | Type    | Required | Validation     | Description                              |
| -------------- | ------- | -------- | -------------- | ---------------------------------------- |
| `product_name` | string  | ✅       | 1-255 chars    | Human-readable product name              |
| `product_sku`  | string  | ✅       | 1-100 chars    | Stock keeping unit (unique product code) |
| `quantity`     | integer | ✅       | Min: 1         | Number of items ordered                  |
| `unit_price`   | numeric | ✅       | Min: 0         | Price per unit                           |
| `description`  | string  | ❌       | Max: 500 chars | Optional product description             |

## Response

### Success (201 Created)

```json
{
    "status": "success",
    "status_code": 201,
    "data": {
        "order_id": 123,
        "total_price": {
            "amount": 2659.97,
            "currency": "USD",
            "formatted": "USD 2,659.97"
        },
        "message": "Order created successfully"
    },
    "message": "Order created successfully"
}
```

### Error (422 Unprocessable Entity)

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "customer_name": ["The customer name field is required."],
        "items.0.product_name": ["The product name field is required."],
        "items.0.quantity": ["The quantity must be at least 1."]
    }
}
```

## Business Rules

1. **Order Requirements**:

    - Must contain at least one item
    - Customer name must be valid format
    - All items must have valid data

2. **Item Requirements**:

    - Product name and SKU cannot be empty
    - Quantity must be at least 1
    - Unit price cannot be negative

3. **Order Status**:
    - New orders default to "pending" status
    - Order ID is auto-generated

## Example Usage

### cURL

```bash
curl -X POST http://localhost:8000/api/orders \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "customer_name": "Alice Johnson",
    "items": [
      {
        "product_name": "Laptop",
        "product_sku": "LAP-001",
        "quantity": 1,
        "unit_price": 999.99,
        "description": "High-performance laptop"
      }
    ]
  }'
```

### JavaScript (Fetch API)

```javascript
const response = await fetch("/api/orders", {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
    },
    body: JSON.stringify({
        customer_name: "Bob Smith",
        items: [
            {
                product_name: "Mouse",
                product_sku: "MOU-001",
                quantity: 2,
                unit_price: 29.99,
            },
        ],
    }),
});

const result = await response.json();
console.log("Order created:", result.data);
```

### PHP

```php
$response = Http::post('/api/orders', [
    'customer_name' => 'Charlie Brown',
    'items' => [
        [
            'product_name' => 'Keyboard',
            'product_sku' => 'KEY-001',
            'quantity' => 1,
            'unit_price' => 89.99,
            'description' => 'Mechanical keyboard'
        ]
    ]
]);

$order = $response->json()['data'];
echo "Order ID: " . $order['order_id'];
echo "Total: " . $order['total_price']['formatted'];
```

## Notes

-   The API automatically calculates the total price based on item quantities and unit prices
-   Order items are validated to ensure they meet business requirements
-   Customer names are automatically formatted and validated
-   All monetary values are handled as USD by default
-   The response includes both the raw data and formatted display values
