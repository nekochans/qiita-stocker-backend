<?php
/**
 * CategoryNotFoundException
 */

namespace App\Models\Domain\Exceptions;

use Throwable;

/**
 * Class CategoryNotFoundException
 * @package App\Models\Domain\Exceptions
 */
class CategoryNotFoundException extends BusinessLogicException
{
    const ERROR_MESSAGE = 'Not Found';

    const ERROR_CODE = 404;

    /**
     * CategoryNotFoundException constructor.
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
