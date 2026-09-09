<?php

namespace App\Exceptions;

class PushSendException extends \RuntimeException
{
    public function __construct(public readonly bool $retryable, string $code)
    {
        // Never include device tokens, credentials, or raw provider responses.
        parent::__construct($code);
    }
}
