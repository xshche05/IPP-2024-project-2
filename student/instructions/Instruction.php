<?php

namespace IPP\Student\instructions;

use IPP\Student\arguments\LabelArgument;
use IPP\Student\arguments\LiteralArgument;
use IPP\Student\arguments\TypeArgument;
use IPP\Student\arguments\VarArgument;
use IPP\Student\exceptions\InterpretSemanticException;
use IPP\Student\exceptions\InvalidSourceStructure;
use IPP\Student\executor\Executor;
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
     */
    public function getArguments(): array
    {
        # if array index is not continuous, throw exception
        $keys = array_keys($this->arguments);
        if (count($keys) !== 0 && $keys !== range(1, count($keys), 1))
            throw new InvalidSourceStructure("Arguments are not in continuous order");
        return $this->arguments;
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

    public function execute(): void
    {
        try {
            $reflection = new ReflectionMethod($this->executor_instance, $this->executor_method);
        } catch (ReflectionException $e) {
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