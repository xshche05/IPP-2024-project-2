<?php

namespace IPP\Student\variables;

class TypePair
{
    private MemoryDataType $type1;
    private MemoryDataType $type2;

    public function __construct(MemoryDataType $type1, MemoryDataType $type2)
    {
        $this->type1 = $type1;
        $this->type2 = $type2;
    }

    public function __equals(self $other): bool
    {
        return $this->type1 == $other->type1 || $this->type2 == $other->type2;
    }
}