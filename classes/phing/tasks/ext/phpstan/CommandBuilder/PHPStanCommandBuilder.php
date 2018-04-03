<?php

declare(strict_types=1);

interface PHPStanCommandBuilder
{

    public function build(PHPStanTask $task): string;
}
