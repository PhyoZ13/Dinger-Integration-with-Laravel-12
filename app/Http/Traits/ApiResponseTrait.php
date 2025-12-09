<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

trait ApiResponseTrait
{
    /**
     * Return success response
     */
    protected function successResponse(
        mixed $data = null,
        string $message = 'Operation successful',
        int $statusCode = 200
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return error response
     */
    protected function errorResponse(
        string $message = 'An error occurred',
        mixed $errors = null,
        int $statusCode = 400
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return paginated response
     */
    protected function paginatedResponse(
        AnonymousResourceCollection $resourceCollection,
        string $message = 'Data retrieved successfully'
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $resourceCollection->response()->getData()->data,
            'meta' => [
                'current_page' => $resourceCollection->resource->currentPage(),
                'per_page' => $resourceCollection->resource->perPage(),
                'total' => $resourceCollection->resource->total(),
                'last_page' => $resourceCollection->resource->lastPage(),
                'from' => $resourceCollection->resource->firstItem(),
                'to' => $resourceCollection->resource->lastItem(),
            ],
        ]);
    }

    /**
     * Return created response (201)
     */
    protected function createdResponse(
        mixed $data = null,
        string $message = 'Resource created successfully'
    ): JsonResponse {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * Return not found response (404)
     */
    protected function notFoundResponse(
        string $message = 'Resource not found'
    ): JsonResponse {
        return $this->errorResponse($message, null, 404);
    }

    /**
     * Return unauthorized response (401)
     */
    protected function unauthorizedResponse(
        string $message = 'Unauthorized'
    ): JsonResponse {
        return $this->errorResponse($message, null, 401);
    }

    /**
     * Return validation error response (422)
     */
    protected function validationErrorResponse(
        mixed $errors,
        string $message = 'Validation failed'
    ): JsonResponse {
        return $this->errorResponse($message, $errors, 422);
    }
}


