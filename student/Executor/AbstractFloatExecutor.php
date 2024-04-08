<?php

namespace IPP\Student\Executor;

use IPP\Student\Arguments\LiteralArgument;
use IPP\Student\Arguments\VarArgument;
use IPP\Student\Exceptions\RuntimeTypeException;
use IPP\Student\Variables\MemoryDataType;
use IPP\Student\Variables\MemoryValue;
use IPP\Student\Variables\TypePair;

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
        $dest_var = $this->getFramedVariable($var);
        $src1_data = $this->getSymbMemoryValue($symb1);
        $src2_data = $this->getSymbMemoryValue($symb2);
        $dest_var->assign($src1_data->div($src2_data));
    }
}