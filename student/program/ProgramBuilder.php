<?php

namespace IPP\Student\program;

use DOMDocument;
use DOMElement;
use IPP\Student\exceptions\InvalidSourceStructure;
use IPP\Student\instructions\InstructionBuilder;

class ProgramBuilder
{
    private Program $program;
    private DOMDocument $dom;

    public function __construct(DOMDocument $dom)
    {
        $this->program = new Program();
        $this->dom = $dom;
    }

    public function build(): Program
    {
        # get program node
        $program_node = $this->dom->documentElement;
        if (!($program_node instanceof DOMElement)) {
            throw new InvalidSourceStructure("No root element");
        }
        if ($program_node->nodeName != 'program') {
            throw new InvalidSourceStructure("Root element is not program");
        }
        $instructions_elements = $program_node->getElementsByTagName('instruction');
        foreach ($instructions_elements as $instruction_element) {
            $instruction = InstructionBuilder::build_instruction($instruction_element);
            $order = $instruction_element->getAttribute('order');
            $order = intval($order);
            $this->program->addInstruction($instruction, $order);
        }
        $this->program->initOrders();
        return $this->program;
    }
}