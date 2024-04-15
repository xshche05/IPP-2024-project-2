<?php

namespace IPP\Student\Program;

use IPP\Core\Exception\InternalErrorException;
use IPP\Student\Exceptions\InterpretSemanticException;
use IPP\Student\Exceptions\InvalidSourceStructure;
use IPP\Student\Instructions\Instruction;
use IPP\Student\Settings;

class Program
{
    /** @var array<int, Instruction> $instruction_flow program's instruction flow */
    private array $instruction_flow;
    /** @var array<string, int> $label_map map of labels to order */
    private array $label_map;
    private int $last_order;
    private int $start_order;
    public function __construct()
    {
        $this->instruction_flow = array();
        $this->label_map = array();
    }

    /**
     * @throws InvalidSourceStructure
     * @throws InterpretSemanticException
     * @throws InternalErrorException
     */
    public function addInstruction(Instruction $instruction, int $order): void
    {
        if ($order < 0)
            throw new InvalidSourceStructure("Order is negative");
        if (isset($this->instruction_flow[$order]))
            throw new InvalidSourceStructure("Order already exists");
        $this->instruction_flow[$order] = $instruction;
        if ($instruction->getOpcode() == 'LABEL') {
            $label = $instruction->getLabelArgument();
            if ($label === null)
                throw new InternalErrorException("Label argument is null");
            $this->addLabel($label->getValue(), $order);
        }
    }

    /**
     * @throws InterpretSemanticException
     */
    public function addLabel(string $label, int $order): void
    {
        if (isset($this->label_map[$label]))
            throw new InterpretSemanticException("Label already exists");
        $this->label_map[$label] = $order;
    }

    public function getInstruction(int $order): Instruction
    {
        return $this->instruction_flow[$order];
    }

    public function isOrderExists(int $order): bool
    {
        return isset($this->instruction_flow[$order]);
    }

    /**
     * @throws InterpretSemanticException
     */
    public function getLabelOrder(string $label): int
    {
        // get label order
        if (!isset($this->label_map[$label]))
            throw new InterpretSemanticException("Label not found");
        return $this->label_map[$label];
    }

    /**
     * Initialize the orders of the program before executing
     */
    public function initOrders(): void
    {
        $array_keys = array_keys($this->instruction_flow);
        if (count($array_keys) == 0)
        {
            $this->start_order = 0;
            $this->last_order = -1;
        }
        else {
            $this->start_order = min($array_keys);
            $this->last_order = max($array_keys);
        }
    }

    public function getStartOrder(): int
    {
        return $this->start_order;
    }

    public function getLastOrder(): int
    {
        return $this->last_order;
    }

    /**
     * Function to get the statistics of the program, get the most executed instruction with the least order
     */
    public function getHotStat(): int
    {
        $most_executed = -1;
        $least_order = $this->getLastOrder();
        foreach ($this->instruction_flow as $order => $instruction) {
            if ($instruction->getExecutionCount() > $most_executed) {
                $most_executed = $instruction->getExecutionCount();
                $least_order = $order;
            }
            if ($instruction->getExecutionCount() == $most_executed) {
                $least_order = min($least_order, $order);
            }
        }
        return $least_order;
    }

    /**
     * Function to get the statistics of the program, get number of instructions executed
     */
    public function getInstStat(): int
    {
        return Settings::getExecutor()->getExecutedInstructionCount();
    }


    /**
     * Function to get the statistics of the program, get max number of variables initialized in the same time
     */
    public function getVarsStat(): int
    {
        return Settings::getExecutor()->getMaxVarCount();
    }

    /**
     * Function to get the statistics of the program, get max number of values on in the same time
     */
    public function getStackStat(): int
    {
        return Settings::getExecutor()->getMaxDataStackSize();
    }
}