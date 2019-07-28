<?php

declare(strict_types=1);

class PHPStanHelpCommandBuilder extends PHPStanCommandBuilder
{
    public function build(PHPStanTask $task): void
    {
        parent::build($task);

        $cmd = $task->getCommandline();

        if (!empty($task->getFormat())) {
            $cmd->createArgument()->setValue('--format=' .  $task->getFormat());
        }
        if ($task->isRaw()) {
            $cmd->createArgument()->setValue('--raw');
        }
        if (!empty($task->getCommandName())) {
            $cmd->createArgument()->setValue($task->getCommandName());
        }
    }
}
