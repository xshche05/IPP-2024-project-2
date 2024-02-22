<?php

namespace IPP\Student\variables;

use IPP\Core\Exception\InternalErrorException;

class Variable extends MemoryValue
{
    private string $name;
    public function __construct(string $name)
    {
        parent::__construct(null, MemoryDataType::NONE_TYPE);
        $this->name = $name;
    }

    /**
     * Function to access (get) name of Variable
     * @return string - name of Variable without frame
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Function to access (get) value of Variable
     * @param bool $soft if true, no exception is thrown if the variable is not initialized
     * @return int|string|float|bool|null - value of the variable
     * @throws InternalErrorException - if the variable is not initialized and $soft is false
     */
    public function getValue(bool $soft = false): int|string|float|bool|null
    {
        if ($this->type === MemoryDataType::NONE_TYPE && !$soft) {
            throw new InternalErrorException("Variable {$this->name} is not initialized.");
        }
//        if ($this->type !== MemoryDataType::fromValue($this->value)) {
//            throw new InternalErrorException("Invalid value type");
//        }
        return $this->value;
    }

    /**
     * Function to assign value and type of another variable or memory value to this variable
     * @param Variable | MemoryValue $variable variable or memory value to be assigned to
     * @throws InternalErrorException
     */
    public function assign(Variable|MemoryValue $variable): void
    {
        $this->value = $variable->getValue();
        $this->type = $variable->getType();
    }
}