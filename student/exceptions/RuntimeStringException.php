<?php

namespace IPP\Student\exceptions;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;

class RuntimeStringException extends IPPException
{
    public function __construct(string $message = "Runtime error - bad string operation")
    {
        parent::__construct($message, ReturnCode::STRING_OPERATION_ERROR);
    }
}