<?php

namespace IPP\Student\CustomIo;

use IPP\Core\StreamWriter;
use Override;

class CustomWriter extends StreamWriter
{
    public function __construct($stream)
    {
        parent::__construct($stream);
    }

    #[Override] public function writeFloat(float $value): void
    {
        $this->writeString(sprintf("%.10e", $value));
    }
}