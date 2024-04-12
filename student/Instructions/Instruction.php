<?php

namespace IPP\Student\Instructions;

use IPP\Student\Arguments\LabelArgument;
use IPP\Student\Arguments\LiteralArgument;
use IPP\Student\Arguments\TypeArgument;
use IPP\Student\Arguments\VarArgument;
use IPP\Student\Exceptions\InvalidSourceStructure;
use IPP\Student\Executor\Executor;
use ReflectionException;
use ReflectionMethod;
use TypeError;

class Instruction
{
    private string $opcode;
    /** @var array<VarArgument|LabelArgument|LiteralArgument|TypeArgument> */
    private array $arguments;
    private Executor $executor_instance;
    private string $executor_method;
    private int $execution_count;

    public function __construct(string $opcode)
    {
        $this->opcode = $opcode;
        $this->arguments = [];
        $this->execution_count = 0;
    }

    /**
     * @throws InvalidSourceStructure
     */
    public function addArgument(LabelArgument|LiteralArgument|TypeArgument|VarArgument $argument, int $order): void
    {
        if (isset($this->arguments[$order]))
            throw new InvalidSourceStructure("Argument order already exists");
        $this->arguments[$order] = $argument;
        ksort($this->arguments, SORT_NUMERIC); # sort by key (order)
    }

    public function getOpcode(): string
    {
        return $this->opcode;
    }

    public function getLabelArgument(int $order = 1): LabelArgument|null
    {
        return $this->arguments[$order] instanceof LabelArgument ? $this->arguments[$order] : null;
    }

    /**
     * @return array<LabelArgument|LiteralArgument|TypeArgument|VarArgument>
     * @throws InvalidSourceStructure
     */
    public function getArguments(): array
    {
        $keys = array_keys($this->arguments);
        if (in_array(3, $keys))
        {
            if (count($keys) < 3 || !in_array(2, $keys))
                throw new InvalidSourceStructure("Three args expected");
        }
        if (in_array(2, $keys))
        {
            if (count($keys) < 2 || !in_array(1, $keys))
                throw new InvalidSourceStructure("Two args expected");
        }
        $ret = $this->arguments;
        ksort($ret, SORT_NUMERIC);
        return $ret;
    }

    public function setExecutor(Executor $executor, string $method): void
    {
        $this->executor_instance = $executor;
        $this->executor_method = $method;
    }

    public function getExecutionCount(): int
    {
        return $this->execution_count;
    }

    /**
     * @throws InvalidSourceStructure
     */
    public function execute(): void
    {
        try {
            $reflection = new ReflectionMethod($this->executor_instance, $this->executor_method);
        } catch (ReflectionException) {
            throw new InvalidSourceStructure("Unsupported instruction opcode " . $this->opcode);
        }
        if ($reflection->getNumberOfRequiredParameters() !== count($this->getArguments()))
            throw new InvalidSourceStructure("Invalid number of arguments for instruction opcode");
        try {
            /** @throws TypeError */
            $reflection->invokeArgs($this->executor_instance, $this->getArguments());
        } catch (TypeError $e) {
            throw new InvalidSourceStructure("Invalid argument type for instruction opcode " . $this->opcode . "\n" . $e->getMessage());
        }
        $this->execution_count++;
    }
}