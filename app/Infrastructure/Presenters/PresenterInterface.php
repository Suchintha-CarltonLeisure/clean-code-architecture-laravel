<?php

namespace App\Infrastructure\Presenters;

/**
 * Base presenter interface for Clean Architecture
 * Presenters are part of the Infrastructure layer and handle
 * the formatting of data for specific output formats (API, Web, etc.)
 */
interface PresenterInterface
{
    public function present(mixed $data): array;
}
