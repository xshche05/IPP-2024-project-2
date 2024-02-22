<?php

namespace IPP\Student\arguments;

class VarArgument extends AbstractArgument
{
    /**
     * Constructor for the VarArgument class
     * @param string $value name of variable as FRAME@name
     */
    public function __construct(string $value)
    {
        parent::__construct(ArgumentType::VARIABLE ,$value);
    }

    /**
     * Function to access (get) the value of the argument
     * @return string - value of the argument as string
     */
    #[\Override] public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Function to access (get) the name of the variable
     * @return string - name of the variable without the frame
     */
    public function getName(): string
    {
        # split the value by @ and return the second part
        return explode("@", $this->value)[1];
    }

    /**
     * Function to access (get) the frame of the variable
     * @return string - frame of the variable
     */
    public function getFrame(): string
    {
        # split the value by @ and return the first part
        return explode("@", $this->value)[0];
    }
}