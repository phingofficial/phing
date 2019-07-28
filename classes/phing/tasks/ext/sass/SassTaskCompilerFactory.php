<?php

declare(strict_types=1);

class SassTaskCompilerFactory
{

    /**
     * @var FileSystem
     */
    private $fs;

    public function __construct(FileSystem $fs)
    {
        $this->fs = $fs;
    }

    public function prepareCompiler(SassTask $sassTask): SassTaskCompiler
    {
        $this->assertCompilerIsSet($sassTask);

        // If both are set to be used, prefer sass over scssphp.
        if ($sassTask->getUseSass() && $sassTask->getUseScssPhp()) {
            if ($this->fs->which($sassTask->getExecutable()) === false) {
                $this->assertScssPhpIsAvailable();
                return new ScssPhpCompiler(
                    $sassTask->getStyle(),
                    $sassTask->getEncoding(),
                    $sassTask->getLineNumbers(),
                    $sassTask->getPath()
                );
            }
        } elseif ($sassTask->getUseSass()) {
            $this->assertSassIsAvailable($sassTask);
        } elseif ($sassTask->getUseScssPhp()) {
            $this->assertScssPhpIsAvailable();
            return new ScssPhpCompiler(
                $sassTask->getStyle(),
                $sassTask->getEncoding(),
                $sassTask->getLineNumbers(),
                $sassTask->getPath()
            );
        }

        return new SassCompiler($sassTask->getExecutable(), $sassTask->getFlags());
    }

    private function assertCompilerIsSet(SassTask $sassTask): void
    {
        if (!$sassTask->getUseSass() && !$sassTask->getUseScssPhp()) {
            throw new BuildException("Neither sass nor scssphp are to be used.");
        }
    }

    private function assertScssPhpIsAvailable(): void
    {
        if (!$this->isScssPhpLoaded()) {
            $msg = sprintf(
                "Install scssphp/scssphp."
            );
            throw new BuildException($msg);
        }
    }

    private function assertSassIsAvailable(SassTask $sassTask): void
    {
        if ($this->fs->which($sassTask->getExecutable()) === false) {
            $msg = sprintf(
                "%s not found. Install sass.",
                $sassTask->getExecutable()
            );
            throw new BuildException($msg);
        }
    }

    private function isScssPhpLoaded(): bool
    {
        return class_exists('\ScssPhp\ScssPhp\Compiler');
    }
}
