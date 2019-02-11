<?php

declare(strict_types=1);

class SassCompiler implements SassTaskCompiler
{

    /**
     * @var string
     */
    private $executable;

    /**
     * @var string
     */
    private $flags;

    public function __construct(string $executable, string $flags)
    {
        $this->executable = $executable;
        $this->flags = $flags;
    }

    /**
     * @throws BuildException
     */
    public function compile(string $inputFilePath, string $outputFilePath, bool $failOnError): void
    {
        try {
            $output = $this->executeCommand($inputFilePath, $outputFilePath);
            if ($failOnError && $output[0] !== 0) {
                throw new BuildException(
                    "Result returned as not 0. Result: {$output[0]}",
                    Project::MSG_INFO
                );
            }
        } catch (Exception $e) {
            if ($failOnError) {
                throw new BuildException($e);
            }
        }
    }

    /**
     * Executes the command and returns return code and output.
     *
     * @param string $inputFile Input file
     * @param string $outputFile Output file
     *
     * @access protected
     * @throws BuildException
     * @return array array(return code, array with output)
     */
    private function executeCommand($inputFile, $outputFile)
    {
        // Prevent over-writing existing file.
        if ($inputFile == $outputFile) {
            throw new BuildException('Input file and output file are the same!');
        }

        $output = [];
        $return = null;

        $fullCommand = $this->executable;

        if (strlen($this->flags) > 0) {
            $fullCommand .= " {$this->flags}";
        }

        $fullCommand .= " {$inputFile} {$outputFile}";

        exec($fullCommand, $output, $return);

        return [$return, $output];
    }
}
