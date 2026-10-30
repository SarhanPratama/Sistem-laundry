<?php

namespace App\Exceptions;

/**
 * Thrown when an order status transition is not sequential.
 * Requirements: 10.3
 */
class InvalidStatusTransitionException extends BusinessException
{
    public function __construct(string $currentStatus, string $attemptedStatus)
    {
        parent::__construct(
            message: 'Transisi status tidak valid dari "' . $currentStatus . '" ke "' . $attemptedStatus . '".',
            errorCode: 'INVALID_TRANSITION',
            httpStatus: 422
        );
    }
}
