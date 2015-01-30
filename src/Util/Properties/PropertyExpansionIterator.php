<?php
namespace Phing\Util\Properties;

/**
 * A wrapper around an (arbitrary) iterator that will
 * use a PropertyExpansionHelper to expand all returned
 * values.
 * @author Matthias Pigulla <mp@webfactory.de>
 */
class PropertyExpansionIterator extends \IteratorIterator
{
    protected $helper;

    public function __construct(PropertyExpansionHelper $h, \Iterator $i)
    {
        parent::__construct($i);
        $this->helper = $h;
    }

    public function current()
    {
        return $this->helper->expand(parent::current());
    }
}
