<?php

namespace IPP\Student\Arguments;

use _PHPStan_cc8d35ffb\Nette\NotImplementedException;
use IPP\Core\Exception\InternalErrorException;
use IPP\Student\Arguments\ArgumentType;
use IPP\Student\Exceptions\InvalidSourceStructure;

class LiteralArgument extends AbstractArgument
{
    private float $float_value;
    private int $int_value;
    private bool $bool_value;
    private string $string_value;
    public function __construct(ArgumentType $type, string $value)
    {
        switch ($type) {
            case ArgumentType::FLOAT_LITERAL:
                parent::__construct($type, $value);
                $this->float_value = $this->float();
                break;
            case ArgumentType::INT_LITERAL:
                parent::__construct($type, $value);
                $this->int_value = $this->int();
                break;
            case ArgumentType::BOOL_LITERAL:
                parent::__construct($type, $value);
                $this->bool_value = $this->bool();
                break;
            case ArgumentType::STRING_LITERAL:
                parent::__construct($type, $value);
                $this->string_value = $this->string();
                break;
            case ArgumentType::NIL_LITERAL:
                parent::__construct($type, $value);
                break;
            default:
                throw new InternalErrorException("Invalid literal type");
        }
    }

    private function float(): float
    {
        throw new NotImplementedException("Not implemented float");
    }

    private function int(): int
    {
        # if starts with 0x or 0X or -0x or -0X then parse as hexadecimal number
        $sign = str_starts_with($this->value, "-") ? -1 : 1;
        $int_regex = '^[+-]?((\d+)|(0[xX][0-9a-fA-F]+)|(0[oO][0-7]+))$';
        if (!preg_match("/$int_regex/", $this->value)) {
            throw new InvalidSourceStructure("Invalid integer number");
        }
        $value = ltrim($this->value, "-");
        if (str_starts_with($value, "0x") || str_starts_with($this->value, "0X")) {
            return intval(base_convert($value, 16, 10))* $sign;
        }
        # if starts with 0o or 0O then parse as binary number
        if (str_starts_with($value, "0o") || str_starts_with($this->value, "0O")){
            return intval(base_convert($value, 8, 10)) * $sign;
        }
        # if starts with 0 or -0 then parse as decimal number
        if (str_starts_with($this->value, "0")){
            return intval(base_convert($value, 10, 10)) * $sign;
        }
        return intval($value) * $sign;
    }

    private function bool(): bool
    {
        if ($this->value === "true") return true;
        if ($this->value === "false") return false;
        throw new InvalidSourceStructure("Invalid boolean value");
    }

    private function string(): string
    {
        $raw = $this->value;
        $regex = '/^([^\x00-\x20\x23\x5C]|(\\\[0-9]{3}))*$/'; // todo
        if (!preg_match($regex, $raw)) {
            throw new InvalidSourceStructure("Invalid string format");
        }
        # replace all escape sequences like \xxx with corresponding characters, xxx is decimal number
        $pattern = '/\\\\([0-9]{3})/';
        $raw = preg_replace_callback($pattern, function ($matches) {
            return mb_chr(intval($matches[1]), "UTF-8");
        }, $raw);
        if ($raw === null) {
            throw new InternalErrorException("preg_replace_callback failed");
        }
        return $raw;
    }

    #[\Override] public function getValue(): string|int|bool|float|null
    {
        switch ($this->type) {
            case ArgumentType::FLOAT_LITERAL:
                return $this->float_value;
            case ArgumentType::INT_LITERAL:
                return $this->int_value;
            case ArgumentType::BOOL_LITERAL:
                return $this->bool_value;
            case ArgumentType::STRING_LITERAL:
                return $this->string_value;
            case ArgumentType::NIL_LITERAL:
                return null;
            default:
                throw new InternalErrorException("Invalid literal type");
        }
    }
}