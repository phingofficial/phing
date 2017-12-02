<?php

/**
 * Class to keep track of the position of an Argument.
 *
 * <p>This class is there to support the srcfile and targetfile
 * elements of &lt;execon&gt; and &lt;transform&gt; - don't know
 * whether there might be additional use cases.</p> --SB
 *
 * @package phing.types
 */
class CommandlineMarker
{
    private $position;
    private $realPos = -1;
    private $outer;

    /**
     * @param Commandline $outer
     * @param $position
     */
    public function __construct(Commandline $outer, $position)
    {
        $this->outer = $outer;
        $this->position = $position;
    }

    /**
     * Return the number of arguments that preceded this marker.
     *
     * <p>The name of the executable - if set - is counted as the
     * very first argument.</p>
     */
    public function getPosition()
    {
        if ($this->realPos == -1) {
            $this->realPos = ($this->outer->executable === null ? 0 : 1);
            for ($i = 0; $i < $this->position; $i++) {
                $arg = $this->outer->arguments[$i];
                $this->realPos += count($arg->getParts());
            }
        }

        return $this->realPos;
    }
}
