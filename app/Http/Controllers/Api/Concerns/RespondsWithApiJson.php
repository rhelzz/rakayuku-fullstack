<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait RespondsWithApiJson
{
    protected function success(
        Request $request,
        string $message,
        mixed $data = null,
        int $status = 200,
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => $this->meta($request),
        ], $status);
    }

    protected function error(
        Request $request,
        string $message,
        array $errors = [],
        int $status = 400,
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'meta' => $this->meta($request),
        ], $status);
    }

    private function meta(Request $request): array
    {
        return [
            'timestamp' => now()->toIso8601String(),
            'path' => '/'.$request->path(),
        ];
    }
}
