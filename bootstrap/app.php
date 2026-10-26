<?php

use App\Modules\Core\Http\Middleware\Authenticate;
use App\Modules\Core\Support\ApiEnvelope;
use App\Modules\Page\Http\Middleware\ShareModulePageContext;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth'       => Authenticate::class,
            'share.page' => ShareModulePageContext::class,
        ]);

        // Must run after the session has started (StartSession) so services it
        // depends on (auth, cart) work correctly, regardless of unrelated
        // middleware ordering in module route groups.
        $middleware->appendToPriorityList(
            after: StartSession::class,
            append: ShareModulePageContext::class,
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->render(function (Throwable $e, $request) {

            if (!$request->is('api/*')) {
                return null;
            }

            if ($e instanceof ValidationException) {
                return ApiEnvelope::error(__('site.api.validation_error'), $e->errors(), 422);
            }

            if ($e instanceof AuthenticationException) {
                return ApiEnvelope::error(__('site.api.unauthenticated'), status: 401);
            }

            if ($e instanceof AccessDeniedHttpException) {
                return ApiEnvelope::error(__('site.api.forbidden'), status: 403);
            }

            if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
                return ApiEnvelope::error(__('site.api.resource_not_found'), status: 404);
            }

            if ($e instanceof ThrottleRequestsException) {
                return ApiEnvelope::error(__('site.api.to_many_requests'), status: 429);
            }

            if ($e instanceof HttpExceptionInterface) {
                return ApiEnvelope::error(
                    $e->getMessage() ? t('errors.something_went_wrong') : Response::$statusTexts[$e->getStatusCode()],
                    status: $e->getStatusCode(),
                );
            }

            report($e);

            // The trace is a debugging aid, not part of the contract: in
            // production the client is told only that something went wrong.
            return ApiEnvelope::error(
                app()->hasDebugModeEnabled()
                    ? $e->getMessage()
                    : t('errors.something_went_wrong'),
                app()->hasDebugModeEnabled()
                    ? [
                        'exception' => class_basename($e),
                        'trace'     => collect($e->getTrace())->take(5),
                    ]
                    : null,
                500,
            );
        });
    })->create();
