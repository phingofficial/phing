<?php

/**
 * Holds a contains element.
 *
 * @package phing.filters
 */
class Contains
{

    /**
     * @var string
     */
    private $_value;

    /**
     * Set 'contains' value.
     * @param string $contains
     */
    public function setValue($contains)
    {
        $this->_value = (string)$contains;
    }

    /**
     * Returns 'contains' value.
     * @return string
     */
    public function getValue()
    {
        return $this->_value;
    }
}
