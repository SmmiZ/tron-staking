<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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
        });
        $this->renderable(function (AuthenticationException $e, Request $request) {
            return $request->expectsJson() ? response()->json([
                'status' => false,
                'error' => $e->getMessage(),
                'errors' => [
                    'essence' => ['Unauthenticated.']
                ],
            ], 401) : new Response(view('errors.401'), 401);
        });
        $this->renderable(function (AccessDeniedHttpException $e, Request $request) {
            return $request->expectsJson() ? response()->json([
                'status' => false,
                'error' => $e->getMessage(),
                'errors' => [
                    'essence' => ['Forbidden.']
                ],
            ], 403) : new Response(view('errors.403'), 403);
        });
        $this->renderable(function (NotFoundHttpException $e, Request $request) {
            return $request->wantsJson() ? new Response([
                'status' => false,
                'error' => 'Cannot Find',
                'errors' => [
                    'essence' => ['Cannot Find']
                ]
            ], 404) : new Response(view('errors.404'), 404);
        });
        $this->renderable(function (ValidationException $e, Request $request) {
            return $request->wantsJson() ? new Response([
                'status' => false,
                'error' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422) : new Response(view('errors.422'), 422);
        });


        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                $error = config('app.debug') ? $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() : $e->getMessage();
                return response([
                    'status' => false,
                    'error' => $error,
                    'errors' => (object)$e->getPrevious(),
                ], 500);
            }
            return new Response(view('errors.500'), 500);
        });
    }
}
