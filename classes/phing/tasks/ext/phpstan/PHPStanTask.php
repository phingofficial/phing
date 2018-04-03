<?php

declare(strict_types=1);

class PHPStanTask
{

    /** @var string */
    private $executable = 'phpstan';

    /** @var string */
    private $command = 'analyse';

    /** @var bool */
    private $help;

    /** @var bool */
    private $quiet;

    /** @var bool */
    private $version;

    /** @var bool */
    private $ansi;

    /** @var bool */
    private $noAnsi;

    /** @var bool */
    private $noInteraction;

    /** @var string */
    private $verbose;

    /** @var string */
    private $configuration;

    /** @var string */
    private $level;

    /** @var bool */
    private $noProgress;

    /** @var bool */
    private $debug;

    /** @var string */
    private $autoloadFile;

    /** @var string */
    private $errorFormat;

    /** @var string */
    private $memoryLimit;

    /** @var string */
    private $format;

    /** @var bool */
    private $raw;

    /** @var string */
    private $namespace;

    public function getExecutable(): string
    {
        return $this->executable;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function isHelp(): ?bool
    {
        return $this->help;
    }

    public function isQuiet(): ?bool
    {
        return $this->quiet;
    }

    public function isVersion(): ?bool
    {
        return $this->version;
    }

    public function isAnsi(): ?bool
    {
        return $this->ansi;
    }

    public function isNoAnsi(): ?bool
    {
        return $this->noAnsi;
    }

    public function isNoInteraction(): ?bool
    {
        return $this->noInteraction;
    }

    public function getVerbose(): ?string
    {
        return $this->verbose;
    }

    public function getConfiguration(): ?string
    {
        return $this->configuration;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function isNoProgress(): ?bool
    {
        return $this->noProgress;
    }

    public function isDebug(): ?bool
    {
        return $this->debug;
    }

    public function getAutoloadFile(): ?string
    {
        return $this->autoloadFile;
    }

    public function getErrorFormat(): ?string
    {
        return $this->errorFormat;
    }

    public function getMemoryLimit(): ?string
    {
        return $this->memoryLimit;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function isRaw(): ?bool
    {
        return $this->raw;
    }

    public function getNamespace(): ?string
    {
        return $this->namespace;
    }
}
