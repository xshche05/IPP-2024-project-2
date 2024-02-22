<?php

namespace IPP\Student\variables;

use IPP\Core\Exception\InternalErrorException;

class MemoryValue
{
    protected int|string|float|bool|null $value;
    protected MemoryDataType $type;

    public function __construct(int|string|float|bool|null $value, MemoryDataType $type)
    {
        $this->value = $value;
        $this->type = $type;
    }

    /**
     * Function to access (get) value of MemoryValue
     * @return int|string|float|bool|null
     * @throws InternalErrorException
     */
    public function getValue(): int|string|float|bool|null
    {
//        if ($this->type !== MemoryDataType::fromValue($this->value)) {
//            echo "type: " . $this->type->toString() . "\n";
//            echo "Invalid value type " . $this->value . "\n";
//            throw new InternalErrorException("Invalid value type");
//        }
        return $this->value;
    }

    /**
     * Function to access (get) type of MemoryValue
     * @return MemoryDataType - type of MemoryValue or null if not set
     */
    public function getType(): MemoryDataType
    {
        return $this->type;
    }
}