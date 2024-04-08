<?php

namespace IPP\Student\Exceptions;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;

class RuntimeUndefVarException extends IPPException
{
    public function __construct(string $message = "Runtime error - non-existent variable")
    {
        parent::__construct($message, ReturnCode::VARIABLE_ACCESS_ERROR);
    }
}