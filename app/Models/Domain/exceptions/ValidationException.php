<?php
/**
 * ValidationException
 */

namespace App\Models\Domain\exceptions;

use Throwable;

/**
 * Class ValidationException
 * @package App\Models\Domain\exceptions
 */
class ValidationException extends \Exception
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
     * @param array $errors
     * @param Throwable|null $previous
     */
    public function __construct(
        array $errors,
        Throwable $previous = null
    ) {
        parent::__construct(
            self::ERROR_MESSAGE,
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
