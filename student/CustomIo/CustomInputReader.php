<?php

namespace IPP\Student\CustomIo;

use IPP\Core\Exception\InputFileException;
use IPP\Core\FileInputReader;
use Override;

class CustomInputReader extends FileInputReader
{
    /**
     * @throws InputFileException
     */
    public function __construct(string $file)
    {
        parent::__construct($file);
    }

    #[Override] public function readFloat(): ?float
    {
        $result = $this->readString();
        if (is_null($result)) return null;
        return filter_var(
            $result,
            FILTER_VALIDATE_FLOAT,
            FILTER_NULL_ON_FAILURE | FILTER_FLAG_ALLOW_SCIENTIFIC | FILTER_FLAG_ALLOW_FRACTION
        );
    }
}