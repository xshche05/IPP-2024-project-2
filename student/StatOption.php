<?php

namespace IPP\Student;

use IPP\Student\program\Program;

class StatOption
{
    private StatOptionType $type;
    private string $value;
    private static Program $program;

    public function __construct(StatOptionType $type, string $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    public static function setProgram(Program $program): void
    {
        self::$program = $program;
    }

    public function getType(): StatOptionType
    {
        return $this->type;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function out(): string
    {
        return match ($this->type) {
            StatOptionType::INSTS => strval(self::$program->getInstStat()),
            StatOptionType::HOT => strval(self::$program->getHotStat()),
            StatOptionType::VARS => strval(self::$program->getVarsStat()),
            StatOptionType::STACK => strval(self::$program->getStackStat()),
            StatOptionType::EOL => "\n",
            StatOptionType::PRINT => $this->value,
        };
    }
}