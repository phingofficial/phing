<?php
/**
 * Created by PhpStorm.
 * User: michiel
 * Date: 31-1-17
 * Time: 11:55
 */

/**
 * Class that holds an XSLT parameter.
 *
 * @package phing.filters
 */
class XsltParam
{
    private $name;

    /**
     * @var RegisterSlot|string
     */
    private $expr;

    /**
     * Sets param name.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get param name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets expression value (alias to the setExpression()) method.
     *
     * @param string $v
     * @see   setExpression()
     */
    public function setValue($v)
    {
        $this->setExpression($v);
    }

    /**
     * Gets expression value (alias to the getExpression()) method.
     *
     * @return string
     * @see    getExpression()
     */
    public function getValue()
    {
        return $this->getExpression();
    }

    /**
     * Sets expression value.
     *
     * @param string $expr
     */
    public function setExpression($expr)
    {
        $this->expr = $expr;
    }

    /**
     * Sets expression to dynamic register slot.
     *
     * @param RegisterSlot $expr
     */
    public function setListeningExpression(RegisterSlot $expr)
    {
        $this->expr = $expr;
    }

    /**
     * Returns expression value -- performs lookup if expr is registerslot.
     *
     * @return string
     */
    public function getExpression()
    {
        if ($this->expr instanceof RegisterSlot) {
            return $this->expr->getValue();
        }

        return $this->expr;
    }
}
