<?php

namespace IPP\Student;

use IPP\Core\AbstractInterpreter;
use IPP\Core\Exception\InternalErrorException;
use IPP\Student\Exceptions\InvalidSourceStructure;
use IPP\Student\Program\ProgramBuilder;

class Interpreter extends AbstractInterpreter
{
    /**
     * @throws InvalidSourceStructure
     * @throws InternalErrorException
     */
    public function execute(): int
    {
        mb_internal_encoding("UTF-8");
        $dom = $this->source->getDOMDocument();
        $scheme = Settings::getSchema();
        if (!$dom->schemaValidateSource($scheme)) {
            throw new InvalidSourceStructure("Invalid XML structure");
        }
        $programBuilder = new ProgramBuilder($dom);
        $program = $programBuilder->build();
        StatOption::setProgram($program);
        $executor = Settings::getExecutor();
        $executor->init($program, $this->input, $this->stdout, $this->stderr);
        $ret_code = $executor->run();
        $stats_file = Settings::getStatsFile();
        if ($stats_file !== "") {
            $file = fopen($stats_file, "w");
            if ($file === false) {
                throw new InternalErrorException("Cannot open file for writing stats");
            }
            $statToOut = Settings::getStatsConf();
            foreach ($statToOut as $stat) {
                fwrite($file, $stat->out());
            }
            fclose($file);
        }
        return $ret_code;
    }
}


// todo: float read, print, debug instruction