<?php

namespace IPP\Student\arguments;

use IPP\Core\Exception\InternalErrorException;
use IPP\Student\arguments\ArgumentType;
use IPP\Student\exceptions\InvalidSourceStructure;

global $string_replace_map;
$string_replace_map = [];

for ($i = 0; $i < 1000; $i++) {
    $key = sprintf("\\%03d", $i);
    $string_replace_map[$key] = mb_chr($i);
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
        } else {
            throw new InvalidSourceStructure("Invalid floating point number");
        }
    }

    /**
     * @throws InternalErrorException
     */
    #[\Override] public function getValue(): string|int|bool|float|null
    {
        global $string_replace_map;
        return match ($this->type) {
            ArgumentType::STRING_LITERAL => strtr($this->value, $string_replace_map),
            ArgumentType::INT_LITERAL => (int)$this->value,
            ArgumentType::BOOL_LITERAL => (bool)$this->value,
            ArgumentType::FLOAT_LITERAL => $this->float(),
            ArgumentType::NIL_LITERAL => null,
            default => throw new InternalErrorException("Invalid literal type"),
        };
    }
}