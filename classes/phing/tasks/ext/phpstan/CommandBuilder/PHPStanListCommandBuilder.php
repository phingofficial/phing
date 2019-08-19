<?php

declare(strict_types=1);

class PHPStanListCommandBuilder extends PHPStanCommandBuilder
{
    private const ARG_FORMAT = '--format=%s';
    private const ARG_RAW = '--raw';

    public function build(PHPStanTask $task): void
    {
        parent::build($task);

        $cmd = $task->getCommandline();

        $cmd->createArgument()->setValue(sprintf(self::ARG_FORMAT, $task->getFormat()));
        if ($task->isRaw()) {
            $cmd->createArgument()->setValue(self::ARG_RAW);
        }
        if (!empty($task->getNamespace())) {
            $cmd->createArgument()->setValue($task->getNamespace());
        }
    }
}
