<?php

namespace IPP\Student\arguments;
use IPP\Student\arguments\ArgumentType;

abstract class AbstractArgument
{
    protected ArgumentType $type;
    protected string $value;

    /**
     * Function to access (get) the value of the argument
     * @return string|int|bool|float|null - value of the argument
     */
    abstract public function getValue(): string|int|bool|float|null;

    /**
     * Constructor for the AbstractArgument class
     * @param ArgumentType $type type of the argument
     * @param string $value value of the argument as string
     */
    public function __construct(ArgumentType $type, string $value)
    {
        $this->type = $type;
        $this->value = trim($value);
    }
    /**
     * Function to access (get) the type of the argument
     * @return ArgumentType - type of the argument
     */
    public function getType(): ArgumentType
    {
        return $this->type;
    }
}