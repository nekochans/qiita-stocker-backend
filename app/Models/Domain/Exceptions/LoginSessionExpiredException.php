<?php
/**
 * LoginSessionExpiredException
 */

namespace App\Models\Domain\Exceptions;

use Throwable;

/**
 * Class LoginSessionExpiredException
 * @package App\Models\Domain\Exceptions
 */
class LoginSessionExpiredException extends BusinessLogicException
{
    const ERROR_MESSAGE = 'Unauthorized';

    const ERROR_CODE = 401;

    /**
     * LoginSessionExpiredException constructor.
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
