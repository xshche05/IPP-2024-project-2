<?php

namespace IPP\Student\Executor\Traits;

use IPP\Core\Exception\InternalErrorException;
use IPP\Student\Arguments\LabelArgument;
use IPP\Student\Arguments\LiteralArgument;
use IPP\Student\Arguments\TypeArgument;
use IPP\Student\Arguments\VarArgument;
use IPP\Student\Exceptions\InterpretSemanticException;
use IPP\Student\Exceptions\RuntimeMemoryFrameException;
use IPP\Student\Exceptions\RuntimeNoValueException;
use IPP\Student\Exceptions\RuntimeStringException;
use IPP\Student\Exceptions\RuntimeTypeException;
use IPP\Student\Exceptions\RuntimeUndefVarException;
use IPP\Student\Exceptions\RuntimeWrongValueException;
use IPP\Student\Variables\MemoryDataType;
use IPP\Student\Variables\MemoryFrame;
use IPP\Student\Variables\MemoryValue;
use IPP\Student\Variables\Variable;

trait BasicExecutorT
{
    use ExecutorBaseLogicT;
    /** MEMORY FRAME, FUNC CALL INSTRUCTIONS */

    /**
     * Function to execute the instruction MOVE
     * @param VarArgument $var variable to move the value to
     * @param VarArgument|LiteralArgument $symb variable or literal argument to move the value from
     * @throws RuntimeMemoryFrameException - if the frame is not defined
     * @throws RuntimeUndefVarException - if the variable is not defined in the frame
     * @throws InternalErrorException|RuntimeNoValueException - if the frame is unknown
     */
    public function MOVE(VarArgument $var, VarArgument|LiteralArgument $symb): void
    {
        $dest_var = $this->getFramedVariable($var);
        $src_symb = $this->getSymbMemoryValue($symb);
        $dest_var->assign($src_symb);
        $this->maxVars = max($this->maxVars, $this->countInitVars());
    }

    /**
     * Function to execute the instruction CREATEFRAME
     */
    public function CREATEFRAME(): void
    {
        $this->tempFrame = new MemoryFrame();
    }

    /**
     * Function to execute the instruction PUSHFRAME
     * @throws RuntimeMemoryFrameException - if there is no temporary frame to push on frame stack
     */
    public function PUSHFRAME(): void
    {
        if ($this->tempFrame === null) {
            throw new RuntimeMemoryFrameException("No temporary frame to push on frame stack");
        }
        $this->frameStack[] = $this->tempFrame;
        $this->tempFrame = null;
    }

    /**
     * Function to execute the instruction POPFRAME
     * @throws RuntimeMemoryFrameException - if there is no local frame to pop from frame stack
     */
    public function POPFRAME(): void
    {
        $this->tempFrame = $this->getLocalFrame();
        array_pop($this->frameStack);
    }

    /**
     * Function to execute the instruction DEFVAR
     * @param VarArgument $var variable to be defined
     * @throws InterpretSemanticException - if the variable already exists in the frame
     * @throws InternalErrorException - if the frame is unknown
     * @throws RuntimeMemoryFrameException - if the frame is not defined
     */
    public function DEFVAR(VarArgument $var): void
    {
        $frame = $var->getFrame();
        $name = $var->getName();
        $new_var = new Variable($name);
        if ($frame === "GF") {
            $this->globalFrame->createVariable($new_var);
        } elseif ($frame === "LF") {
            $this->getLocalFrame()->createVariable($new_var);
        } elseif ($frame === "TF") {
            if (!($this->tempFrame instanceof MemoryFrame))
                throw new RuntimeMemoryFrameException("No temporary frame");
            $this->tempFrame->createVariable($new_var);
        } else {
            throw new InternalErrorException("Unknown frame");
        }
        $this->maxVars = max($this->maxVars, $this->countInitVars());
    }

    /**
     * Function to execute the instruction CALL
     * @param LabelArgument $label label to call
     * @throws InterpretSemanticException
     */
    public function CALL(LabelArgument $label): void
    {
        $this->callStack[] = $this->instruction_pointer;
        $this->instruction_pointer = $this->program->getLabelOrder($label->getValue());
    }

    /**
     * Function to execute the instruction RETURN
     * @throws RuntimeNoValueException - if there is no return address on call stack
     */
    public function RETURN(): void
    {
        $return_order = array_pop($this->callStack);
        if ($return_order === null) {
            throw new RuntimeNoValueException("No return address on call stack");
        }
        $this->instruction_pointer = $return_order;
    }

    /** DATA STACK INSTRUCTIONS */

    /**
     * Function to execute the instruction PUSHS
     * @param VarArgument|LiteralArgument $symb variable or literal argument to push on data stack
     * @throws InternalErrorException - if the frame is unknown
     * @throws RuntimeMemoryFrameException - if the frame is not defined
     * @throws RuntimeUndefVarException - if the variable is not defined in the frame
     * @throws RuntimeNoValueException
     */
    public function PUSHS(VarArgument|LiteralArgument $symb): void
    {
        $data = $this->getSymbMemoryValue($symb);
        $this->dataPush($data);
    }

    /**
     * Function to execute the instruction POPS
     * @param VarArgument $var variable to pop the value to
     * @throws RuntimeNoValueException - if there is no value on data stack
     * @throws InternalErrorException - if the frame is unknown
     * @throws RuntimeMemoryFrameException - if the frame is not defined
     * @throws RuntimeUndefVarException - if the variable is not defined in the frame
     */
    public function POPS(VarArgument $var): void
    {
        $dest_var = $this->getFramedVariable($var);
        $src_data = $this->dataPop();
        $dest_var->assign($src_data);
        $this->maxVars = max($this->maxVars, $this->countInitVars());
    }

    /** ALU INSTRUCTIONS */

    /**
     * Function to execute the instruction ADD
     * @param VarArgument $var
     * @param VarArgument|LiteralArgument $symb1
     * @param VarArgument|LiteralArgument $symb2
     * @throws InternalErrorException
     * @throws RuntimeMemoryFrameException
     * @throws RuntimeTypeException
     * @throws RuntimeUndefVarException
     * @throws RuntimeNoValueException
     */
    public function ADD(VarArgument $var, VarArgument|LiteralArgument $symb1, VarArgument|LiteralArgument $symb2): void
    {
        $dest_var = $this->getFramedVariable($var);
        $src1_data = $this->getSymbMemoryValue($symb1);
        $src2_data = $this->getSymbMemoryValue($symb2);
        $dest_var->assign($src1_data->add($src2_data));
    }

    /**
     * Function to execute the instruction SUB
     * @param VarArgument $var
     * @param VarArgument|LiteralArgument $symb1
     * @param VarArgument|LiteralArgument $symb2
     * @throws InternalErrorException
     * @throws RuntimeMemoryFrameException
     * @throws RuntimeTypeException
     * @throws RuntimeUndefVarException
     * @throws RuntimeNoValueException
     */
    public function SUB(VarArgument $var, VarArgument|LiteralArgument $symb1, VarArgument|LiteralArgument $symb2): void
    {
        $dest_var = $this->getFramedVariable($var);
        $src1_data = $this->getSymbMemoryValue($symb1);
        $src2_data = $this->getSymbMemoryValue($symb2);
        $dest_var->assign($src1_data->sub($src2_data));
    }

    /**
     * Function to execute the instruction MUL
     * @param VarArgument $var
     * @param VarArgument|LiteralArgument $symb1
     * @param VarArgument|LiteralArgument $symb2
     * @throws InternalErrorException
     * @throws RuntimeMemoryFrameException
     * @throws RuntimeTypeException
     * @throws RuntimeUndefVarException
     * @throws RuntimeNoValueException
     */
    public function MUL(VarArgument $var, VarArgument|LiteralArgument $symb1, VarArgument|LiteralArgument $symb2): void
    {
        $dest_var = $this->getFramedVariable($var);
        $src1_data = $this->getSymbMemoryValue($symb1);
        $src2_data = $this->getSymbMemoryValue($symb2);
        $dest_var->assign($src1_data->mul($src2_data));
    }

    /**
     * Function to execute the instruction IDIV
     * @param VarArgument $var
     * @param VarArgument|LiteralArgument $symb1
     * @param VarArgument|LiteralArgument $symb2
     * @throws InternalErrorException
     * @throws RuntimeMemoryFrameException
     * @throws RuntimeTypeException
     * @throws RuntimeUndefVarException
     * @throws RuntimeWrongValueException
     * @throws RuntimeNoValueException
     */
    public function IDIV(VarArgument $var, VarArgument|LiteralArgument $symb1, VarArgument|LiteralArgument $symb2): void
    {
        $dest_var = $this->getFramedVariable($var);
        $src1_data = $this->getSymbMemoryValue($symb1);
        $src2_data = $this->getSymbMemoryValue($symb2);
        $dest_var->assign($src1_data->idiv($src2_data));
    }

    /**
     * Function to execute the instruction LT
     * @param VarArgument $var
     * @param VarArgument|LiteralArgument $symb1
     * @param VarArgument|LiteralArgument $symb2
     * @throws InternalErrorException
     * @throws RuntimeMemoryFrameException
     * @throws RuntimeTypeException
     * @throws RuntimeUndefVarException
     * @throws RuntimeNoValueException
     */
    public function LT(VarArgument $var, VarArgument|LiteralArgument $symb1, VarArgument|LiteralArgument $symb2): void
    {
        $dest_var = $this->getFramedVariable($var);
        $src1_data = $this->getSymbMemoryValue($symb1);
        $src2_data = $this->getSymbMemoryValue($symb2);
        $dest_var->assign($src1_data->lt($src2_data));
    }

    /**
     * Function to execute the instruction GT
     * @param VarArgument $var
     * @param VarArgument|LiteralArgument $symb1
     * @param VarArgument|LiteralArgument $symb2
     * @throws InternalErrorException
     * @throws RuntimeMemoryFrameException
     * @throws RuntimeTypeException
     * @throws RuntimeUndefVarException
     * @throws RuntimeNoValueException
     */
    public function GT(VarArgument $var, VarArgument|LiteralArgument $symb1, VarArgument|LiteralArgument $symb2): void
    {
        $dest_var = $this->getFramedVariable($var);
        $src1_data = $this->getSymbMemoryValue($symb1);
        $src2_data = $this->getSymbMemoryValue($symb2);
        $dest_var->assign($src1_data->gt($src2_data));
    }

    /**
     * Function to execute the instruction EQ
     * @param VarArgument $var
     * @param VarArgument|LiteralArgument $symb1
     * @param VarArgument|LiteralArgument $symb2
     * @throws InternalErrorException
     * @throws RuntimeMemoryFrameException
     * @throws RuntimeTypeException
     * @throws RuntimeUndefVarException
     * @throws RuntimeNoValueException
     */
    public function EQ(VarArgument $var, VarArgument|LiteralArgument $symb1, VarArgument|LiteralArgument $symb2): void
    {
        $dest_var = $this->getFramedVariable($var);
        $src1_data = $this->getSymbMemoryValue($symb1);
        $src2_data = $this->getSymbMemoryValue($symb2);
        $dest_var->assign($src1_data->eq($src2_data));
    }

    /**
     * Function to execute the instruction AND
     * @param VarArgument $var
     * @param VarArgument|LiteralArgument $symb1
     * @param VarArgument|LiteralArgument $symb2
     * @throws InternalErrorException
     * @throws RuntimeMemoryFrameException
     * @throws RuntimeTypeException
     * @throws RuntimeUndefVarException
     * @throws RuntimeNoValueException
     */
    public function AND(VarArgument $var, VarArgument|LiteralArgument $symb1, VarArgument|LiteralArgument $symb2): void
    {
        $dest_var = $this->getFramedVariable($var);
        $src1_data = $this->getSymbMemoryValue($symb1);
        $src2_data = $this->getSymbMemoryValue($symb2);
        $dest_var->assign($src1_data->and($src2_data));
    }

    /**
     * Function to execute the instruction OR
     * @param VarArgument $var
     * @param VarArgument|LiteralArgument $symb1
     * @param VarArgument|LiteralArgument $symb2
     * @throws InternalErrorException
     * @throws RuntimeMemoryFrameException
     * @throws RuntimeTypeException
     * @throws RuntimeUndefVarException
     * @throws RuntimeNoValueException
     */
    public function OR(VarArgument $var, VarArgument|LiteralArgument $symb1, VarArgument|LiteralArgument $symb2): void
    {
        $dest_var = $this->getFramedVariable($var);
        $src1_data = $this->getSymbMemoryValue($symb1);
        $src2_data = $this->getSymbMemoryValue($symb2);
        $dest_var->assign($src1_data->or($src2_data));
    }

    /**
     * Function to execute the instruction NOT
     * @param VarArgument $var
     * @param VarArgument|LiteralArgument $symb
     * @throws InternalErrorException
     * @throws RuntimeMemoryFrameException
     * @throws RuntimeTypeException
     * @throws RuntimeUndefVarException
     * @throws RuntimeNoValueException
     */
    public function NOT(VarArgument $var, VarArgument|LiteralArgument $symb): void
    {
        $dest_var = $this->getFramedVariable($var);
        $src_data = $this->getSymbMemoryValue($symb);
        $dest_var->assign($src_data->not());
    }

    /**
     * Function to execute the instruction INT2CHAR
     * @param VarArgument $var variable to store the result
     * @param VarArgument|LiteralArgument $symb operand
     * @throws RuntimeStringException - if the operand is not a valid character
     * @throws RuntimeTypeException - if the operand is not an integer
     * @throws InternalErrorException - internal errors
     * @throws RuntimeMemoryFrameException - if the frame is not defined
     * @throws RuntimeUndefVarException - if the variable is not defined in the frame
     * @throws RuntimeNoValueException
     */
    public function INT2CHAR(VarArgument $var, VarArgument|LiteralArgument $symb): void
    {
        $dest_var = $this->getFramedVariable($var);
        $src_data = $this->getSymbMemoryValue($symb);
        if ($src_data->getType() != MemoryDataType::INT) {
            throw new RuntimeTypeException("Invalid type of INT2CHAR operand, expected INT as second operand");
        }
        $data = mb_chr((int)$src_data->getValue(), "UTF-8");
        if (!$data) {
            throw new RuntimeStringException("INT2CHAR - Invalid character");
        }
        $result = new MemoryValue($data, MemoryDataType::STRING);
        $dest_var->assign($result);
    }

    /**
     * Function to execute the instruction STRI2INT
     * @param VarArgument $var variable to store the result
     * @param VarArgument|LiteralArgument $symb1 first operand
     * @param VarArgument|LiteralArgument $symb2 second operand
     * @throws RuntimeStringException - if the second operand is out of range or not a valid character
     * @throws RuntimeTypeException - if the first operand is not a string or the second operand is not an integer
     * @throws InternalErrorException - internal errors
     * @throws RuntimeMemoryFrameException - if the frame is not defined
     * @throws RuntimeUndefVarException - if the variable is not defined in the frame
     * @throws RuntimeNoValueException
     */
    public function STRI2INT(VarArgument $var, VarArgument|LiteralArgument $symb1, VarArgument|LiteralArgument $symb2): void
    {
        $dest_var = $this->getFramedVariable($var);
        $src1_data = $this->getSymbMemoryValue($symb1);
        $src2_data = $this->getSymbMemoryValue($symb2);
        if ($src1_data->getType() != MemoryDataType::STRING || $src2_data->getType() != MemoryDataType::INT) {
            throw new RuntimeTypeException("Invalid type of STRI2INT operands, expected STRING and INT");
        }
        if ($src2_data->getValue() < 0 || $src2_data->getValue() >= mb_strlen((string)$src1_data->getValue())) {
            throw new RuntimeStringException("STRI2INT - Index out of range");
        }
        $string = (string)$src1_data->getValue();
        $char = mb_substr($string, (int)$src2_data->getValue(), 1, "UTF-8");
        $data = mb_ord($char, "UTF-8");
        if (!$data) {
            throw new RuntimeStringException("STRI2INT - Invalid character");
        }
        $result = new MemoryValue($data, MemoryDataType::INT);
        $dest_var->assign($result);
    }

    /** IO INSTRUCTIONS */

    /**
     * Function to execute the instruction READ
     * @param VarArgument $var variable to store the result
     * @param TypeArgument $type type of the input
     * @throws InternalErrorException - if the type of the input is unknown
     * @throws RuntimeMemoryFrameException - if the frame is not defined
     * @throws InterpretSemanticException if the type of the input is invalid
     * @throws RuntimeUndefVarException|RuntimeNoValueException - if the variable is not defined in the frame
     */
    public function READ(VarArgument $var, TypeArgument $type): void
    {
        $dest_var = $this->getFramedVariable($var);
        $type_str = $type->getValue();
        switch ($type_str) {
            case "int":
                $input = $this->stdin->readInt();
                $result = new MemoryValue($input, $input === null ? MemoryDataType::NIL : MemoryDataType::INT);
                break;
            case "string":
                $input = $this->stdin->readString();
                $result = new MemoryValue($input, $input === null ? MemoryDataType::NIL : MemoryDataType::STRING);
                break;
            case "bool":
                $input = $this->stdin->readBool();
                $result = new MemoryValue($input, $input === null ? MemoryDataType::NIL : MemoryDataType::BOOL);
                break;
            case "float":
                $input = $this->stdin->readFloat();
                $result = new MemoryValue($input, $input === null ? MemoryDataType::NIL : MemoryDataType::FLOAT);
                break;
            default:
                throw new InterpretSemanticException("Invalid type of READ operand");
        }
        $dest_var->assign($result);
    }

    /**
     * Function to execute the instruction WRITE
     * @param VarArgument|LiteralArgument $symb variable or literal argument to write
     * @throws InternalErrorException - if the type of the input is unknown
     * @throws RuntimeMemoryFrameException - if the frame is not defined
     * @throws RuntimeUndefVarException - if the variable is not defined in the frame
     * @throws RuntimeNoValueException
     */
    public function WRITE(VarArgument|LiteralArgument $symb): void
    {
        $src_data = $this->getSymbMemoryValue($symb);
        if ($src_data->getType() === MemoryDataType::INT) {
            $this->stdout->writeInt((int)$src_data->getValue());
        } elseif ($src_data->getType() === MemoryDataType::STRING) {
            $this->stdout->writeString((string)$src_data->getValue());
        } elseif ($src_data->getType() === MemoryDataType::BOOL) {
            $this->stdout->writeBool((bool)$src_data->getValue());
        } elseif ($src_data->getType() === MemoryDataType::FLOAT) {
            $this->stdout->writeFloat((float)$src_data->getValue());
        } elseif ($src_data->getType() === MemoryDataType::NIL) {
            $this->stdout->writeString("");
        } else {
            throw new InternalErrorException("Invalid type of WRITE operand");
        }
    }

    /** STRING INSTRUCTIONS */

    /**
     * Function to execute the instruction CONCAT
     * @param VarArgument $var variable to store the result
     * @param VarArgument|LiteralArgument $symb1 first operand
     * @param VarArgument|LiteralArgument $symb2 second operand
     * @throws RuntimeMemoryFrameException - if the frame is not defined
     * @throws RuntimeUndefVarException - if the variable is not defined in the frame
     * @throws InternalErrorException - internal errors
     * @throws RuntimeTypeException - if the operands are not strings
     * @throws RuntimeNoValueException
     */
    public function CONCAT(VarArgument $var, VarArgument|LiteralArgument $symb1, VarArgument|LiteralArgument $symb2): void
    {
        $dest_var = $this->getFramedVariable($var);
        $src1_data = $this->getSymbMemoryValue($symb1);
        $src2_data = $this->getSymbMemoryValue($symb2);
        if ($src1_data->getType() != MemoryDataType::STRING || $src2_data->getType() != MemoryDataType::STRING) {
            throw new RuntimeTypeException("CONCAT - Invalid type");
        }
        $result = new MemoryValue($src1_data->getValue() . $src2_data->getValue(), MemoryDataType::STRING);
        $dest_var->assign($result);
    }

    /**
     * Function to execute the instruction STRLEN
     * @param VarArgument $var variable to store the result
     * @param VarArgument|LiteralArgument $symb operand
     * @throws RuntimeMemoryFrameException - if the frame is not defined
     * @throws RuntimeUndefVarException - if the variable is not defined in the frame
     * @throws InternalErrorException - internal errors
     * @throws RuntimeTypeException - if the operand is not a string
     * @throws RuntimeNoValueException
     */
    public function STRLEN(VarArgument $var, VarArgument|LiteralArgument $symb): void
    {
        $dest_var = $this->getFramedVariable($var);
        $src_data = $this->getSymbMemoryValue($symb);
        if ($src_data->getType() != MemoryDataType::STRING) {
            throw new RuntimeTypeException("STRLEN - Invalid type");
        }
        $result = new MemoryValue(mb_strlen((string)$src_data->getValue()), MemoryDataType::INT);
        $dest_var->assign($result);
    }

    /**
     * Function to execute the instruction GETCHAR
     * @param VarArgument $var variable to store the result
     * @param VarArgument|LiteralArgument $symb1 first operand
     * @param VarArgument|LiteralArgument $symb2 second operand
     * @throws RuntimeMemoryFrameException - if the frame is not defined
     * @throws RuntimeUndefVarException - if the variable is not defined in the frame
     * @throws InternalErrorException - internal errors
     * @throws RuntimeTypeException - if the operands are not of correct types
     * @throws RuntimeStringException - if the index is out of range
     * @throws RuntimeNoValueException
     */
    public function GETCHAR(VarArgument $var, VarArgument|LiteralArgument $symb1, VarArgument|LiteralArgument $symb2): void
    {
        $dest_var = $this->getFramedVariable($var);
        $src1_data = $this->getSymbMemoryValue($symb1);
        $src2_data = $this->getSymbMemoryValue($symb2);
        if ($src1_data->getType() != MemoryDataType::STRING || $src2_data->getType() != MemoryDataType::INT) {
            throw new RuntimeTypeException("GETCHAR - Invalid type");
        }
        if ($src2_data->getValue() < 0 || $src2_data->getValue() >= mb_strlen((string)$src1_data->getValue())) {
            throw new RuntimeStringException("GETCHAR - Index out of range");
        }
        $char = mb_substr((string)$src1_data->getValue(), (int)$src2_data->getValue(), 1, "UTF-8");
        $result = new MemoryValue($char, MemoryDataType::STRING);
        $dest_var->assign($result);
    }

    /**
     * Function to execute the instruction SETCHAR
     * @param VarArgument $var variable to store the result
     * @param VarArgument|LiteralArgument $symb1 first operand
     * @param VarArgument|LiteralArgument $symb2 second operand
     * @throws RuntimeMemoryFrameException - if the frame is not defined
     * @throws RuntimeUndefVarException - if the variable is not defined in the frame
     * @throws InternalErrorException - internal errors
     * @throws RuntimeTypeException - if the operands are not of correct types
     * @throws RuntimeStringException - if the index is out of range
     * @throws RuntimeNoValueException
     */
    public function SETCHAR(VarArgument $var, VarArgument|LiteralArgument $symb1, VarArgument|LiteralArgument $symb2): void
    {
        $dest_var = $this->getFramedVariable($var);
        $src1_data = $this->getSymbMemoryValue($symb1);
        $src2_data = $this->getSymbMemoryValue($symb2);
        if ($dest_var->getType() != MemoryDataType::STRING || $src1_data->getType() != MemoryDataType::INT || $src2_data->getType() != MemoryDataType::STRING) {
            throw new RuntimeTypeException("SETCHAR - Invalid type");
        }
        if ($src1_data->getValue() < 0 || $src1_data->getValue() >= mb_strlen((string)$dest_var->getValue())) {
            throw new RuntimeStringException("SETCHAR - Index out of range");
        }
        $data = (string)$dest_var->getValue();
        // set index in unicode string to new char
        $char = mb_substr((string)$src2_data->getValue(), 0, 1, "UTF-8");
        $data = mb_substr($data, 0, (int)$src1_data->getValue(), "UTF-8") . $char . mb_substr($data, (int)$src1_data->getValue() + 1, null, "UTF-8");
        $result = new MemoryValue($data, MemoryDataType::STRING);
        $dest_var->assign($result);
    }

    /** TYPE WORK INSTRUCTIONS */

    /**
     * Function to execute the instruction TYPE
     * @param VarArgument $var variable to store the result
     * @param VarArgument|LiteralArgument $symb operand
     * @throws RuntimeUndefVarException - if the variable is not defined in the frame
     * @throws RuntimeMemoryFrameException - if the frame is not defined
     * @throws InternalErrorException - internal errors
     * @throws RuntimeNoValueException
     */
    public function TYPE(VarArgument $var, VarArgument|LiteralArgument $symb): void
    {
        $dest_var = $this->getFramedVariable($var);
        $src_data = $this->getSymbMemoryValue($symb, true);
        $value = match ($src_data->getType()) {
            MemoryDataType::INT => "int",
            MemoryDataType::STRING => "string",
            MemoryDataType::BOOL => "bool",
            MemoryDataType::FLOAT => "float",
            MemoryDataType::NIL => "nil",
            MemoryDataType::NONE_TYPE => ""
        };
        $result = new MemoryValue($value, MemoryDataType::STRING);
        $dest_var->assign($result);
    }

    /** PROGRAM FLOW INSTRUCTIONS */

    /**
     * Function to execute the instruction LABEL, does nothing
     * @param LabelArgument $label label
     */
    public function LABEL(LabelArgument $label): void
    {
        $this->executed_instruction_count--;
    }

    /**
     * Function to execute the instruction JUMP
     * @param LabelArgument $label label to jump to
     * @throws InterpretSemanticException
     */
    public function JUMP(LabelArgument $label): void
    {
        $this->instruction_pointer = $this->program->getLabelOrder($label->getValue());
    }

    /**
     * Function to execute the instruction JUMPIFEQ
     * @param LabelArgument $label label to jump to
     * @param VarArgument|LiteralArgument $symb1 first operand
     * @param VarArgument|LiteralArgument $symb2 second operand
     * @throws RuntimeTypeException - if the operands are not of the same type
     * @throws InternalErrorException - internal errors
     * @throws RuntimeMemoryFrameException - if the frame is not defined
     * @throws RuntimeUndefVarException|InterpretSemanticException - if the variable is not defined in the frame
     * @throws RuntimeNoValueException
     */
    public function JUMPIFEQ(LabelArgument $label, VarArgument|LiteralArgument $symb1, VarArgument|LiteralArgument $symb2): void
    {
        $src1_data = $this->getSymbMemoryValue($symb1);
        $src2_data = $this->getSymbMemoryValue($symb2);
        if ($src1_data->getType() != $src2_data->getType()) {
            if ($src1_data->getType() != MemoryDataType::NIL && $src2_data->getType() != MemoryDataType::NIL) {
                throw new RuntimeTypeException("Different (no-nil) types of JUMPIFEQ operands");
            }
        }
        if ($src1_data->getValue() === $src2_data->getValue()) {
            $this->instruction_pointer = $this->program->getLabelOrder($label->getValue());
        }
    }

    /**
     * Function to execute the instruction JUMPIFNEQ
     * @param LabelArgument $label label to jump to
     * @param VarArgument|LiteralArgument $symb1 first operand
     * @param VarArgument|LiteralArgument $symb2 second operand
     * @throws RuntimeTypeException - if the operands are not of the same type
     * @throws InternalErrorException - internal errors
     * @throws RuntimeMemoryFrameException - if the frame is not defined
     * @throws RuntimeUndefVarException - if the variable is not defined in the frame
     * @throws InterpretSemanticException
     * @throws RuntimeNoValueException
     */
    public function JUMPIFNEQ(LabelArgument $label, VarArgument|LiteralArgument $symb1, VarArgument|LiteralArgument $symb2): void
    {
        $src1_data = $this->getSymbMemoryValue($symb1);
        $src2_data = $this->getSymbMemoryValue($symb2);
        if ($src1_data->getType() != $src2_data->getType()) {
            if ($src1_data->getType() != MemoryDataType::NIL && $src2_data->getType() != MemoryDataType::NIL) {
                throw new RuntimeTypeException("Different (no-nil) types of JUMPIFNEQ operands");
            }
        }
        if ($src1_data->getValue() !== $src2_data->getValue()) {
            $this->instruction_pointer = $this->program->getLabelOrder($label->getValue());
        }
    }

    /**
     * Function to execute the instruction EXIT
     * @param VarArgument|LiteralArgument $symb exit code
     * @throws InternalErrorException - internal errors
     * @throws RuntimeMemoryFrameException - if the frame is not defined
     * @throws RuntimeUndefVarException - if the variable is not defined in the frame
     * @throws RuntimeWrongValueException - if the exit code is out of range 0-9
     * @throws RuntimeTypeException
     * @throws RuntimeNoValueException
     */
    public function EXIT(VarArgument|LiteralArgument $symb): void
    {
        $src_data = $this->getSymbMemoryValue($symb);
        if ($src_data->getType() != MemoryDataType::INT) {
            throw new RuntimeTypeException("Invalid type of EXIT operand");
        }
        $code = $src_data->getValue();
        if ($code < 0 || $code > 9) {
            throw new RuntimeWrongValueException("EXIT - Invalid exit code $code must be in range 0-9");
        }
        self::$stop_execution = true;
        self::$ret_code = (int)$code;
    }

    /** DEBUG INSTRUCTIONS */

    /**
     * Function to execute the instruction DPRINT
     * @param VarArgument|LiteralArgument $symb variable or literal argument to write
     * @throws InternalErrorException - if the type of the input is unknown
     * @throws RuntimeMemoryFrameException - if the frame is not defined
     * @throws RuntimeUndefVarException - if the variable is not defined in the frame
     * @throws RuntimeNoValueException
     */
    public function DPRINT(VarArgument|LiteralArgument $symb): void
    {
        $this->executed_instruction_count--;
        $src_data = $this->getSymbMemoryValue($symb);
        if ($src_data->getType() === MemoryDataType::INT) {
            $this->stderr->writeInt((int)$src_data->getValue());
        } elseif ($src_data->getType() === MemoryDataType::STRING) {
            $this->stderr->writeString((string)$src_data->getValue());
        } elseif ($src_data->getType() === MemoryDataType::BOOL) {
            $this->stderr->writeBool((bool)$src_data->getValue());
        } elseif ($src_data->getType() === MemoryDataType::FLOAT) {
            $this->stderr->writeFloat((float)$src_data->getValue());
        } elseif ($src_data->getType() === MemoryDataType::NIL) {
            $this->stderr->writeString("");
        } else {
            throw new InternalErrorException("Invalid type of DPRINT operand");
        }
    }

    /**
     * Function to execute the instruction BREAK
     * @throws RuntimeMemoryFrameException
     */
    public function BREAK(): void
    {
        $this->stderr->writeString("\n\nCurrent state of the executor:\n\n");
        $this->stderr->writeString("Instruction pointer: $this->instruction_pointer\n");
        $this->stderr->writeString("Executed instructions: $this->executed_instruction_count\n");
        $this->stderr->writeString("Global frame:\n");
        $this->stderr->writeString($this->globalFrame->toString());
        if (count($this->frameStack) > 0) {
            $this->stderr->writeString("Local frame:\n");
            $this->stderr->writeString($this->getLocalFrame()->toString());
        } else {
            $this->stderr->writeString("No local frame\n");
        }
        if ($this->tempFrame !== null) {
            $this->stderr->writeString("Temporary frame:\n");
            $this->stderr->writeString($this->tempFrame->toString());
        } else {
            $this->stderr->writeString("No temporary frame\n");
        }
        $this->stderr->writeString("Data stack:\n");
        $data_stack = "{\n";
        foreach ($this->dataStack as $data) {
            $data_stack .= "  " . $data->toString() . "\n";
        }
        $data_stack .= "}\n";
        $this->stderr->writeString($data_stack);
        $this->stderr->writeString("Call stack:\n");
        $call_stack = "{\n";
        foreach ($this->callStack as $call) {
            $call_stack .= "Return order:  $call\n";
        }
        $call_stack .= "}\n";
        $this->stderr->writeString($call_stack);
        $this->executed_instruction_count--;
    }
}