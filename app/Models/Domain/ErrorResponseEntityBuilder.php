<?php
/**
 * ErrorResponseEntityBuilder
 */

namespace App\Models\Domain;

class ErrorResponseEntityBuilder
{
    /**
     * エラーメッセージ
     *
     * @var string
     */
    private $errorMessage;

    /**
     * エラーコード
     *
     * @var int
     */
    private $errorCode;

    /**
     * エラーの詳細
     *
     * @var array
     */
    private $errors;

    /**
     * ErrorResponseEntityBuilder constructor.
     */
    public function __construct()
    {
        $this->errors = [];
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * @param string $errorMessage
     */
    public function setErrorMessage(string $errorMessage): void
    {
        $this->errorMessage = $errorMessage;
    }

    /**
     * @return int
     */
    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * @param int $errorCode
     */
    public function setErrorCode(int $errorCode): void
    {
        $this->errorCode = $errorCode;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     */
    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    /**
     * @return ErrorResponseEntity
     */
    public function build(): ErrorResponseEntity
    {
        return new ErrorResponseEntity($this);
    }
}
