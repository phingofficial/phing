<?php

declare(strict_types=1);

trait HgTaskTestSkip
{

    public function markTestAsSkippedWhenHgNotInstalled(): void
    {
        exec('hg help > /dev/null 2>&1', $output, $code);
        if ($code != 0) {
            $this->markTestSkipped('This test require hg to be installed');
        }
    }
}