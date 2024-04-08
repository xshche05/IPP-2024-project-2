<?php

namespace IPP\Student\Arguments;

use IPP\Student\Exceptions\InvalidSourceStructure;
use Override;

class VarArgument extends AbstractArgument
{
    private static string $var_regex = '/^[LTG]F@[a-zA-Z0-9_\-&%*$!?]+$/';
    private string $name;
    private string $frame;

    /**
     * @throws InvalidSourceStructure
     */
    public function __construct(string $value)
    {
        parent::__construct(ArgumentType::VARIABLE ,$value);
        if (!preg_match(self::$var_regex, $this->value))
            throw new InvalidSourceStructure("Invalid variable format");
        $parts = explode('@', $this->value);
        $this->frame = $parts[0];
        $this->name = $parts[1];
    }

    /**
     * Function to access (get) the value of the argument
     * @return string - value of the argument as string
     */
    #[Override] public function getValue(): string
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
        return $this->name;
    }

    /**
     * Function to access (get) the frame of the variable
     * @return string - frame of the variable
     */
    public function getFrame(): string
    {
        # split the value by @ and return the first part
        return $this->frame;
    }
}