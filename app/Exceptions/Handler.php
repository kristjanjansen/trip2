<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\QueryException;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            abort(404);
        }

        if (strpos(config('app.debug_ips'), ',') !== false) {
            $ips = explode(',', config('app.debug_ips'));
        } else {
            $ips = [];
        }

        if (in_array(request()->ip(), $ips)) {
            Config::set('app.debug', true);
        }

        if (! config('app.debug') && ($e instanceof \ErrorException || $e instanceof FatalErrorException || $e instanceof QueryException)) {
            try {
                return response()->view('errors.500', [], 500);
            } catch (Exception $e) {
                echo '<h1 style="width: 40vw; padding: 3rem; font-size: 2em; font-family: sans-serif; color: hsl(0, 79%, 66%);">Tripil on tehnilised probleemid. Oleme varsti tagasi.</h1>';
                exit();
            }
        } else {
            return parent::render($request, $e);
        }
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest('login');
    }
}
