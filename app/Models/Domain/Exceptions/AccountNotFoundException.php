<?php
/**
 * AccountNotFoundException
 */

namespace App\Models\Domain\Exceptions;

use Throwable;

/**
 * Class AccountNotFoundException
 * @package App\Models\Domain\Exceptions
 */
class AccountNotFoundException extends BusinessLogicException
{
    const ERROR_MESSAGE = 'Not Found';

    const ERROR_CODE = 404;

    /**
     * AccountNotFoundException constructor.
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
