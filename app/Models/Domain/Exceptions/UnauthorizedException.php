<?php
/**
 * UnauthorizedException
 */

namespace App\Models\Domain\Exceptions;

use Throwable;

/**
 * Class UnauthorizedException
 * @package App\Models\Domain\Exceptions
 */
class UnauthorizedException extends BusinessLogicException
{
    const ERROR_MESSAGE = 'Unauthorized';

    const ERROR_CODE = 401;

    /**
     * UnauthorizedException constructor.
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
