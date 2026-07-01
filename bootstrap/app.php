<?php

use App\Modules\Core\Http\Middleware\Authenticate;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth' => Authenticate::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('basket:clear-abandoned')->daily();
    })
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->render(function (Throwable $e, $request) {

            if (!$request->is('api/*')) {
                return null;
            }

            if ($e instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => t('api.validation_error'),
                    'errors' => $e->errors(),
                ], 422);
            }

            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'success' => false,
                    'message' =>t('api.unauthenticated'),
                    'errors' => null,
                ], 401);
            }

            if ($e instanceof AccessDeniedHttpException) {
                return response()->json([
                    'success' => false,
                    'message' => t('api.forbidden'),
                    'errors' => null,
                ], 403);
            }

            if ($e instanceof ModelNotFoundException) {
                return response()->json([
                    'success' => false,
                    'message' => t('api.resource_not_found'),
                    'errors' => null,
                ], 404);
            }

            if ($e instanceof NotFoundHttpException) {
                return response()->json([
                    'success' => false,
                    'message' => t('api.resource_not_found'),
                    'errors' => null,
                ], 404);
            }

            if ($e instanceof ThrottleRequestsException) {
                return response()->json([
                    'success' => false,
                    'message' => t('api.to_many_requests'),
                    'errors' => null,
                ], 429);
            }

            if ($e instanceof HttpExceptionInterface) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ? t('errors.something_went_wrong') : Response::$statusTexts[$e->getStatusCode()],
                    'errors' => null,
                ], $e->getStatusCode());
            }

            report($e);

            return response()->json([
                'success' => false,
                'message' => app()->hasDebugModeEnabled()
                    ? $e->getMessage()
                    : t('errors.something_went_wrong'),
                'errors' => app()->hasDebugModeEnabled()
                    ? [
                        'exception' => class_basename($e),
                        'trace' => collect($e->getTrace())
                            ->take(5),
                    ]
                    : null,
            ], 500);
        });
    })->create();
