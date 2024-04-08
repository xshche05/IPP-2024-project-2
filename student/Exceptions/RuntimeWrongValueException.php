<?php

namespace IPP\Student\Exceptions;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;

class RuntimeWrongValueException extends IPPException
{
    public function __construct(string $message = "Runtime error - bad operand value")
    {
        parent::__construct($message, ReturnCode::OPERAND_VALUE_ERROR);
    }
}