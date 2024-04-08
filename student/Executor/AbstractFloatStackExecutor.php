<?php

namespace IPP\Student\Executor;

use IPP\Student\Exceptions\RuntimeTypeException;
use IPP\Student\Variables\MemoryDataType;
use IPP\Student\Variables\MemoryValue;

trait AbstractFloatStackExecutor
{
    use ExecutorBaseLogic;
    /** FLOAT STACK CONV */

    public function INT2FLOATS() : void
    {
        $src_data = $this->dataPop();
        if ($src_data->getType() != MemoryDataType::INT) {
            throw new RuntimeTypeException("Invalid type of INT2FLOATS operand, expected INT as second operand");
        }
        $result = new MemoryValue(floatval($src_data->getValue()), MemoryDataType::FLOAT);
        $this->dataPush($result);
    }

    public function FLOAT2INTS() : void
    {
        $src_data = $this->dataPop();
        if ($src_data->getType() != MemoryDataType::FLOAT) {
            throw new RuntimeTypeException("Invalid type of FLOAT2INTS operand, expected FLOAT as second operand");
        }
        $result = new MemoryValue(intval($src_data->getValue()), MemoryDataType::INT);
        $this->dataPush($result);
    }
}