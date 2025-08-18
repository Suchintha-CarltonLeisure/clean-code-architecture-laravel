<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Infrastructure\Repositories\EloquentOrderRepository;
use App\Infrastructure\Services\PaymentService;
use App\Domain\Order\Services\OrderPricingService;
use App\Application\Queries\GetOrderPricing\GetOrderPricingHandler;
use App\Infrastructure\Presenters\Api\OrderPresenter;
use App\Infrastructure\Presenters\Api\ResponsePresenter;


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
        
        // Register Infrastructure Presenters
        $this->app->bind(OrderPresenter::class);
        $this->app->bind(ResponsePresenter::class);

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
        //
    }
}
