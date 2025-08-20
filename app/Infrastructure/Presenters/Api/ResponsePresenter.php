<?php

namespace App\Infrastructure\Presenters\Api;

use App\Infrastructure\Presenters\PresenterInterface;

/**
 * Generic API Response Presenter
 * Handles standardized API response formatting
 * Part of Infrastructure layer - outer layer implementation
 */
final class ResponsePresenter implements PresenterInterface
{
    public function present(mixed $data): array
    {
        return [
            'success' => true,
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ];
    }

    public function presentSuccess(mixed $data, string $message = 'Success'): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ];
    }

    public function presentError(string $message, mixed $errors = null, int $code = 400): array
    {
        return [
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'error_code' => $code,
            'timestamp' => now()->toISOString(),
        ];
    }

    public function presentCreated(mixed $data, string $message = 'Resource created successfully'): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ];
    }

    public function presentNotFound(string $message = 'Resource not found'): array
    {
        return [
            'success' => false,
            'message' => $message,
            'error_code' => 404,
            'timestamp' => now()->toISOString(),
        ];
    }

    public function presentPaginated(mixed $data, int $total, int $perPage, int $currentPage): array
    {
        return [
            'success' => true,
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $currentPage,
                'last_page' => ceil($total / $perPage),
                'has_more' => $currentPage < ceil($total / $perPage),
            ],
            'timestamp' => now()->toISOString(),
        ];
    }
}
