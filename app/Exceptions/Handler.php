<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\JsonResponse;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // If request expects JSON (API), return a structured JSON response with status code and message
        if ($request->expectsJson() || str_starts_with($request->getRequestUri(), '/api')) {
            $status = 500;
            if (method_exists($e, 'getStatusCode')) {
                $status = $e->getStatusCode();
            }

            $response = [
                'success' => false,
                'message' => $e->getMessage() ?: 'Server Error',
            ];

            if (config('app.debug')) {
                $response['exception'] = get_class($e);
                $response['trace'] = $e->getTrace();
            }

            return new JsonResponse($response, $status);
        }

        return parent::render($request, $e);
    }
}
