<?php

namespace IPP\Student\Executor;

use IPP\Core\Exception\InternalErrorException;
use IPP\Student\Arguments\LabelArgument;
use IPP\Student\Arguments\LiteralArgument;
use IPP\Student\Arguments\VarArgument;
use IPP\Student\Exceptions\RuntimeStringException;
use IPP\Student\Exceptions\RuntimeTypeException;
use IPP\Student\Exceptions\RuntimeWrongValueException;
use IPP\Student\Variables\MemoryDataType;
use IPP\Student\Variables\MemoryValue;
use IPP\Student\Variables\TypePair;

trait AbstractStackExecutor
{
    use ExecutorBaseLogic;
    /** STACK */

    public function CLEARS(): void
    {
        $this->dataStack = [];
    }

    public function ADDS(): void
    {
        $src2_data = $this->dataPop();
        $src1_data = $this->dataPop();
        $this->dataPush($src1_data->add($src2_data));
    }

    public function SUBS(): void
    {
        $src2_data = $this->dataPop();
        $src1_data = $this->dataPop();
        $this->dataPush($src1_data->sub($src2_data));
    }

    public function MULS(): void
    {
        $src2_data = $this->dataPop();
        $src1_data = $this->dataPop();
        $this->dataPush($src1_data->mul($src2_data));
    }

    public function IDIVS(): void
    {
        $src2_data = $this->dataPop();
        $src1_data = $this->dataPop();
        $this->dataPush($src1_data->idiv($src2_data));
    }

    public function DIVS(): void
    {
        $src2_data = $this->dataPop();
        $src1_data = $this->dataPop();
        $this->dataPush($src1_data->div($src2_data));
    }

    public function LTS(): void
    {
        $src2_data = $this->dataPop();
        $src1_data = $this->dataPop();
        $this->dataPush($src1_data->lt($src2_data));
    }

    public function GTS(): void
    {
        $src2_data = $this->dataPop();
        $src1_data = $this->dataPop();
        $this->dataPush($src1_data->gt($src2_data));
    }

    public function EQS(): void
    {
        $src2_data = $this->dataPop();
        $src1_data = $this->dataPop();
        $this->dataPush($src1_data->eq($src2_data));
    }

    public function ANDS(): void
    {
        $src2_data = $this->dataPop();
        $src1_data = $this->dataPop();
        $this->dataPush($src1_data->and($src2_data));
    }

    public function ORS(): void
    {
        $src2_data = $this->dataPop();
        $src1_data = $this->dataPop();
        $this->dataPush($src1_data->or($src2_data));
    }

    public function NOTS(): void
    {
        $src_data = $this->dataPop();
        $this->dataPush($src_data->not());
    }

    public function INT2CHARS(): void
    {
        $src_data = $this->dataPop();
        if ($src_data->getType() != MemoryDataType::INT) {
            throw new RuntimeTypeException("Invalid type of INT2CHARS operand, expected INT as second operand");
        }
        $data = mb_chr((int)$src_data->getValue(), "UTF-8");
        if (!$data) {
            throw new RuntimeStringException("INT2CHARS - Invalid character");
        }
        $this->dataPush(new MemoryValue($data, MemoryDataType::STRING));
    }

    public function STRI2INTS(): void
    {
        $src2_data = $this->dataPop();
        $src1_data = $this->dataPop();
        if ($src1_data->getType() != MemoryDataType::STRING || $src2_data->getType() != MemoryDataType::INT) {
            throw new RuntimeTypeException("Invalid type of STRI2INTS operands, expected STRING and INT");
        }
        if ($src2_data->getValue() < 0 || $src2_data->getValue() >= strlen((string)$src1_data->getValue())) {
            throw new RuntimeStringException("STRI2INTS - Index out of range");
        }
        $data = mb_ord(((string)$src1_data->getValue())[$src2_data->getValue()], "UTF-8");
        if (!$data) {
            throw new RuntimeStringException("STRI2INTS - Invalid character");
        }
        $this->dataPush(new MemoryValue($data, MemoryDataType::INT));
    }

    public function JUMPIFEQS(LabelArgument $label): void
    {
        $src2_data = $this->dataPop();
        $src1_data = $this->dataPop();
        if ($src1_data->getType() != $src2_data->getType()) {
            if ($src1_data->getType() != MemoryDataType::NIL && $src2_data->getType() != MemoryDataType::NIL) {
                throw new RuntimeTypeException("Different (no-nil) types of JUMPIFEQS operands");
            }
        }
        if ($src1_data->getValue() === $src2_data->getValue()) {
            $this->instruction_pointer = $this->program->getLabelOrder($label->getValue());
        }
    }

    public function JUMPIFNEQS(LabelArgument $label): void
    {
        $src2_data = $this->dataPop();
        $src1_data = $this->dataPop();
        if ($src1_data->getType() != $src2_data->getType()) {
            if ($src1_data->getType() != MemoryDataType::NIL && $src2_data->getType() != MemoryDataType::NIL) {
                throw new RuntimeTypeException("Different (no-nil) types of JUMPIFNEQS operands");
            }
        }
        if ($src1_data->getValue() != $src2_data->getValue()) {
            $this->instruction_pointer = $this->program->getLabelOrder($label->getValue());
        }
    }
}