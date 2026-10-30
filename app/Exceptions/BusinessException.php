<?php

namespace App\Exceptions;

use Exception;

/**
 * Base exception for business logic errors.
 * Requirements: 10.3
 */
class BusinessException extends Exception
{
    public function __construct(
        string $message,
        private readonly string $errorCode = 'BUSINESS_ERROR',
        private readonly int $httpStatus = 422
    ) {
        parent::__construct($message);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }
}
