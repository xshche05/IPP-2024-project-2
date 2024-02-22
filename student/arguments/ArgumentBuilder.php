<?php

namespace IPP\Student\arguments;

use DOMElement;
use IPP\Core\Exception\InternalErrorException;
use IPP\Student\exceptions\InvalidSourceStructure;

class ArgumentBuilder
{
    /**
     * @param DOMElement $node XML node representing an argument
     * @return VarArgument|LiteralArgument|TypeArgument|LabelArgument - Argument object
     * @throws InternalErrorException
     */
    public static function buildArgument(DOMElement $node): VarArgument|LiteralArgument|TypeArgument|LabelArgument
    {
        $type = $node->getAttribute('type');
        $value = $node->nodeValue;
        if (!is_string($value))
            throw new InvalidSourceStructure("Invalid argument value or no value provided");
        if ($type === '') {
            throw new InvalidSourceStructure("No argument type provided");
        }
        return match ($type) {
            'var' => new VarArgument($value),
            'int' => new LiteralArgument(ArgumentType::INT_LITERAL, $value),
            'bool' => new LiteralArgument(ArgumentType::BOOL_LITERAL, $value),
            'string' => new LiteralArgument(ArgumentType::STRING_LITERAL, $value),
            'nil' => new LiteralArgument(ArgumentType::NIL_LITERAL, $value),
            'type' => new TypeArgument($value),
            'label' => new LabelArgument($value),
            'float' => new LiteralArgument(ArgumentType::FLOAT_LITERAL, $value),
            default => throw new InternalErrorException("Unknown argument type: $type"),
        };
    }

    public static function getArgOrder(string $arg_tag) : int | null
    {
        preg_match('/(\d+)$/', $arg_tag, $matches);
        return isset($matches[1]) ? (int)$matches[1] : null;
    }
}