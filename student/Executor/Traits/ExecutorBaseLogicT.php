<?php

namespace IPP\Student\Executor\Traits;

use IPP\Core\Exception\InternalErrorException;
use IPP\Core\Interface\InputReader;
use IPP\Core\Interface\OutputWriter;
use IPP\Student\Arguments\LiteralArgument;
use IPP\Student\Arguments\VarArgument;
use IPP\Student\Exceptions\InvalidSourceStructure;
use IPP\Student\Exceptions\RuntimeMemoryFrameException;
use IPP\Student\Exceptions\RuntimeNoValueException;
use IPP\Student\Exceptions\RuntimeUndefVarException;
use IPP\Student\Program\Program;
use IPP\Student\Variables\MemoryDataType;
use IPP\Student\Variables\MemoryFrame;
use IPP\Student\Variables\MemoryValue;
use IPP\Student\Variables\Variable;

trait ExecutorBaseLogicT
{
    /** @var int $instruction_pointer current execution instruction order */
    protected int $instruction_pointer;
    /** @var Program $program program to be executed */
    protected Program $program;
    /** @var int[] call stack */
    protected array $callStack;
    /** @var MemoryFrame[] frame stack */
    protected array $frameStack;
    /** @var int $maxVars maximum count of variables in the program in single moment */
    protected int $maxVars;
    protected int $currentVarCount;
    /** @var MemoryValue[] data stack */
    protected array $dataStack;
    /** @var int $maxDataStackSize maximum size of data stack in the program in single moment */
    protected int $maxDataStackSize;
    /** @var MemoryFrame $globalFrame global frame */
    protected MemoryFrame $globalFrame;
    /** @var MemoryFrame|null $tempFrame temporary frame */
    protected MemoryFrame|null $tempFrame;
    /** @var InputReader $stdin input (stdin) reader */
    protected InputReader $stdin;
    /** @var OutputWriter $stdout output (stdout) writer */
    protected OutputWriter $stdout;
    /** @var OutputWriter $stderr error (stderr) writer */
    protected OutputWriter $stderr;
    /** @var int $executed_instruction_count count of executed instructions */
    protected int $executed_instruction_count;
    protected static int $ret_code = 0;
    protected static bool $stop_execution = false;

    /**
     * Constructor of Executor, blocks creating new instances
     */
    protected function __construct() {
    }

    /**
     * Clone method of Executor, blocks cloning
     */
    protected function __clone() {
    }

    /**
     * Wakeup method of Executor, blocks deserializing
     * @throws InternalErrorException - if deserializing is attempted
     */
    public function __wakeup() {
        throw new InternalErrorException("Deserializing is not allowed");
    }

    /**
     * Function to get total count of executed instructions
     * @return int - total count of executed instructions
     */
    public function getExecutedInstructionCount(): int
    {
        return $this->executed_instruction_count;
    }

    /**
     * Function to get maximum count of variables in the program in single moment
     * @return int - maximum count of variables in the program in single moment
     */
    public function getMaxVarCount(): int
    {
        return $this->maxVars;
    }

    /**
     * Function to get maximum size of data stack in the program in single moment
     * @return int - maximum size of data stack in the program in single moment
     */
    public function getMaxDataStackSize(): int
    {
        return $this->maxDataStackSize;
    }

    /**
     * Function to initialize Executor
     * @param Program $program - program to be executed
     * @param InputReader $stdin - input (stdin) reader
     * @param OutputWriter $stdout - output (stdout) writer
     * @param OutputWriter $stderr - error (stderr) writer
     */
    public function init(Program $program, InputReader $stdin, OutputWriter $stdout, OutputWriter $stderr): void
    {
        $this->instruction_pointer = $program->getStartOrder();
        $this->program = $program;
        $this->callStack = [];
        $this->frameStack = [];
        $this->dataStack = [];
        $this->globalFrame = new MemoryFrame();
        $this->tempFrame = null;
        $this->stdin = $stdin;
        $this->stdout = $stdout;
        $this->stderr = $stderr;
        $this->maxVars = 0;
        $this->maxDataStackSize = 0;
        $this->executed_instruction_count = 0;
        $this->currentVarCount = 0;
    }

    /**
     * Function to push data on data stack
     * @param MemoryValue $value - value to push on data stack
     */
    protected function dataPush(MemoryValue $value) : void
    {
        $size = array_push($this->dataStack, $value);
        if ($size > $this->maxDataStackSize) {
            $this->maxDataStackSize = $size;
        }
    }

    /**
     * Function to pop data from data stack
     * @return MemoryValue - value popped from data stack
     * @throws RuntimeNoValueException - if there is no value on data stack
     */
    protected function dataPop() : MemoryValue
    {
        $value = array_pop($this->dataStack);
        if ($value === null) {
            throw new RuntimeNoValueException("No value on data stack");
        }
        return $value;
    }

    /**
     * Function to execute the program
     * @throws InvalidSourceStructure - if there is no temporary frame to push on frame stack
     */
    public function run(): int
    {
        while ($this->instruction_pointer <= $this->program->getLastOrder()) {
            if ($this->program->isOrderExists($this->instruction_pointer)) {
                $instruction = $this->program->getInstruction($this->instruction_pointer);
                $instruction->execute();
                if (self::$stop_execution) {
                    return self::$ret_code;
                }
                $this->executed_instruction_count++;
            }
            $this->instruction_pointer++;
        }
        return 0;
    }

    /**
     * Function to get the value of a variable
     * @param string $frame frame of the variable
     * @param string $name name of the variable
     * @return Variable - requested variable
     * @throws RuntimeUndefVarException - if the variable is not defined in the frame
     * @throws RuntimeMemoryFrameException - if the frame is not defined
     * @throws InternalErrorException - internal errors
     */
    protected function getVariable(string $frame, string $name): Variable
    {
        if ($frame === "GF") {
            return $this->globalFrame->getVariable($name);
        } elseif ($frame === "LF") {
            return $this->getLocalFrame()->getVariable($name);
        } elseif ($frame === "TF") {
            if (!($this->tempFrame instanceof MemoryFrame))
                throw new RuntimeMemoryFrameException("No temporary frame");
            return $this->tempFrame->getVariable($name);
        } else {
            throw new InternalErrorException("Unknown frame");
        }
    }

    /**
     * Function to get the value of a variable
     * @param VarArgument $var variable to get the value of
     * @return Variable - requested variable
     * @throws RuntimeMemoryFrameException - if the frame is not defined
     * @throws RuntimeUndefVarException - if the variable is not defined in the frame
     * @throws InternalErrorException - if the frame is unknown
     */
    protected function getFramedVariable(VarArgument $var): Variable
    {
        $frame = $var->getFrame();
        $name = $var->getName();
        return $this->getVariable($frame, $name);
    }

    /**
     * Function to get the value of a variable or literal argument
     * @param VarArgument|LiteralArgument $symb variable or literal argument to get the value of
     * @param bool $soft if true, no exception is thrown if the variable is not initialized
     * @return MemoryValue - value of the variable or literal argument
     * @throws RuntimeMemoryFrameException - if the frame is not defined
     * @throws RuntimeUndefVarException - if the variable is not defined in the frame
     * @throws InternalErrorException|RuntimeNoValueException - if the frame is unknown
     */
    protected function getSymbMemoryValue(VarArgument|LiteralArgument $symb, bool $soft = false): MemoryValue
    {
        if ($symb instanceof VarArgument) {
            $var = $this->getFramedVariable($symb);
            $type = $var->getType();
            return new MemoryValue($var->getValue($soft), $type);
        } else {
            return new MemoryValue($symb->getValue(), MemoryDataType::fromArgumentType($symb->getType()));
        }
    }

    /**
     * Function to get the local frame
     * @return MemoryFrame - local frame
     * @throws RuntimeMemoryFrameException - if there is no local frame
     */
    protected function getLocalFrame(): MemoryFrame
    {
        if (count($this->frameStack) === 0) {
            throw new RuntimeMemoryFrameException("No local frame");
        }
        return $this->frameStack[count($this->frameStack) - 1];
    }
}