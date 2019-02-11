<?php

declare(strict_types=1);

abstract class PHPStanCommandBuilder
{
    public function build(PHPStanTask $task): string
    {
        $commandParts = [];

        $commandParts[] = $this->buildExecutable($task);
        $commandParts[] = $this->buildCommand($task);

        $commandParts[] = $this->buildHelp($task);
        $commandParts[] = $this->buildQuiet($task);
        $commandParts[] = $this->buildVersion($task);
        $commandParts[] = $this->buildANSI($task);
        $commandParts[] = $this->buildNoANSI($task);
        $commandParts[] = $this->buildNoInteraction($task);
        $commandParts[] = $this->buildVerbose($task);

        $commandParts = array_filter($commandParts);
        
        return implode(' ', $commandParts);
    }

    private function buildExecutable(PHPStanTask $task): string
    {
        if (empty($task->getExecutable())) {
            throw new BuildException('executable not set');
        }
        return $task->getExecutable();
    }

    private function buildCommand(PHPStanTask $task): string
    {
        return $task->getCommand();
    }

    private function buildHelp(PHPStanTask $task): ?string
    {
        if ($task->isHelp()) {
            return '--help';
        }
        return null;
    }

    private function buildQuiet(PHPStanTask $task): ?string
    {
        if ($task->isQuiet()) {
            return '--quiet';
        }
        return null;
    }

    private function buildVersion(PHPStanTask $task): ?string
    {
        if ($task->isVersion()) {
            return '--version';
        }
        return null;
    }

    private function buildANSI(PHPStanTask $task): ?string
    {
        if ($task->isAnsi()) {
            return '--ansi';
        }
        return null;
    }

    private function buildNoANSI(PHPStanTask $task): ?string
    {
        if ($task->isNoAnsi()) {
            return '--no-ansi';
        }
        return null;
    }

    private function buildNoInteraction(PHPStanTask $task): ?string
    {
        if ($task->isNoInteraction()) {
            return '--no-interaction';
        }
        return null;
    }

    private function buildVerbose(PHPStanTask $task): ?string
    {
        if ($task->isVerbose()) {
            return '--verbose';
        }
        return null;
    }
}
