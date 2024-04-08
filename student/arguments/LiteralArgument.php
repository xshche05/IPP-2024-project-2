<?php

namespace IPP\Student\arguments;

use IPP\Core\Exception\InternalErrorException;
use IPP\Student\arguments\ArgumentType;
use IPP\Student\exceptions\InvalidSourceStructure;

global $string_replace_map;
$string_replace_map = [];

for ($i = 0; $i < 1000; $i++) {
    $key = sprintf("\\%03d", $i);
    $string_replace_map[$key] = mb_chr($i, "UTF-8");
}

class LiteralArgument extends AbstractArgument
{
    /**
     * @throws InternalErrorException
     */
    public function __construct(ArgumentType $type, string $value)
    {
        switch ($type) {
            case ArgumentType::VARIABLE:
            case ArgumentType::LABEL:
            case ArgumentType::TYPE:
                throw new InternalErrorException("Invalid literal type");
            default:
                parent::__construct($type, $value);
        }
    }

    // function float to parse string with floating-point number in hexadecimal notation
    private function float(): float
    {
        $str = $this->value;
        $pattern = '/
        (?<sign>[+-])?
        (?:
          (?<inf>Infinity) | (?<nan>NaN) |
          (?<type>0[xX])
          (?= [0-9a-fA-F] | \\.[0-9a-fA-F] )
          (?<int>[0-9a-fA-F]*)
          (?:\\.(?<frac>[0-9a-fA-F]*))?
          (?:[pP]
            (?<esign>[+-])?
            (?<exp>[0-9]+)
          )?
        )
    /x';
        if (preg_match($pattern, $str, $matches)) {
            $grp = $matches;
            $sign = ($grp['sign'] === '-') ? -1 : 1;

            if ($grp['nan'] === 'NaN') return NAN;
            if ($grp['inf'] === 'Infinity') return INF * $sign;

            $int = isset($grp['int']) ? hexdec($grp['int']) : 0;
            $frac = isset($grp['frac']) ? hexdec($grp['frac']) * (16 ** (-strlen($grp['frac']))) : 0;
            $exp = isset($grp['exp']) ? intval($grp['exp']) * ($grp['esign'] === '-' ? -1 : 1) : 0;

            return $sign * ($int + $frac) * (2 ** $exp);
        } else if (is_numeric($str)) {
            return floatval($str);
        } else {
            throw new InvalidSourceStructure("Invalid floating-point number {$str}");
        }
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

    /**
     * @throws InternalErrorException
     * @throws InvalidSourceStructure
     */
    #[\Override] public function getValue(): string|int|bool|float|null
    {
        global $string_replace_map;
        return match ($this->type) {
            ArgumentType::STRING_LITERAL => strtr($this->value, $string_replace_map),
            ArgumentType::INT_LITERAL => $this->int(),
            ArgumentType::BOOL_LITERAL => $this->bool(),
            ArgumentType::FLOAT_LITERAL => $this->float(),
            ArgumentType::NIL_LITERAL => null,
            default => throw new InternalErrorException("Invalid literal type"),
        };
    }
}