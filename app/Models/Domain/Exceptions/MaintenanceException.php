<?php
/**
 * MaintenanceException
 */

namespace App\Models\Domain\Exceptions;

use Throwable;

/**
 * Class MaintenanceException
 * @package App\Models\Domain\Exceptions
 */
class MaintenanceException extends BusinessLogicException
{
    const ERROR_MESSAGE = 'Service Unavailable';

    const ERROR_CODE = 503;

    /**
     * MaintenanceException constructor.
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
