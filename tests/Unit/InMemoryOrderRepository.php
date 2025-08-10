<?php
use App\Domain\Models\Order\Order;
use App\Domain\Repositories\OrderRepositoryInterface;

class InMemoryOrderRepository implements OrderRepositoryInterface {
    private array $store = [];
    private int $next = 1;
    public function save(Order $order): Order {
        if (!$order->getId()) {
            $order->setId($this->next++);
        }
        $this->store[$order->getId()] = $order;
        return $order;
    }
    public function findById(int $id): ?Order { return $this->store[$id] ?? null; }
    public function deleteById(int $id): bool { return (bool) (isset($this->store[$id]) && (unset($this->store[$id]) || true)); }
    public function list(int $perPage = 15, int $page = 1): array { return array_map(fn($o) => $o->toArray(), array_values($this->store)); }
}
