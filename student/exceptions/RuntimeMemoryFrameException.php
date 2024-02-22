<?php

namespace IPP\Student\exceptions;

use IPP\Core\Exception\IPPException;
use IPP\Core\ReturnCode;

class RuntimeMemoryFrameException extends IPPException
{
    public function __construct(string $message = "Runtime error - non-existent frame")
    {
        parent::__construct($message, ReturnCode::FRAME_ACCESS_ERROR);
    }
}