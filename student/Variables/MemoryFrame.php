<?php

namespace IPP\Student\Variables;

use IPP\Student\Exceptions\InterpretSemanticException;
use IPP\Student\Exceptions\RuntimeUndefVarException;

class MemoryFrame
{
    /** @var array<string, Variable> */
    private array $var_map;

    public function __construct()
    {
        $this->var_map = [];
    }

    /**
     * Function to create a new variable in the frame
     * @param Variable $variable variable to be created
     * @throws InterpretSemanticException - if the variable already exists in the frame
     */
    public function createVariable(Variable $variable): void
    {
        if (isset($this->var_map[$variable->getName()])) {
            throw new InterpretSemanticException("Variable {$variable->getName()} already exists in this frame");
        }
        $this->var_map[$variable->getName()] = $variable;
    }

    /**
     * Function to get a variable from the frame
     * @param string $name name of the variable
     * @return Variable - requested variable
     * @throws RuntimeUndefVarException - if the variable is not defined in the frame
     */
    public function getVariable(string $name): Variable
    {
        if (!isset($this->var_map[$name])) {
            throw new RuntimeUndefVarException("Variable $name is not defined in this frame");
        }
        return $this->var_map[$name];
    }

    /**
     * Function to get number of variables in the frame
     */
    public function getVarCount(): int
    {
        return count($this->var_map);
    }

    public function toString(): string
    {
        $str = "{\n";
        foreach ($this->var_map as $var) {
            $str .= "  " . $var->getName() . " = " . $var->toString() . "\n";
        }
        $str .= "}\n";
        return $str;
    }
}