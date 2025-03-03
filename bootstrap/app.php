<?php

use App\Exceptions\AccessPermissionException;
use App\Exceptions\ApiException;
use App\Exceptions\DataNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->use([
            \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
            \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
            \App\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
            \App\Http\Middleware\TrustProxies::class,
            \App\Http\Middleware\RedirectToHttps::class,
        ]);

        $middleware->group('web', [
            // \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            // \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
             \Illuminate\Routing\Middleware\SubstituteBindings::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \App\Http\Middleware\SetTenantMiddlware::class,
            \App\Http\Middleware\SetRequestId::class,
            \App\Http\Middleware\LogRequestContent::class,
        ]);

        $middleware->group('api', [
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            // 'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        /*
        $middleware->use([
            // \Illuminate\Http\Middleware\TrustHosts::class,
            \App\Http\Middleware\EncryptCookies::class,
            \App\Http\Middleware\TrustProxies::class,
            \Illuminate\Http\Middleware\HandleCors::class,
            \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \Illuminate\Http\Middleware\ValidatePostSize::class,
            \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        ]);
        */

        //$middleware->alias(['guest' => \App\Http\Middleware\RedirectIfAuthenticated::class]);
        //$middleware->alias(['auth' => null]);
        //$middleware->alias(['auth.basic' => null]);
        //$middleware->alias(['auth.session' => null]);
        //$middleware->remove(\Illuminate\Auth\Middleware\Authenticate::class);
        $middleware->alias(['custom_auth' => \App\Http\Middleware\Auth::class]);
        $middleware->web(replace: [
            \Illuminate\Auth\Middleware\Authenticate::class => \App\Http\Middleware\Auth::class,
        ]);

        $middleware->priority([
            \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            \Illuminate\Auth\Middleware\Authorize::class,
            \App\Http\Middleware\Auth::class,
            \App\Http\Middleware\SetRequestId::class,
            \App\Http\Middleware\LogRequestContent::class,
            \App\Http\Middleware\SetTenantMiddlware::class,
        ]);


    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
        $exceptions->render(function (Throwable $exception) {
            $errorMessage = $exception->getMessage();
            Log::error('--- bootstrap exception start ---');
            Log::error('errorMessage : ' . $errorMessage);
            Log::error($exception->getTraceAsString());
            Log::error('--- bootstrap exception end ---');
            switch(get_class($exception))
            {
                case \Illuminate\Session\TokenMismatchException::class:
                    // CSRFトークン不一致
                    $errorMessage = __('messages.error.csrf_token_mismatch');
                    break;
                case AccessPermissionException::class:
                    // 画面権限無し
                    $errorMessage = __('messages.error.access_permission');
                    break;
                case DataNotFoundException::class:
                    // 該当データなし
                    $errorMessage = $exception->getMessage();
                    break;
                case ApiException::class:
                    // API接続エラー
                    $errorMessage = __('messages.error.api_error');
                    break;
                case ValidationException::class:
                    // $exceptionをValidationExceptionとして扱い、エラーメッセージを取得する
                    // Log::info('ValidationException', $exception->errors());
                    // 無視する
                    return;
                    break;
                case TypeError::class:
                    // 無視する
                    $errorMessage = __('messages.error.invalid_parameter');
                    break;
                case PDOException::class:
                    // 本番環境ではDBエラーを表示しない
                    if(app()->environment('production'))
                    {
                        $errorMessage = __('messages.error.db_error');
                    }else{
                        $errorMessage = $exception->getMessage();
                    }
                    break;
                default:
                    // その他
                    // HTTPステータスコードエラーならここに書く
                    if($exception instanceof HttpExceptionInterface)
                    {
                        if($exception->getStatusCode() == 404)
                        {
                            $errorMessage = __('messages.error.404_not_found');
                        }else{
                            $errorMessage = $exception->getMessage();
                        }
                    }
                    break;
            }
            return response()->view('errors.error', [
                'errorMessage' => $errorMessage,
            ]);
        });
    })->create();
