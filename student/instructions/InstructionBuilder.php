<?php

namespace IPP\Student\instructions;

use DOMElement;
use IPP\Core\Exception\InternalErrorException;
use IPP\Student\arguments\ArgumentBuilder;
use IPP\Student\exceptions\InvalidSourceStructure;
use IPP\Student\Settings;

class InstructionBuilder
{
    /**
     * @throws InternalErrorException
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
                $instruction->addArgument($arg, $order);
            }
        }
        $instruction->setExecutor(Settings::getExecutor(), $instruction->getOpcode());
        return $instruction;
    }
}