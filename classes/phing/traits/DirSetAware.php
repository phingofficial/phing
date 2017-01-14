<?php

include_once 'phing/types/DirSet.php';

trait DirSetAware
{
    /** @var DirSet[] $dirsets */
    private $dirsets = [];

    public function addDirSet(DirSet $dirSet)
    {
        $this->dirsets[] = $dirSet;
    }

    /**
     * @return DirSet[]
     */
    public function getDirSets()
    {
        return $this->dirsets;
    }
}
