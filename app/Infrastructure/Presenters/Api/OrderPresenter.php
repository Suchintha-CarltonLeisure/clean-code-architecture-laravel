<?php

namespace App\Infrastructure\Presenters\Api;

use App\Domain\Order\Entities\Order;
use App\Infrastructure\Presenters\PresenterInterface;

/**
 * Order Presenter for API responses
 * Part of Infrastructure layer - handles output formatting
 * Converts domain objects to API-specific response format
 */
final class OrderPresenter implements PresenterInterface
{
    public function present(mixed $data): array
    {
        if ($data instanceof Order) {
            return $this->presentSingle($data);
        }

        if (is_array($data)) {
            return $this->presentCollection($data);
        }

        throw new \InvalidArgumentException('OrderPresenter can only present Order entities or arrays of Orders');
    }

    private function presentSingle(Order $order): array
    {
        return [
            'id' => $order->getId()->getValue(),
            'customer' => [
                'name' => $order->getCustomerName()->getValue(),
                'first_name' => $order->getCustomerName()->getFirstName(),
                'last_name' => $order->getCustomerName()->getLastName(),
                'initials' => $order->getCustomerName()->getInitials(),
            ],
            'items' => $this->presentItems($order->getItems()),
            'pricing' => [
                'total' => $order->totalPrice()->toArray(),
                'formatted_total' => $order->totalPrice()->format(),
                'currency' => $order->totalPrice()->getCurrency(),
            ],
            'status' => [
                'code' => $order->getStatus()->getValue(),
                'label' => $this->getStatusLabel($order->getStatus()->getValue()),
            ],
            'summary' => [
                'item_count' => $order->getItemCount(),
                'total_amount' => $order->totalPrice()->getAmount(),
            ]
        ];
    }

    private function presentCollection(array $orders): array
    {
        return [
            'orders' => array_map([$this, 'presentSingle'], $orders),
            'meta' => [
                'count' => count($orders),
                'total_value' => $this->calculateTotalValue($orders),
            ]
        ];
    }

    private function presentItems(array $items): array
    {
        return array_map(function ($item) {
            return [
                'id' => $item->getId()->getValue(),
                'product' => [
                    'name' => $item->getProductName(),
                    'sku' => $item->getProductSku(),
                    'description' => $item->getDescription(),
                ],
                'quantity' => $item->getQuantity(),
                'pricing' => [
                    'unit_price' => $item->getUnitPrice()->toArray(),
                    'total_price' => $item->getTotalPrice()->toArray(),
                    'formatted_unit_price' => $item->getUnitPrice()->format(),
                    'formatted_total_price' => $item->getTotalPrice()->format(),
                ]
            ];
        }, $items);
    }

    private function getStatusLabel(string $status): string
    {
        return match($status) {
            'pending' => 'Pending Confirmation',
            'confirmed' => 'Order Confirmed',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            default => ucfirst($status)
        };
    }

    private function calculateTotalValue(array $orders): float
    {
        return array_reduce($orders, function ($total, Order $order) {
            return $total + $order->totalPrice()->getAmount();
        }, 0.0);
    }
}
