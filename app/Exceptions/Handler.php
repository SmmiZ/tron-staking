<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\{AccessDeniedHttpException, NotFoundHttpException};
use Throwable;

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
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (Throwable $e, Request $request) {
            if (!$request->is('api/*')) return null;

            $data = match (get_class($e)) {
                AuthenticationException::class => ['code' => 401, 'error' => $e->getMessage(), 'errors' => ['essence' => ['Unauthenticated.']]],
                AccessDeniedHttpException::class => ['code' => 403, 'error' => $e->getMessage(), 'errors' => ['essence' => ['Forbidden.']]],
                NotFoundHttpException::class => ['code' => 404, 'error' => 'Cannot Find', 'errors' => ['essence' => ['Cannot Find.']]],
                ValidationException::class => ['code' => 422, 'error' => $e->getMessage(), 'errors' => $e->errors()],
                default => ['code' => 500, 'error' => $e->getMessage(), 'errors' => (object)$e->getPrevious()],
            };

            return response()->json([
                'status' => false,
                'error' => $data['error'],
                'errors' => $data['errors'],
            ], $data['code']);
        });
    }
}
