<?php

namespace IPP\Student\Variables;

use IPP\Core\Exception\InternalErrorException;
use IPP\Student\Arguments\ArgumentType;

enum MemoryDataType
{
    case INT;
    case STRING;
    case BOOL;
    case FLOAT;
    case NIL;
    case NONE_TYPE;

    /**
     * @param ArgumentType $type - type to convert
     * @return MemoryDataType - converted type
     *
     * @throws InternalErrorException - if the type is invalid or not supported
     */
    public static function fromArgumentType(ArgumentType $type): self
    {
        return match ($type) {
            ArgumentType::INT_LITERAL => self::INT,
            ArgumentType::STRING_LITERAL => self::STRING,
            ArgumentType::BOOL_LITERAL => self::BOOL,
            ArgumentType::FLOAT_LITERAL => self::FLOAT,
            ArgumentType::NIL_LITERAL => self::NIL,
            default => throw new InternalErrorException("Invalid argument type"),
        };
    }

    /**
     * @param int|string|float|bool|null $value - value to convert
     * @return MemoryDataType - converted type
     */
    public static function fromValue(int|string|float|bool|null $value): self
    {
        return match (gettype($value)) {
            "integer" => self::INT,
            "string" => self::STRING,
            "boolean" => self::BOOL,
            "double" => self::FLOAT,
            "NULL" => self::NIL
        };
    }

    public function toString(): string
    {
        return match ($this) {
            self::INT => "int",
            self::STRING => "string",
            self::BOOL => "bool",
            self::FLOAT => "float",
            self::NIL => "nil",
            self::NONE_TYPE => "none"
        };
    }
}
