<?php

namespace IPP\Student\arguments;

class LabelArgument extends AbstractArgument
{
    public function __construct(string $value)
    {
        parent::__construct(ArgumentType::LABEL ,$value);
    }


    #[\Override] public function getValue(): string
    {
        return $this->value;
    }
}