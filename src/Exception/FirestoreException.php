<?php

namespace Tiriel\FirestoreOdmBundle\Exception;

class FirestoreException extends \Exception
{
    protected array $context;

    public function __construct(string $message = "", array $context = [], int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }
}
