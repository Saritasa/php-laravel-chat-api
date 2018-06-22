<?php

namespace Saritasa\LaravelChatApi\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Throws in case error in chat service.
 */
class ChatException extends Exception
{
    /**
     * Throws in case error in chat service.
     *
     * @param string $message Error message
     * @param Throwable $previous Previous error
     */
    public function __construct(string $message, Throwable $previous = null)
    {
        parent::__construct($message, Response::HTTP_BAD_REQUEST, $previous);
    }
}
