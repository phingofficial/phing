<?php

declare(strict_types=1);

trait HgTaskTestSkipper
{

    public function markTestAsSkippedWhenHgNotInstalled(): void
    {
        exec('hg help', $output, $code);
        if ($code != 0)  {
            $this->markTestSkipped('This test require hg to be installed');
        }
    }
}