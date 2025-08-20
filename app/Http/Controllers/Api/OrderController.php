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
use App\Infrastructure\Presenters\Api\OrderPresenter;
use App\Infrastructure\Presenters\Api\ResponsePresenter;
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
        private OrderPresenter $orderPresenter,
        private ResponsePresenter $responsePresenter,
        private ?PaymentService $paymentService = null
    ) {}

    public function store(CreateOrderRequest $req)
    {
        try {
            $cmd = new CreateOrderCommand($req->input('items'), $req->getCustomerName());
            $res = $this->createHandler->handle($cmd);

            // Get the created order for presentation
            $order = $this->getHandler->handle(
                new \App\Application\Queries\GetOrder\GetOrderQuery(OrderId::fromInt($res->orderId))
            );

            $presentedData = $this->orderPresenter->present($order);
            $response = $this->responsePresenter->presentCreated($presentedData, 'Order created successfully');

            return response()->json($response, 201);
        } catch (\Exception $e) {
            $errorResponse = $this->responsePresenter->presentError('Failed to create order: ' . $e->getMessage());
            return response()->json($errorResponse, 500);
        }
    }

    public function show(int $id)
    {
        try {
            $orderId = OrderId::fromInt($id);
            $order = $this->getHandler->handle(new \App\Application\Queries\GetOrder\GetOrderQuery($orderId));

            if (!$order) {
                $errorResponse = $this->responsePresenter->presentNotFound('Order not found');
                return response()->json($errorResponse, 404);
            }

            $presentedData = $this->orderPresenter->present($order);
            $response = $this->responsePresenter->presentSuccess($presentedData, 'Order retrieved successfully');

            return response()->json($response);
        } catch (\Exception $e) {
            $errorResponse = $this->responsePresenter->presentError('Failed to retrieve order: ' . $e->getMessage());
            return response()->json($errorResponse, 500);
        }
    }

    public function index()
    {
        $list = $this->listHandler->handle(new \App\Application\Queries\ListOrders\ListOrdersQuery(15, 1));
        return response()->json($list);
    }

    public function update(\App\Http\Requests\UpdateOrderRequest $req, int $id)
    {
        try {
            $orderId = OrderId::fromInt($id);
            $cmd = new \App\Application\Commands\UpdateOrder\UpdateOrderCommand($orderId, $req->input('items'), $req->input('customer_name'));
            $updated = $this->updateHandler->handle($cmd);
            
            if (!$updated) {
                $errorResponse = $this->responsePresenter->presentNotFound('Order not found');
                return response()->json($errorResponse, 404);
            }

            // Get the updated order for presentation
            $order = $this->getHandler->handle(
                new \App\Application\Queries\GetOrder\GetOrderQuery($orderId)
            );

            $presentedData = $this->orderPresenter->present($order);
            $response = $this->responsePresenter->presentSuccess($presentedData, 'Order updated successfully');

            return response()->json($response, 200);
        } catch (\Exception $e) {
            $errorResponse = $this->responsePresenter->presentError('Failed to update order: ' . $e->getMessage());
            return response()->json($errorResponse, 500);
        }
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

            $pricingResponse = $this->pricingHandler->handle($query);

            if (!$pricingResponse) {
                $errorResponse = $this->responsePresenter->presentNotFound('Order not found');
                return response()->json($errorResponse, 404);
            }

            $response = $this->responsePresenter->presentSuccess(
                $pricingResponse->toArray(),
                'Order pricing calculated successfully'
            );

            return response()->json($response);
        } catch (\Exception $e) {
            $errorResponse = $this->responsePresenter->presentError('Failed to calculate pricing: ' . $e->getMessage());
            return response()->json($errorResponse, 500);
        }
    }
}
