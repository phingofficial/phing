<?php

declare(strict_types=1);

class PHPStanHelpCommandBuilder extends PHPStanCommandBuilder
{

    public function build(PHPStanTask $task): string
    {
        $commandParts = [];

        $commandParts[] = parent::build($task);

        $commandParts[] = $this->buildFormat($task);
        $commandParts[] = $this->buildRaw($task);
        $commandParts[] = $this->buildCommandName($task);

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

    private function buildCommandName(PHPStanTask $task): ?string
    {
        if (!empty($task->getCommandName())) {
            return $task->getCommandName();
        }
        return null;
    }
}
