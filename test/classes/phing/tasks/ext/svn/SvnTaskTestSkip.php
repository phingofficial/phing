<?php

declare(strict_types=1);

trait SvnTaskTestSkip
{

    public function markTestAsSkippedWhenSvnNotInstalled(): void
    {
        exec('svn help > /dev/null 2>&1', $output, $code);
        if ($code != 0) {
            $this->markTestSkipped('This test require svn to be installed');
        }
    }
}