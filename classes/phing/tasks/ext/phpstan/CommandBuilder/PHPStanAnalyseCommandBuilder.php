<?php

declare(strict_types=1);

class PHPStanAnalyseCommandBuilder extends PHPStanCommandBuilder
{
    public function build(PHPStanTask $task): string
    {
        $commandParts = [];

        $commandParts[] = parent::build($task);

        $commandParts[] = $this->buildConfiguration($task);
        $commandParts[] = $this->buildLevel($task);
        $commandParts[] = $this->buildNoProgress($task);
        $commandParts[] = $this->buildDebug($task);
        $commandParts[] = $this->buildAutoloadFile($task);
        $commandParts[] = $this->buildErrorFormat($task);
        $commandParts[] = $this->buildMemoryLimit($task);
        $commandParts[] = $this->buildPaths($task);

        $commandParts = array_filter($commandParts);

        return implode(' ', $commandParts);
    }

    private function buildConfiguration(PHPStanTask $task): ?string
    {
        if (!empty($task->getConfiguration())) {
            return '--configuration=' . $task->getConfiguration();
        }
        return null;
    }

    private function buildLevel(PHPStanTask $task): ?string
    {
        if (!empty($task->getLevel())) {
            return '--level=' . $task->getLevel();
        }
        return null;
    }

    private function buildNoProgress(PHPStanTask $task): ?string
    {
        if ($task->isNoProgress()) {
            return '--no-progress';
        }
        return null;
    }

    private function buildDebug(PHPStanTask $task): ?string
    {
        if ($task->isDebug()) {
            return '--debug';
        }
        return null;
    }

    private function buildAutoloadFile(PHPStanTask $task): ?string
    {
        if (!empty($task->getAutoloadFile())) {
            return '--autoload-file=' . $task->getAutoloadFile();
        }
        return null;
    }

    private function buildErrorFormat(PHPStanTask $task): ?string
    {
        if (!empty($task->getErrorFormat())) {
            return '--error-format=' . $task->getErrorFormat();
        }
        return null;
    }

    private function buildMemoryLimit(PHPStanTask $task): ?string
    {
        if (!empty($task->getMemoryLimit())) {
            return '--memory-limit=' . $task->getMemoryLimit();
        }
        return null;
    }

    private function buildPaths(PHPStanTask $task): ?string
    {
        if (!empty($task->getPaths())) {
            return $task->getPaths();
        }
        return null;
    }
}
