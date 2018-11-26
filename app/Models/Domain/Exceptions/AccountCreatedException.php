<?php
/**
 * AccountCreatedException
 */

namespace App\Models\Domain\Exceptions;

use Throwable;

/**
 * Class AccountCreatedException
 * @package App\Models\Domain\Exceptions
 */
class AccountCreatedException extends BusinessLogicException
{
    const ERROR_MESSAGE = 'Conflict';

    const ERROR_CODE = 409;

    /**
     * AccountCreatedException constructor.
     * @param string $message
     * @param Throwable|null $previous
     */
    public function __construct(
        string $message = self::ERROR_MESSAGE,
        Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            self::ERROR_CODE,
            $previous
        );
    }
}
