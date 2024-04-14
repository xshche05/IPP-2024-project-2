<?php

namespace IPP\Student\CustomIo;

use IPP\Core\StreamWriter;
use Override;

class CustomWriter extends StreamWriter
{
    public function __construct($stream, private readonly string $format = "%.10f")
    {
        parent::__construct($stream);
    }

    #[Override] public function writeFloat(float $value): void
    {
        $this->writeString(sprintf($this->format, $value));
    }
}