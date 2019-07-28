<?php

declare(strict_types=1);

class PHPStanAnalyseCommandBuilder extends PHPStanCommandBuilder
{
    public function build(PHPStanTask $task): void
    {
        parent::build($task);

        $cmd = $task->getCommandline();

        if (!empty($task->getConfiguration())) {
            $cmd->createArgument()->setValue('--configuration=' . $task->getConfiguration());
        }
        if (!empty($task->getLevel())) {
            $cmd->createArgument()->setValue('--level=' . $task->getLevel());
        }
        if ($task->isNoProgress()) {
            $cmd->createArgument()->setValue('--no-progress');
        }
        if ($task->isDebug()) {
            $cmd->createArgument()->setValue('--debug');
        }
        if (!empty($task->getAutoloadFile())) {
            $cmd->createArgument()->setValue('--autoload-file=' . $task->getAutoloadFile());
        }
        if (!empty($task->getErrorFormat())) {
            $cmd->createArgument()->setValue('--error-format=' . $task->getErrorFormat());
        }
        if (!empty($task->getMemoryLimit())) {
            $cmd->createArgument()->setValue('--memory-limit=' . $task->getMemoryLimit());
        }
        if (!empty($task->getPaths())) {
            $cmd->createArgument()->setValue($task->getPaths());
        }
        if (count($task->getFileSets()) > 0) {
            foreach ($task->getFileSets() as $fs) {
                foreach ($fs as $file) {
                    $cmd->createArgument()->setValue($file);
                }
            }
        }
    }
}
