<?php

namespace IPP\Student\Variables;

use IPP\Student\Exceptions\RuntimeNoValueException;
use IPP\Student\Exceptions\RuntimeTypeException;
use IPP\Student\Exceptions\RuntimeWrongValueException;

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
     */
    public function getValue(): int|string|float|bool|null
    {
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

    public function toString(): string
    {
        return match ($this->type) {
            MemoryDataType::INT, MemoryDataType::FLOAT, MemoryDataType::STRING => (string)$this->value,
            MemoryDataType::BOOL => $this->value ? "true" : "false",
            MemoryDataType::NIL => "nil",
            MemoryDataType::NONE_TYPE => "none",
        };
    }

    /**
     * @throws RuntimeTypeException
     * @throws RuntimeNoValueException
     */
    public function add(MemoryValue|Variable $second): MemoryValue
    {
        if ($this->type == MemoryDataType::INT && $second->getType() == MemoryDataType::INT) {
            if (is_numeric($this->value) && is_numeric($second->getValue())) {
                return new MemoryValue($this->value + $second->getValue(), MemoryDataType::INT);
            }
        }
        if ($this->type == MemoryDataType::FLOAT && $second->getType() == MemoryDataType::FLOAT) {
            if (is_numeric($this->value) && is_numeric($second->getValue())) {
                return new MemoryValue($this->value + $second->getValue(), MemoryDataType::FLOAT);
            }
        }
        throw new RuntimeTypeException("Operation not supported"); // todo
    }

    /**
     * @throws RuntimeTypeException
     * @throws RuntimeNoValueException
     */
    public function sub(MemoryValue|Variable $second): MemoryValue
    {
        if ($this->type == MemoryDataType::INT && $second->getType() == MemoryDataType::INT) {
            if (is_numeric($this->value) && is_numeric($second->getValue())) {
                return new MemoryValue($this->value - $second->getValue(), MemoryDataType::INT);
            }
        }
        if ($this->type == MemoryDataType::FLOAT && $second->getType() == MemoryDataType::FLOAT) {
            if (is_numeric($this->value) && is_numeric($second->getValue())) {
                return new MemoryValue($this->value - $second->getValue(), MemoryDataType::FLOAT);
            }
        }
        throw new RuntimeTypeException("Operation not supported"); // todo
    }

    /**
     * @throws RuntimeTypeException|RuntimeNoValueException
     */
    public function mul(MemoryValue|Variable $second): MemoryValue
    {
        if ($this->type == MemoryDataType::INT && $second->getType() == MemoryDataType::INT) {
            if (is_numeric($this->value) && is_numeric($second->getValue())) {
                return new MemoryValue($this->value * $second->getValue(), MemoryDataType::INT);
            }
        }
        if ($this->type == MemoryDataType::FLOAT && $second->getType() == MemoryDataType::FLOAT) {
            if (is_numeric($this->value) && is_numeric($second->getValue())) {
                return new MemoryValue($this->value * $second->getValue(), MemoryDataType::FLOAT);
            }
        }
        throw new RuntimeTypeException("Operation not supported"); // todo
    }

    /**
     * @throws RuntimeTypeException
     * @throws RuntimeWrongValueException
     * @throws RuntimeNoValueException
     */
    public function idiv(MemoryValue|Variable $second): MemoryValue
    {
        if ($this->type == MemoryDataType::INT && $second->getType() == MemoryDataType::INT) {
            if (is_int($this->value) && is_int($second->getValue())) {
                if ($second->getValue() == 0) {
                    throw new RuntimeWrongValueException("Division by zero");
                }
                return new MemoryValue(intval($this->value / $second->getValue()), MemoryDataType::INT);
            }
        }
        throw new RuntimeTypeException("Operation not supported"); // todo
    }

    /**
     * @throws RuntimeTypeException
     * @throws RuntimeWrongValueException
     * @throws RuntimeNoValueException
     */
    public function div(MemoryValue|Variable $second): MemoryValue
    {
        if ($this->type == MemoryDataType::FLOAT && $second->getType() == MemoryDataType::FLOAT) {
            if (is_float($this->value) && is_float($second->getValue())) {
                if ($second->getValue() == 0.0) {
                    throw new RuntimeWrongValueException("Division by zero");
                }
                return new MemoryValue($this->value / $second->getValue(), MemoryDataType::FLOAT);
            }
        }
        throw new RuntimeTypeException("Operation not supported"); // todo
    }

    /**
     * @throws RuntimeTypeException
     * @throws RuntimeNoValueException
     */
    public function eq(MemoryValue|Variable $second): MemoryValue
    {
        if ($this->type === $second->getType()) {
            return new MemoryValue($this->value === $second->getValue(), MemoryDataType::BOOL);
        }
        else if ($this->type === MemoryDataType::NIL || $second->getType() === MemoryDataType::NIL) {
            return new MemoryValue($this->value === $second->getValue(), MemoryDataType::BOOL);
        }
        throw new RuntimeTypeException("Operation not supported"); // todo
    }

    /**
     * @throws RuntimeTypeException
     * @throws RuntimeNoValueException
     */
    public function lt(MemoryValue|Variable $second): MemoryValue
    {
        if ($this->type === $second->getType()
            && $this->type !== MemoryDataType::NIL
            && $second->type !== MemoryDataType::NIL) {
            return new MemoryValue($this->value < $second->getValue(), MemoryDataType::BOOL);
        }
        throw new RuntimeTypeException("Operation not supported"); // todo
    }

    /**
     * @throws RuntimeTypeException
     * @throws RuntimeNoValueException
     */
    public function gt(MemoryValue|Variable $second): MemoryValue
    {
        if ($this->type === $second->getType()
            && $this->type !== MemoryDataType::NIL
            && $second->type !== MemoryDataType::NIL) {
            return new MemoryValue($this->value > $second->getValue(), MemoryDataType::BOOL);
        }
        throw new RuntimeTypeException("Operation not supported"); // todo
    }

    /**
     * @throws RuntimeTypeException|RuntimeNoValueException
     */
    public function and(MemoryValue|Variable $second): MemoryValue
    {
        if ($this->type === MemoryDataType::BOOL && $second->getType() === MemoryDataType::BOOL) {
            return new MemoryValue($this->value && $second->getValue(), MemoryDataType::BOOL);
        }
        throw new RuntimeTypeException("Operation not supported"); // todo
    }

    /**
     * @throws RuntimeTypeException
     * @throws RuntimeNoValueException
     */
    public function or(MemoryValue|Variable $second): MemoryValue
    {
        if ($this->type === MemoryDataType::BOOL && $second->getType() === MemoryDataType::BOOL) {
            return new MemoryValue($this->value || $second->getValue(), MemoryDataType::BOOL);
        }
        throw new RuntimeTypeException("Operation not supported"); // todo
    }

    /**
     * @throws RuntimeTypeException
     */
    public function not(): MemoryValue
    {
        if ($this->type === MemoryDataType::BOOL) {
            return new MemoryValue(!$this->value, MemoryDataType::BOOL);
        }
        throw new RuntimeTypeException("Operation not supported"); // todo
    }
}