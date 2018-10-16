<?php
/**
 * JsonResponseError
 */

namespace App\Models\Domain;

/**
 * Class JsonResponseError
 * @package App\Models\Domain
 */
class ErrorResponseEntity
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
     * ErrorResponseEntity constructor.
     * @param ErrorResponseEntityBuilder $builder
     */
    public function __construct(ErrorResponseEntityBuilder $builder)
    {
        $this->errorMessage = $builder->getErrorMessage();
        $this->errorCode = $builder->getErrorCode();
        $this->errors = $builder->getErrors();
    }

    /**
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * @return int
     */
    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * レスポンスボディを作成する
     *
     * @return array
     */
    public function buildBody(): array
    {
        $data = [
            'code'    => $this->getErrorCode(),
            'message' => $this->getErrorMessage(),
        ];

        if ($this->getErrors() !== []) {
            $data['errors'] = $this->getErrors();
        }

        return $data;
    }
}
