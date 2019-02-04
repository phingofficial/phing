<?php

declare(strict_types=1);

use Leafo\ScssPhp\Compiler;

class ScssPhpCompiler implements SassTaskCompiler
{

    /**
     * @var Compiler
     */
    private $scssCompiler;

    public function __construct(string $style, string $encoding, bool $lineNumbers, string $loadPath)
    {
        $this->scssCompiler = new Compiler();
        if ($style) {
            $ucStyle = ucfirst(strtolower($style));
            $this->scssCompiler->setFormatter('Leafo\\ScssPhp\\Formatter\\' . $ucStyle);
        }
        if ($encoding) {
            $this->scssCompiler->setEncoding($encoding);
        }
        if ($lineNumbers) {
            $this->scssCompiler->setLineNumberStyle(1);
        }
        if ($loadPath !== '') {
            $this->scssCompiler->setImportPaths(explode(PATH_SEPARATOR, $loadPath));
        }
    }

    public function compile(string $inputFilePath, string $outputFilePath, bool $failOnError): void
    {
        if (!$this->checkInputFile($inputFilePath, $failOnError)) {
            return;
        }

        $input = file_get_contents($inputFilePath);
        try {
            $out = $this->scssCompiler->compile($input);
            if ($out !== '') {
                $success = file_put_contents($outputFilePath, $out);
                if (!$success && $failOnError) {
                    throw new BuildException(
                        "Cannot write to output file " . var_export($outputFilePath, true),
                        Project::MSG_INFO
                    );
                }
            }
        } catch (Exception $ex) {
            if ($failOnError) {
                throw new BuildException($ex->getMessage());
            }
        }
    }

    private function checkInputFile($inputFilePath, $failOnError): bool
    {
        if (file_exists($inputFilePath) && is_readable($inputFilePath)) {
            return true;
        } elseif ($failOnError) {
            throw new BuildException(
                "Cannot read from input file " . var_export($inputFilePath, true),
                Project::MSG_INFO
            );
        }
        return false;
    }
}
