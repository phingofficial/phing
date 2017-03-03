<?php

/**
 * The class that holds a comment representation.
 *
 * @package phing.filters
 */
class Comment
{

    /** The prefix for a line comment. */
    private $_value;

    /*
     * Sets the prefix for this type of line comment.
     *
     * @param string $value The prefix for a line comment of this type.
     *                      Must not be <code>null</code>.
     */
    /**
     * @param $value
     */
    public function setValue($value)
    {
        $this->_value = (string)$value;
    }

    /*
     * Returns the prefix for this type of line comment.
     *
     * @return string The prefix for this type of line comment.
    */
    public function getValue()
    {
        return $this->_value;
    }
}
