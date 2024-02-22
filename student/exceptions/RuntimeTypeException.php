<?php

namespace IPP\Student\exceptions;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;

class RuntimeTypeException extends IPPException
{
    public function __construct(string $message = "Runtime error - bad operand types")
    {
        parent::__construct($message, ReturnCode::OPERAND_TYPE_ERROR);
    }
}