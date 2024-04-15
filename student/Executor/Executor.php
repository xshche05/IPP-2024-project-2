<?php

namespace IPP\Student\Executor;

use IPP\Student\Executor\Traits\BasicExecutorT;
use IPP\Student\Executor\Traits\ExecutorBaseLogicT;
use IPP\Student\Executor\Traits\FloatExecutorT;
use IPP\Student\Executor\Traits\FloatStackExecutorT;
use IPP\Student\Executor\Traits\StackExecutorT;

class Executor
{
    use ExecutorBaseLogicT;
    use BasicExecutorT;
    use FloatExecutorT;
    use StackExecutorT;
    use FloatStackExecutorT;

    private static Executor $instance;
    public static function getInstance(): Executor
    {
        if (!isset(self::$instance)) {
            self::$instance = new Executor();
        }
        return self::$instance;
    }
}