<?php

namespace IPP\Student;

use IPP\Core\AbstractInterpreter;
use IPP\Core\Exception\InternalErrorException;
use IPP\Student\Exceptions\InterpretSemanticException;
use IPP\Student\Exceptions\InvalidSourceStructure;
use IPP\Student\Program\ProgramBuilder;
use IPP\Student\Stat\StatOption;
use IPP\Student\Stat\StatOptionType;

class Interpreter extends AbstractInterpreter
{
    /**
     * @return int
     * @throws InterpretSemanticException
     * @throws InternalErrorException
     * @throws InvalidSourceStructure
     */
    public function execute(): int
    {
        mb_internal_encoding("UTF-8");
        $dom = $this->source->getDOMDocument(); // Get source code
        $scheme = Settings::getSchema();
        if (!$dom->schemaValidateSource($scheme)) { // Ippcode sml validation
            throw new InvalidSourceStructure("Invalid XML structure");
        }
        $programBuilder = new ProgramBuilder($dom);
        $program = $programBuilder->build();
        StatOption::setProgram($program); // Set program for stats getting
        $executor = Settings::getExecutor();
        $executor->init($program, $this->input, $this->stdout, $this->stderr);
        $ret_code = $executor->run(); // Run program
        $stats_file = Settings::getStatsFile(); // Fill stats file
        if ($stats_file !== "") {
            $file = fopen($stats_file, "w");
            if ($file === false) {
                throw new InternalErrorException("Cannot open file for writing stats");
            }
            $statToOut = Settings::getStatsConf();
            foreach ($statToOut as $stat) {
                if ($stat->getType() === StatOptionType::EOL) {
                    fwrite($file, "\n");
                } else {
                    fwrite($file, $stat->out() . "\n");
                }

            }
            fclose($file);
        }
        return $ret_code;
    }
}