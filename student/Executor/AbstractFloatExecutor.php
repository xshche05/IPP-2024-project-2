<?php

namespace IPP\Student\Executor;

use IPP\Core\Exception\InternalErrorException;
use IPP\Student\Arguments\LiteralArgument;
use IPP\Student\Arguments\VarArgument;
use IPP\Student\Exceptions\RuntimeMemoryFrameException;
use IPP\Student\Exceptions\RuntimeNoValueException;
use IPP\Student\Exceptions\RuntimeTypeException;
use IPP\Student\Exceptions\RuntimeUndefVarException;
use IPP\Student\Exceptions\RuntimeWrongValueException;
use IPP\Student\Variables\MemoryDataType;
use IPP\Student\Variables\MemoryValue;

trait AbstractFloatExecutor
{
    use ExecutorBaseLogic;

    /** FLOAT */

    /**
     * @param VarArgument $var
     * @param VarArgument|LiteralArgument $symb
     * @throws InternalErrorException
     * @throws RuntimeTypeException
     * @throws RuntimeMemoryFrameException
     * @throws RuntimeUndefVarException
     * @throws RuntimeNoValueException
     */
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

    /**
     * @param VarArgument $var
     * @param VarArgument|LiteralArgument $symb
     * @throws InternalErrorException
     * @throws RuntimeTypeException
     * @throws RuntimeMemoryFrameException
     * @throws RuntimeUndefVarException
     * @throws RuntimeNoValueException
     */
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

    /**
     * @param VarArgument $var
     * @param VarArgument|LiteralArgument $symb1
     * @param VarArgument|LiteralArgument $symb2
     * @throws InternalErrorException
     * @throws RuntimeTypeException
     * @throws RuntimeMemoryFrameException
     * @throws RuntimeUndefVarException
     * @throws RuntimeWrongValueException|RuntimeNoValueException
     */
    public function DIV(VarArgument $var, VarArgument|LiteralArgument $symb1, VarArgument|LiteralArgument $symb2): void
    {
        $dest_var = $this->getFramedVariable($var);
        $src1_data = $this->getSymbMemoryValue($symb1);
        $src2_data = $this->getSymbMemoryValue($symb2);
        $dest_var->assign($src1_data->div($src2_data));
    }
}