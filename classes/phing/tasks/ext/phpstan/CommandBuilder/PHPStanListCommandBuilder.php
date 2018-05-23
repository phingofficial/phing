<?php

declare(strict_types=1);

class PHPStanListCommandBuilder extends PHPStanCommandBuilder
{

    public function build(PHPStanTask $task): string
    {
        $commandParts = [];

        $commandParts[] = parent::build($task);

        $commandParts[] = $this->buildFormat($task);
        $commandParts[] = $this->buildRaw($task);
        $commandParts[] = $this->buildNamespace($task);

        $commandParts = array_filter($commandParts);

        return implode(' ', $commandParts);
    }

    private function buildFormat(PHPStanTask $task): ?string
    {
        if (!empty($task->getFormat())) {
            return '--format=' .  $task->getFormat();
        }
        return null;
    }

    private function buildRaw(PHPStanTask $task): ?string
    {
        if ($task->isRaw()) {
            return '--raw';
        }
        return null;
    }

    private function buildNamespace(PHPStanTask $task): ?string
    {
        if (!empty($task->getNamespace())) {
            return $task->getNamespace();
        }
        return null;
    }
}
