<?php

namespace App\Exceptions;

/**
 * Thrown when an order cancellation is not allowed.
 * Requirements: 10.3
 */
class CancellationDeniedException extends BusinessException
{
    public function __construct(string $currentStatus)
    {
        parent::__construct(
            message: 'Order tidak dapat dibatalkan karena status saat ini adalah "' . $currentStatus . '". Pembatalan hanya diizinkan saat status masih "diterima".',
            errorCode: 'CANCELLATION_DENIED',
            httpStatus: 422
        );
    }
}
