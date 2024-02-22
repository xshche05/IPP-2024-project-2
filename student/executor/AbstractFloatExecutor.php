<?php

namespace IPP\Student\executor;

use IPP\Student\arguments\LiteralArgument;
use IPP\Student\arguments\VarArgument;
use IPP\Student\exceptions\RuntimeTypeException;
use IPP\Student\variables\MemoryDataType;
use IPP\Student\variables\MemoryValue;
use IPP\Student\variables\TypePair;

trait AbstractFloatExecutor
{
    use ExecutorBaseLogic;
    /** FLOAT  */
    public function INT2FLOAT(VarArgument $var, VarArgument|LiteralArgument $symb): void
    {
        $dest_var = $this->getFramedVariable($var);
        $src_data = $this->getSymbMemoryValue($symb);
        if ($src_data->getType() != MemoryDataType::INT) {
            throw new RuntimeTypeException("Invalid type of INT2FLOAT operand, expected INT as second operand");
        }
        $src_val = $src_data->getValue();
        $result = new MemoryValue(floatval($src_val), MemoryDataType::FLOAT);
        $dest_var->assign($result);
    }

    public function FLOAT2INT(VarArgument $var, VarArgument|LiteralArgument $symb): void
    {
        $dest_var = $this->getFramedVariable($var);
        $src_data = $this->getSymbMemoryValue($symb);
        if ($src_data->getType() != MemoryDataType::FLOAT) {
            throw new RuntimeTypeException("Invalid type of FLOAT2INT operand, expected FLOAT as second operand");
        }
        $src_val = $src_data->getValue();
        $result = new MemoryValue(intval($src_val), MemoryDataType::INT);
        $dest_var->assign($result);
    }

    public function DIV(VarArgument $var, VarArgument|LiteralArgument $symb1, VarArgument|LiteralArgument $symb2): void
    {
        $this->basicAluOperation($var, $symb1, $symb2, "DIV", [
            new TypePair(MemoryDataType::FLOAT, MemoryDataType::FLOAT),
        ]);
    }
}