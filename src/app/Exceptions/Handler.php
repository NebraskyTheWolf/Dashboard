<?php

namespace App\Exceptions;

use App\Mail\ApplicationError;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array<int, string>
     */
    protected $dontReport = [
        ValidationException::class,
        ThrottleRequestsException::class,
        PostTooLargeException::class,
        ValidationException::class
    ];

    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Registers an error handler for the application.
     *
     * @return void
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if(!in_array(get_class($e), $this->dontReport)){
                $this->sendAlert($e);
            }
        });
    }

    /**
     * Report the given exception.
     *
     * @param Throwable $e The exception to be reported.
     *
     * @return void
     */
    public function report(Throwable $e)
    {
        if (!in_array(get_class($e), $this->dontReport) || $this->shouldntReport($e)) {
            $this->sendAlert($e);
        }

        parent::report($e);
    }

    /**
     * Render the given exception.
     *
     * This method is responsible for rendering the exception into an appropriate
     * response. If the exception is an instance of HttpException, it will return a
     * view response based on the status code of the exception. Otherwise, it will
     * call the parent's render method to handle the exception.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $e
     */
    public function render($request, Throwable $e)
    {
        if ($this->isHttpException($e)) {
            if ($e->getStatusCode() == 404) {

                if (Auth::guest()) {
                    return redirect()->route('login');
                } else {
                    return response()->view('errors.404', [], 404);
                }
            }

            if ($e->getStatusCode() == 500) {
                return response()->view('errors.500', [], 500);
            }

            if ($e->getStatusCode() == 403) {
                if (Auth::guest()) {
                    return redirect()->route('login');
                } else {
                    return response()->view('errors.403', [], 403);
                }
            }
        }
        return parent::render($request, $e);
    }

    private function sendAlert(Throwable $e)
    {

    }
}
