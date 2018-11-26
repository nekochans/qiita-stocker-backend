<?php
/**
 * ValidationException
 */

namespace App\Models\Domain\Exceptions;

use Throwable;

/**
 * Class ValidationException
 * @package App\Models\Domain\Exceptions
 */
class ValidationException extends BusinessLogicException
{
    const ERROR_MESSAGE = 'Unprocessable Entity';

    const ERROR_CODE = 422;

    /**
     * バリデーションエラーの情報
     *
     * @var array
     */
    private $errors = [];

    /**
     * ValidationException constructor.
     *
     * @param string $message
     * @param array $errors
     * @param Throwable|null $previous
     */
    public function __construct(
        string $message = self::ERROR_MESSAGE,
        array $errors,
        Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            self::ERROR_CODE,
            $previous
        );

        $this->errors = $errors;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
