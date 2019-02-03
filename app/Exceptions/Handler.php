<?php

namespace App\Exceptions;

use Exception;
use App\Models\Domain\ErrorResponseEntity;
use App\Models\Domain\ErrorResponseEntityBuilder;
use App\Models\Domain\Exceptions\ValidationException;
use App\Models\Domain\Exceptions\BusinessLogicException;
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
        app('log')->alert(
            $exception,
            [
                'request' => [
                    'url'    => request()->fullUrl(),
                    'header' => request()->headers->all(),
                    'params' => request()->all(),
                ],
            ]
        );
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
        if ($exception instanceof ValidationException) {
            $errorResponseEntity = $this->convertErrorResponseEntity(
                $exception->getMessage(),
                $exception->getCode(),
                $exception->getErrors()
            );
            return response()->json($errorResponseEntity->buildBody(), $errorResponseEntity->getErrorCode());
        }

        if ($exception instanceof BusinessLogicException) {
            $errorResponseEntity = $this->convertErrorResponseEntity(
                $exception->getMessage(),
                $exception->getCode()
            );
            return response()->json($errorResponseEntity->buildBody(), $errorResponseEntity->getErrorCode());
        }

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            $errorResponseEntity = $this->convertErrorResponseEntity('Not Found', 404);
            return response()->json($errorResponseEntity->buildBody(), $errorResponseEntity->getErrorCode());
        }

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
            $errorResponseEntity = $this->convertErrorResponseEntity('Method Not Allowed', 405);
            return response()->json($errorResponseEntity->buildBody(), $errorResponseEntity->getErrorCode());
        }

        $errorResponseEntity = $this->convertErrorResponseEntity('Internal Server Error', 500);
        return response()->json($errorResponseEntity->buildBody(), $errorResponseEntity->getErrorCode());
    }

    /**
     * ErrorResponseEntity を作成する
     *
     * @param string $message
     * @param int $statusCode
     * @param array $errors
     * @return ErrorResponseEntity
     */
    private function convertErrorResponseEntity(string $message, int $statusCode, array $errors = []): ErrorResponseEntity
    {
        $errorResponseEntityBuilder = new ErrorResponseEntityBuilder();
        $errorResponseEntityBuilder->setErrorMessage($message);
        $errorResponseEntityBuilder->setErrorCode($statusCode);
        if (count($errors)) {
            $errorResponseEntityBuilder->setErrors($errors);
        }
        return  $errorResponseEntityBuilder->build();
    }
}
