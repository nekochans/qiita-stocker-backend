<?php

namespace App\Exceptions;

use Exception;
use App\Models\Domain\ErrorResponseEntityBuilder;
use App\Models\Domain\exceptions\ValidationException;
use App\Models\Domain\exceptions\AccountCreatedException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
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
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof AccountCreatedException) {
            $errorResponseEntityBuilder = new ErrorResponseEntityBuilder();
            $errorResponseEntityBuilder->setErrorMessage($exception->getMessage());
            $errorResponseEntityBuilder->setErrorCode($exception->getCode());
            $errorResponseEntity = $errorResponseEntityBuilder->build();

            return response()->json($errorResponseEntity->buildBody(), $errorResponseEntity->getErrorCode());
        }

        if ($exception instanceof ValidationException) {
            $errorResponseEntityBuilder = new ErrorResponseEntityBuilder();
            $errorResponseEntityBuilder->setErrorMessage($exception->getMessage());
            $errorResponseEntityBuilder->setErrorCode($exception->getCode());
            $errorResponseEntityBuilder->setErrors($exception->getErrors());
            $errorResponseEntity = $errorResponseEntityBuilder->build();

            return response()->json($errorResponseEntity->buildBody(), $errorResponseEntity->getErrorCode());
        }
        return parent::render($request, $exception);
    }
}
