<?php

namespace IPP\Student\Executor;

class Executor
{
    use ExecutorBaseLogic;
    use AbstractBasicExecutor;
    use AbstractFloatExecutor;
    use AbstractStackExecutor;
    use AbstractFloatStackExecutor;

    private static Executor $instance;
    public static function getInstance(): Executor
    {
        if (!isset(self::$instance)) {
            self::$instance = new Executor();
        }
        return self::$instance;
    }
}