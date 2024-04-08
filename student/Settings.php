<?php

namespace IPP\Student;

use IPP\Core\Exception\InternalErrorException;
use IPP\Core\Exception\ParameterException;
use IPP\Core\ReturnCode;
use IPP\Student\Executor\Executor;

class Settings extends \IPP\Core\Settings
{
    /** @var string[] */
    protected array $longOptions = [
        "help",
        "source:",
        "input:",
        "stats:",
        ];
    private static string $stats_file = "";

    /** @var StatOption[] array of statistic to write */
    private static array $stats_conf = [];

    private static string $executor = Executor::class;

    /**
     * @throws ParameterException
     */
    public function processArgs(): void
    {
        parent::processArgs();
        $options = getopt("", $this->longOptions);
        $options = $options ?: [];
        if (isset($options['stats']) && is_string($options['stats'])) {
            self::$stats_file = $options['stats'];
        }
        $options = $_SERVER['argv'];
        array_shift($options);
        foreach ($options as $value) {
            // if starts with --source= or --input= or --stats= then remove it from array
            if (!(str_starts_with($value, "--source=")
                || str_starts_with($value, "--input=")
                || str_starts_with($value, "--stats=")
                || str_starts_with($value, "--help"))) {
                self::$stats_conf[] = new StatOption(StatOptionType::fromString($value), explode("=", $value)[1] ?? "");
            }
        }
        if (self::$stats_file === "" && count(self::$stats_conf) > 0) {
            throw new ParameterException("Stats file not specified");
        }
    }

    /**
     * @return StatOption[]
     */
    public static function getStatsConf(): array
    {
        return self::$stats_conf;
    }

    public static function getStatsFile(): string
    {
        return self::$stats_file;
    }

    public function getHelpString(): string
    {
        return "IPPcode24 Interpreter\n"
            ."Usage: php interpret.php [options...]\n"
            ."\n"
            ."Options:\n"
            ."--help            : displays this help and exits\n"
            ."--source=<file>   : source code in XML format\n"
            ."--input=<file>    : user inputs\n"
            ."--stats=<file>    : file for statistics\n"
            ."At least one of the options --source and --input has to be specified.\n"
            ."For the unspecified option, STDIN is used instead of a file.\n"
            ."\n"
            ."If --stats is specified, the following options can be used:\n"
            ."--insts           : total number of executed instructions\n"
            ."--vars            : maximum number of variables at the same time\n"
            ."--stack           : maximum size of data stack at the same time\n"
            ."--hot             : most executed instruction (is more write one with least order)\n"
            ."--eol             : write EOL\n"
            ."--print=STRING    : write STRING to file\n"
            ."\n"
            ."Return codes:\n"
            ."0-9 : correct execution\n"
            .ReturnCode::PARAMETER_ERROR
            ."  : invalid parameters\n"
            .ReturnCode::INPUT_FILE_ERROR
            ."  : input file error\n"
            .ReturnCode::OUTPUT_FILE_ERROR
            ."  : output file error\n"
            .ReturnCode::INVALID_XML_ERROR
            ."  : invalid source XML format\n"
            .ReturnCode::INVALID_SOURCE_STRUCTURE
            ."  : invalid source structure\n"
            .ReturnCode::SEMANTIC_ERROR
            ."  : semantic error\n"
            .ReturnCode::OPERAND_TYPE_ERROR
            ."  : runtime error - bad operand types\n"
            .ReturnCode::VARIABLE_ACCESS_ERROR
            ."  : runtime error - non-existent variable\n"
            .ReturnCode::FRAME_ACCESS_ERROR
            ."  : runtime error - non-existent frame\n"
            .ReturnCode::VALUE_ERROR
            ."  : runtime error - missing value\n"
            .ReturnCode::OPERAND_VALUE_ERROR
            ."  : runtime error - bad operand value\n"
            .ReturnCode::STRING_OPERATION_ERROR
            ."  : runtime error - bad string operation\n"
            .ReturnCode::INTEGRATION_ERROR
            ."  : integration error\n"
            .ReturnCode::INTERNAL_ERROR
            ."  : internal error\n"
            ;
    }

    public static function getExecutor(): Executor
    {
        /** @var Executor $executor*/
        $executor = self::$executor;
        return $executor::getInstance();
    }

    /**
     * @throws InternalErrorException
     */
    public static function getSchema(): string
    {
        $xml_scheme = __DIR__ . "/schema.xsd";
        $file = realpath($xml_scheme);
        if ($file === false) {
            throw new InternalErrorException("Cannot find schema file");
        }
        $file_data = file_get_contents($file);
        if ($file_data === false) {
            throw new InternalErrorException("Cannot read schema file");
        }
        return $file_data;
    }
}