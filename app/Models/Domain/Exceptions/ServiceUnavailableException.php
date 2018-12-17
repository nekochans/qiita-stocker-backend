<?php
/**
 * ServiceUnavailableException
 */

namespace App\Models\Domain\Exceptions;

use Throwable;

/**
 * Class ServiceUnavailableException
 * @package App\Models\Domain\Exceptions
 */
class ServiceUnavailableException extends BusinessLogicException
{
    const ERROR_MESSAGE = 'Service Unavailable';

    const ERROR_CODE = 503;

    /**
     * ServiceUnavailableException constructor.
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
