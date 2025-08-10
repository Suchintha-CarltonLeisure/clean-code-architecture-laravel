<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Application\Commands\CreateOrder\CreateOrderCommand;
use App\Application\Commands\CreateOrder\CreateOrderHandler;
use App\Application\Queries\GetOrder\GetOrderHandler;
use App\Application\Commands\UpdateOrder\UpdateOrderHandler;
use App\Application\Commands\DeleteOrder\DeleteOrderHandler;
use App\Application\Queries\ListOrders\ListOrdersHandler;

class OrderController extends Controller
{
    public function __construct(
        private CreateOrderHandler $createHandler,
        private GetOrderHandler $getHandler,
        private UpdateOrderHandler $updateHandler,
        private DeleteOrderHandler $deleteHandler,
        private ListOrdersHandler $listHandler
    ) {}

    public function store(CreateOrderRequest $req)
    {
        $cmd = new CreateOrderCommand($req->input('items'), $req->input('customer_name'));
        $res = $this->createHandler->handle($cmd);
        return response()->json($res->toArray(), 201);
    }

    public function show(int $id)
    {
        $res = $this->getHandler->handle(new \App\Application\Queries\GetOrder\GetOrderQuery($id));
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
        $cmd = new \App\Application\Commands\UpdateOrder\UpdateOrderCommand($id, $req->input('items'));
        $updated = $this->updateHandler->handle($cmd);
        if (!$updated) return response()->json(['message' => 'Not found'], 404);
        return response()->json($updated);
    }

    public function destroy(int $id)
    {
        $ok = $this->deleteHandler->handle(new \App\Application\Commands\DeleteOrder\DeleteOrderCommand($id));
        return response()->json(['deleted' => $ok]);
    }
}
