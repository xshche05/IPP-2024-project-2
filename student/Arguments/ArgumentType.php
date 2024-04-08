<?php

namespace IPP\Student\Arguments;

enum ArgumentType
{
    case VARIABLE;
    case NIL_LITERAL;
    case INT_LITERAL;
    case STRING_LITERAL;
    case BOOL_LITERAL;
    case FLOAT_LITERAL;
    case LABEL;
    case TYPE;
}
