<?php

namespace IPP\Student\Executor\Traits;

use IPP\Student\Exceptions\RuntimeNoValueException;
use IPP\Student\Exceptions\RuntimeTypeException;
use IPP\Student\Exceptions\RuntimeWrongValueException;
use IPP\Student\Variables\MemoryDataType;
use IPP\Student\Variables\MemoryValue;

trait FloatStackExecutorT
{
    use ExecutorBaseLogicT;
    /** FLOAT STACK CONV */

    /**
     * @return void
     * @throws RuntimeTypeException
     * @throws RuntimeNoValueException
     */
    public function INT2FLOATS() : void
    {
        $src_data = $this->dataPop();
        if ($src_data->getType() != MemoryDataType::INT) {
            throw new RuntimeTypeException("Invalid type of INT2FLOATS operand, expected INT as second operand");
        }
        $result = new MemoryValue(floatval($src_data->getValue()), MemoryDataType::FLOAT);
        $this->dataPush($result);
    }

    /**
     * @throws RuntimeNoValueException
     * @throws RuntimeTypeException
     */
    public function FLOAT2INTS() : void
    {
        $src_data = $this->dataPop();
        if ($src_data->getType() != MemoryDataType::FLOAT) {
            throw new RuntimeTypeException("Invalid type of FLOAT2INTS operand, expected FLOAT as second operand");
        }
        $result = new MemoryValue(intval($src_data->getValue()), MemoryDataType::INT);
        $this->dataPush($result);
    }


    /**
     * @throws RuntimeNoValueException
     * @throws RuntimeWrongValueException
     * @throws RuntimeTypeException
     */
    public function DIVS(): void
    {
        $src2_data = $this->dataPop();
        $src1_data = $this->dataPop();
        $this->dataPush($src1_data->div($src2_data));
    }
}