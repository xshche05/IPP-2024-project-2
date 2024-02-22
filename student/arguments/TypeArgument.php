<?php

namespace IPP\Student\arguments;

use IPP\Core\Exception\InternalErrorException;

class TypeArgument extends AbstractArgument
{

    /**
     * @throws InternalErrorException
     */
    public function __construct(string $value)
    {
        # if value is not string, int, bool, float throw exception
        $value = trim($value);
        if (!in_array($value, ["int", "bool", "string", "float"])) {
            throw new InternalErrorException("Invalid TYPE argument value");
        }
        parent::__construct(ArgumentType::TYPE ,$value);
    }

    #[\Override] public function getValue(): string
    {
        return $this->value;
    }
}