<?php

namespace IPP\Student\exceptions;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;

class RuntimeNoValueException extends IPPException
{
    public function __construct(string $message = "Runtime error - missing value")
    {
        parent::__construct($message, ReturnCode::VALUE_ERROR);
    }
}