<?php

declare(strict_types=1);

interface SassTaskCompiler
{

    public function compile(string $inputFilePath, string $outputFilePath, bool $failOnError): void;
}