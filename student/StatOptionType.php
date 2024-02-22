<?php

namespace IPP\Student;

enum StatOptionType
{
    case INSTS;
    case HOT;
    case VARS;
    case STACK;
    case EOL;
    case PRINT;

    public static function fromString(string $str): StatOptionType
    {
        if (str_starts_with($str, "--print")) {
            return self::PRINT;
        }
        return match ($str) {
            "--insts" => self::INSTS,
            "--hot" => self::HOT,
            "--vars" => self::VARS,
            "--stack" => self::STACK,
            "--eol" => self::EOL,
            default => throw new \InvalidArgumentException("Invalid stat option: $str"),
        };
    }
}
