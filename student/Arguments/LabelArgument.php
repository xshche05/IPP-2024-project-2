<?php

namespace IPP\Student\Arguments;

use IPP\Student\Exceptions\InvalidSourceStructure;

class LabelArgument extends AbstractArgument
{
    static private string $label_regex = '/^[a-zA-Z0-9_\-&%*$!?]+$/';
    private string $label_value;
    public function __construct(string $value)
    {
        parent::__construct(ArgumentType::LABEL ,$value);
        if (!preg_match(self::$label_regex, $this->value))
            throw new InvalidSourceStructure("Invalid label format");
        $this->label_value = $this->value;
    }


    #[\Override] public function getValue(): string
    {
        return $this->label_value;
    }
}