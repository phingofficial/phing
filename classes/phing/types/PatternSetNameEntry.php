<?php

/**
 * "Internal" class for holding an include/exclude pattern.
 *
 * @package phing.types
 */
class PatternSetNameEntry
{

    /**
     * The pattern.
     *
     * @var string
     */
    private $name;

    /**
     * The if-condition property for this pattern to be applied.
     *
     * @var string
     */
    private $ifCond;

    /**
     * The unless-condition property for this pattern to be applied.
     *
     * @var string
     */
    private $unlessCond;

    /**
     * An alias for the setName() method.
     *
     * @see   setName()
     * @param string $pattern
     */
    public function setPattern($pattern)
    {
        $this->setName($pattern);
    }

    /**
     * Set the pattern text.
     *
     * @param string $name The pattern
     */
    public function setName($name)
    {
        $this->name = (string) $name;
    }

    /**
     * Sets an if-condition property for this pattern to match.
     *
     * @param string $cond
     */
    public function setIf($cond)
    {
        $this->ifCond = (string) $cond;
    }

    /**
     * Sets an unless-condition property for this pattern to match.
     *
     * @param string $cond
     */
    public function setUnless($cond)
    {
        $this->unlessCond = (string) $cond;
    }

    /**
     * Get the pattern text.
     *
     * @return string The pattern.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Evaluates the pattern.
     *
     * @param  Project $project
     * @return string The pattern or null if it is ruled out by a condition.
     */
    public function evalName(Project $project)
    {
        return $this->valid($project) ? $this->name : null;
    }

    /**
     * Checks whether pattern should be applied based on whether the if and unless
     * properties are set in project.
     *
     * @param  Project $project
     * @return boolean
     */
    public function valid(Project $project)
    {
        if ($this->ifCond !== null && $project->getProperty($this->ifCond) === null) {
            return false;
        } else {
            if ($this->unlessCond !== null && $project->getProperty($this->unlessCond) !== null) {
                return false;
            }
        }

        return true;
    }

    /**
     * Gets a string representation of this pattern.
     *
     * @return string
     */
    public function __toString()
    {
        $buf = $this->name;
        if (($this->ifCond !== null) || ($this->unlessCond !== null)) {
            $buf .= ":";
            $connector = "";

            if ($this->ifCond !== null) {
                $buf .= "if->{$this->ifCond}";
                $connector = ";";
            }
            if ($this->unlessCond !== null) {
                $buf .= "$connector unless->{$this->unlessCond}";
            }
        }

        return $buf;
    }
}
