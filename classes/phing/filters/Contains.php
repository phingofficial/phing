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
    private $value;

    /**
     * Set 'contains' value.
     *
     * @param string $contains
     */
    public function setValue($contains)
    {
        $this->value = (string) $contains;
    }

    /**
     * Returns 'contains' value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
