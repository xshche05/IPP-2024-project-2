<?php

namespace IPP\Student\Exceptions;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;

class InterpretSemanticException extends IPPException
{
    public function __construct(string $message = "Semantic error")
    {
        parent::__construct($message,ReturnCode::SEMANTIC_ERROR);
    }
}