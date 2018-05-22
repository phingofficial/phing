<?php

declare(strict_types=1);

class PHPStanCommandBuilderFactory
{

    public function createBuilder(PHPStanTask $task): PHPStanCommandBuilder
    {
        switch ($task->getCommand()) {
            case 'analyse':
            case 'analyze':
                return new PHPStanAnalyseCommandBuilder();
            case 'list':
                return new PHPStanListCommandBuilder();
            case 'help':
                return new PHPStanHelpCommandBuilder();
        }
        throw new BuildException('unknown command');
    }
}
