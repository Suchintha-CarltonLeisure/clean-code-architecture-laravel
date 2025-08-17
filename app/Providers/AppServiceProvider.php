<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Infrastructure\Repositories\EloquentOrderRepository;
use App\Infrastructure\Services\PaymentService;
use App\Domain\Order\Services\OrderPricingService;
use App\Application\Queries\GetOrderPricing\GetOrderPricingHandler;
use App\Domain\Shared\Events\EventDispatcher;
use App\Infrastructure\Events\InMemoryEventDispatcher;
use App\Domain\Customer\Events\CustomerOrderCreatedHandler;
use App\Domain\Inventory\Events\InventoryOrderCreatedHandler;
use App\Domain\Notification\Events\NotificationOrderStatusChangedHandler;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(OrderRepositoryInterface::class, EloquentOrderRepository::class);

        // Register Domain Services
        $this->app->singleton(OrderPricingService::class);
        
        // Register Application Handlers
        $this->app->bind(GetOrderPricingHandler::class);

        // Register Event System
        $this->app->singleton(EventDispatcher::class, InMemoryEventDispatcher::class);
        
        // Register Event Handlers
        $this->app->bind(CustomerOrderCreatedHandler::class);
        $this->app->bind(InventoryOrderCreatedHandler::class);
        $this->app->bind(NotificationOrderStatusChangedHandler::class);

        // Bind PaymentService with configuration values
        $this->app->bind(PaymentService::class, function ($app) {
            return new PaymentService(
                config('services.payment.api_key', 'test_key'),
                config('services.payment.endpoint', 'https://api.payment-gateway.com'),
                config('services.payment.test_mode', true)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Subscribe event handlers to the event dispatcher
        $eventDispatcher = $this->app->make(EventDispatcher::class);
        
        // Subscribe handlers for OrderCreated event
        $eventDispatcher->subscribe('order.created', $this->app->make(CustomerOrderCreatedHandler::class));
        $eventDispatcher->subscribe('order.created', $this->app->make(InventoryOrderCreatedHandler::class));
        
        // Subscribe handlers for OrderStatusChanged event
        $eventDispatcher->subscribe('order.status_changed', $this->app->make(NotificationOrderStatusChangedHandler::class));
    }
}
