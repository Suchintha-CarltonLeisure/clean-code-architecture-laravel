<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Application\Commands\CreateOrder\CreateOrderCommand;
use App\Application\Commands\CreateOrder\CreateOrderHandler;
use App\Application\Queries\GetOrder\GetOrderHandler;
use App\Application\Commands\UpdateOrder\UpdateOrderHandler;
use App\Application\Commands\DeleteOrder\DeleteOrderHandler;
use App\Application\DTOs\MoneyDTO;
use App\Application\Queries\ListOrders\ListOrdersHandler;
use App\Application\Queries\GetOrderPricing\GetOrderPricingHandler;
use App\Application\Queries\GetOrderPricing\GetOrderPricingQuery;
use App\Infrastructure\Services\PaymentService;
use App\Models\Order;
use App\Domain\Order\ValueObjects\OrderId;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private CreateOrderHandler $createHandler,
        private GetOrderHandler $getHandler,
        private UpdateOrderHandler $updateHandler,
        private DeleteOrderHandler $deleteHandler,
        private ListOrdersHandler $listHandler,
        private GetOrderPricingHandler $pricingHandler,
        private ?PaymentService $paymentService = null
    ) {}

    public function store(CreateOrderRequest $req)
    {
        $cmd = new CreateOrderCommand($req->input('items'), $req->getCustomerName());
        $res = $this->createHandler->handle($cmd);
        return response()->json($res->toArray(), 201);
    }

    public function show(int $id)
    {
        $orderId = OrderId::fromInt($id);
        $res = $this->getHandler->handle(new \App\Application\Queries\GetOrder\GetOrderQuery($orderId));
        if (!$res) return response()->json(['message' => 'Not found'], 404);
        return response()->json($res->toArray());
    }

    public function index()
    {
        $list = $this->listHandler->handle(new \App\Application\Queries\ListOrders\ListOrdersQuery(15, 1));
        return response()->json($list);
    }

    public function update(\App\Http\Requests\UpdateOrderRequest $req, int $id)
    {
        $orderId = OrderId::fromInt($id);
        $cmd = new \App\Application\Commands\UpdateOrder\UpdateOrderCommand($orderId, $req->input('items'));
        $updated = $this->updateHandler->handle($cmd);
        if (!$updated) return response()->json(['message' => 'Not found'], 404);
        return response()->json($updated);
    }

    public function destroy(int $id)
    {
        $orderId = OrderId::fromInt($id);
        $ok = $this->deleteHandler->handle(new \App\Application\Commands\DeleteOrder\DeleteOrderCommand($orderId));
        return response()->json(['deleted' => $ok]);
    }

    public function processPayment(Request $request, int $orderId)
    {
        if (!$this->paymentService) {
            return response()->json(['error' => 'Payment service not available'], 503);
        }

        $order = Order::find($orderId);
        $amount = new MoneyDTO($request->input('amount'));
        $paymentMethod = $request->input('payment_method');

        $result = $this->paymentService->processPayment($order, $amount, $paymentMethod);

        return response()->json($result);
    }

    /**
     * Get order pricing with discounts using Domain Service
     */
    public function getPricing(int $id)
    {
        try {
            $orderId = OrderId::fromInt($id);
            $query = new GetOrderPricingQuery($orderId);
            
            $response = $this->pricingHandler->handle($query);
            
            if (!$response) {
                return response()->json(['error' => 'Order not found'], 404);
            }

            return response()->json($response->toArray());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
