<?php

namespace IPP\Student\executor;

use IPP\Core\Exception\InternalErrorException;
use IPP\Student\arguments\LabelArgument;
use IPP\Student\arguments\LiteralArgument;
use IPP\Student\arguments\VarArgument;
use IPP\Student\exceptions\RuntimeStringException;
use IPP\Student\exceptions\RuntimeTypeException;
use IPP\Student\exceptions\RuntimeWrongValueException;
use IPP\Student\variables\MemoryDataType;
use IPP\Student\variables\MemoryValue;
use IPP\Student\variables\TypePair;

trait AbstractStackExecutor
{
    use ExecutorBaseLogic;
    /** STACK */

    public function CLEARS(): void
    {
        $this->dataStack = [];
    }

    /**
     * @param MemoryValue $symb1
     * @param MemoryValue $symb2
     * @param string $operation
     * @param array<TypePair> $allowedTypeComb
     * @return MemoryValue
     * @throws InternalErrorException
     * @throws RuntimeTypeException
     * @throws RuntimeWrongValueException
     */
    protected function basicStackOperation(MemoryValue $symb1, MemoryValue $symb2,
                                           string $operation,
                                           array $allowedTypeComb): MemoryValue
    {
        $type_comb = new TypePair($symb1->getType(), $symb2->getType());
        if (!in_array($type_comb, $allowedTypeComb)) {
            throw new RuntimeTypeException("Invalid type combination for $operation");
        }
        $val1 = $symb1->getValue();
        $val2 = $symb2->getValue();
        if (is_numeric($val1) && is_numeric($val2)) {
            if (($operation === "IDIVS" || $operation === "DIVS") && $val2 == 0) {
                throw new RuntimeWrongValueException("Division by zero");
            }
            $result_val = match ($operation) {
                "ADDS" => $val1 + $val2,
                "SUBS" => $val1 - $val2,
                "MULS" => $val1 * $val2,
                "IDIVS" => intdiv((int)$val1, (int)$val2),
                "DIVS" => $val1 / $val2,

                "LTS" => ($val1 < $val2),
                "GTS" => ($val1 > $val2),
                "EQS" => ($val1 === $val2),
                default => throw new InternalErrorException("Invalid operation"),
            };
            $result_type = match ($operation) {
                "ADDS", "SUBS", "MULS" => $symb1->getType(),
                "IDIVS" => MemoryDataType::INT,
                "DIVS" => MemoryDataType::FLOAT,
                "LTS", "GTS", "EQS" => MemoryDataType::BOOL,
                default => throw new InternalErrorException("Invalid operation")
            };
        } else {
            $result_val = match ($operation) {
                "LTS" => ($val1 < $val2),
                "GTS" => ($val1 > $val2),
                "EQS" => ($val1 === $val2),
                "ANDS" => $val1 && $val2,
                "ORS" => $val1 || $val2,
                "NOTS" => !$val1,
                default => throw new InternalErrorException("Invalid operation"),
            };
            $result_type = MemoryDataType::BOOL;
        }
        return new MemoryValue($result_val, $result_type);
    }

    public function ADDS(): void
    {
        $src2_data = $this->dataPop();
        $src1_data = $this->dataPop();
        $result = $this->basicStackOperation($src1_data, $src2_data, "ADDS", [
            new TypePair(MemoryDataType::INT, MemoryDataType::INT),
            new TypePair(MemoryDataType::FLOAT, MemoryDataType::FLOAT),
        ]);
        $this->dataPush($result);
    }

    public function SUBS(): void
    {
        $src2_data = $this->dataPop();
        $src1_data = $this->dataPop();
        $result = $this->basicStackOperation($src1_data, $src2_data, "SUBS", [
            new TypePair(MemoryDataType::INT, MemoryDataType::INT),
            new TypePair(MemoryDataType::FLOAT, MemoryDataType::FLOAT),
        ]);
        $this->dataPush($result);
    }

    public function MULS(): void
    {
        $src2_data = $this->dataPop();
        $src1_data = $this->dataPop();
        $result = $this->basicStackOperation($src1_data, $src2_data, "MULS", [
            new TypePair(MemoryDataType::INT, MemoryDataType::INT),
            new TypePair(MemoryDataType::FLOAT, MemoryDataType::FLOAT),
        ]);
        $this->dataPush($result);
    }

    public function IDIVS(): void
    {
        $src2_data = $this->dataPop();
        $src1_data = $this->dataPop();
        $result = $this->basicStackOperation($src1_data, $src2_data, "IDIVS", [
            new TypePair(MemoryDataType::INT, MemoryDataType::INT),
        ]);
        $this->dataPush($result);
    }

    public function DIVS(): void
    {
        $src2_data = $this->dataPop();
        $src1_data = $this->dataPop();
        $result = $this->basicStackOperation($src1_data, $src2_data, "DIVS", [
            new TypePair(MemoryDataType::FLOAT, MemoryDataType::FLOAT),
        ]);
        $this->dataPush($result);
    }

    public function LTS(): void
    {
        $src2_data = $this->dataPop();
        $src1_data = $this->dataPop();
        $result = $this->basicStackOperation($src1_data, $src2_data, "LTS", [
            new TypePair(MemoryDataType::INT, MemoryDataType::INT),
            new TypePair(MemoryDataType::FLOAT, MemoryDataType::FLOAT),
            new TypePair(MemoryDataType::STRING, MemoryDataType::STRING),
            new TypePair(MemoryDataType::BOOL, MemoryDataType::BOOL),
        ]);
        $this->dataPush($result);
    }

    public function GTS(): void
    {
        $src2_data = $this->dataPop();
        $src1_data = $this->dataPop();
        $result = $this->basicStackOperation($src1_data, $src2_data, "GTS", [
            new TypePair(MemoryDataType::INT, MemoryDataType::INT),
            new TypePair(MemoryDataType::FLOAT, MemoryDataType::FLOAT),
            new TypePair(MemoryDataType::STRING, MemoryDataType::STRING),
            new TypePair(MemoryDataType::BOOL, MemoryDataType::BOOL),
        ]);
        $this->dataPush($result);
    }

    public function EQS(): void
    {
        $src2_data = $this->dataPop();
        $src1_data = $this->dataPop();
        $result = $this->basicStackOperation($src1_data, $src2_data, "EQS", [
            new TypePair(MemoryDataType::INT, MemoryDataType::INT),
            new TypePair(MemoryDataType::FLOAT, MemoryDataType::FLOAT),
            new TypePair(MemoryDataType::STRING, MemoryDataType::STRING),
            new TypePair(MemoryDataType::BOOL, MemoryDataType::BOOL),

            new TypePair(MemoryDataType::NIL, MemoryDataType::NIL),

            new TypePair(MemoryDataType::INT, MemoryDataType::NIL),
            new TypePair(MemoryDataType::FLOAT, MemoryDataType::NIL),
            new TypePair(MemoryDataType::STRING, MemoryDataType::NIL),
            new TypePair(MemoryDataType::BOOL, MemoryDataType::NIL),

            new TypePair(MemoryDataType::NIL, MemoryDataType::INT),
            new TypePair(MemoryDataType::NIL, MemoryDataType::FLOAT),
            new TypePair(MemoryDataType::NIL, MemoryDataType::STRING),
            new TypePair(MemoryDataType::NIL, MemoryDataType::BOOL),
        ]);
        $this->dataPush($result);
    }

    public function ANDS(): void
    {
        $src2_data = $this->dataPop();
        $src1_data = $this->dataPop();
        $result = $this->basicStackOperation($src1_data, $src2_data, "ANDS", [
            new TypePair(MemoryDataType::BOOL, MemoryDataType::BOOL),
        ]);
        $this->dataPush($result);
    }

    public function ORS(): void
    {
        $src2_data = $this->dataPop();
        $src1_data = $this->dataPop();
        $result = $this->basicStackOperation($src1_data, $src2_data, "ORS", [
            new TypePair(MemoryDataType::BOOL, MemoryDataType::BOOL),
        ]);
        $this->dataPush($result);
    }

    public function NOTS(): void
    {
        $src_data = $this->dataPop();
        $result = $this->basicStackOperation($src_data, $src_data, "NOTS", [
            new TypePair(MemoryDataType::BOOL, MemoryDataType::BOOL),
        ]);
        $this->dataPush($result);
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