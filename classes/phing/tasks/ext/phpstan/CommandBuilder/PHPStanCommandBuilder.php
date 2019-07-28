<?php

declare(strict_types=1);

abstract class PHPStanCommandBuilder
{
    private const ARG_HELP = '--help';
    private const ARG_QUIET = '--quiet';
    private const ARG_VERSION = '--version';
    private const ARG_ANSI = '--ansi';
    private const ARG_NO_ANSI = '--no-ansi';
    private const ARG_NO_INTERACTION = '--no-interaction';
    private const ARG_VERBOSE = '--verbose';

    public function build(PHPStanTask $task): void
    {
        $this->validate($task);

        $cmd = $task->getCommandline();
        $cmd->setExecutable($task->getExecutable());
        $cmd->createArgument()->setValue($task->getCommand());
        if ($task->isHelp()) {
            $cmd->createArgument()->setValue(self::ARG_HELP);
        }
        if ($task->isQuiet()) {
            $cmd->createArgument()->setValue(self::ARG_QUIET);
        }
        if ($task->isVersion()) {
            $cmd->createArgument()->setValue(self::ARG_VERSION);
        }
        if ($task->isAnsi()) {
            $cmd->createArgument()->setValue(self::ARG_ANSI);
        }
        if ($task->isNoAnsi()) {
            $cmd->createArgument()->setValue(self::ARG_NO_ANSI);
        }
        if ($task->isNoInteraction()) {
            $cmd->createArgument()->setValue(self::ARG_NO_INTERACTION);
        }
        if ($task->isVerbose()) {
            $cmd->createArgument()->setValue(self::ARG_VERBOSE);
        }
    }

    private function validate(PHPStanTask $task): void
    {
        if (empty($task->getExecutable())) {
            throw new BuildException('executable not set');
        }
    }
}
