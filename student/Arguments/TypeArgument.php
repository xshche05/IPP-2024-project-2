<?php

namespace IPP\Student\Arguments;

use IPP\Core\Exception\InternalErrorException;
use Override;

class TypeArgument extends AbstractArgument
{
    private string $type_value;
    /**
     * @throws InternalErrorException
     */
    public function __construct(string $value)
    {
        # if value is not string, int, bool, float throw exception
        parent::__construct(ArgumentType::TYPE ,$value);
        if (!in_array($this->value, ["int", "bool", "string", "float"])) {
            throw new InternalErrorException("Invalid TYPE argument value");
        }
        $this->type_value = $this->value;
    }

    #[Override] public function getValue(): string
    {
        return $this->type_value;
    }
}