<?php

namespace IPP\Student\Instructions;

use DOMElement;
use IPP\Core\Exception\InternalErrorException;
use IPP\Student\Arguments\ArgumentBuilder;
use IPP\Student\Exceptions\InvalidSourceStructure;
use IPP\Student\Settings;

class InstructionBuilder
{
    /**
     * @throws InternalErrorException
     * @throws InvalidSourceStructure
     */
    public static function build_instruction(DOMElement $instructionNode): Instruction
    {
        $opcode = $instructionNode->getAttribute('opcode');
        $instruction = new Instruction($opcode);
        $argumentNodeList = $instructionNode->childNodes;
        foreach ($argumentNodeList as $argumentNode) {
            if ($argumentNode instanceof DOMElement) {
                $arg = ArgumentBuilder::buildArgument($argumentNode);
                $order = ArgumentBuilder::getArgOrder($argumentNode->tagName);
                if ($order === null) {
                    throw new InvalidSourceStructure("Invalid argument order");
                }
                $instruction->addArgument($arg, $order);
            }
        }
        $instruction->setExecutor(Settings::getExecutor(), $instruction->getOpcode());
        return $instruction;
    }
}