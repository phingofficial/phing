<?php

use Phing\Task;

class Dir_Subdir_SampleTask extends Task
{
    public function main()
    {
        $this->log('SampleTask executed!');
    }
}
