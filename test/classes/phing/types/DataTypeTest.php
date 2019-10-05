<?php
class DataTypeTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        $this->datatype = new DataType();
    }

    /**
     * testTooManyAttributes
     *
     * @expectedException        BuildException
     * @expectedExceptionMessage You must not specify more than one attribute when using refid
     */
    public function testTooManyAttributes()
    {
        $ex = $this->datatype->tooManyAttributes();
        throw $ex;
    }

    /**
     * testNoChildrenAllowedException
     *
     * @expectedException        BuildException
     * @expectedExceptionMessage You must not specify nested elements when using refid
     */
    public function testNoChildrenAllowedException()
    {
        $ex = $this->datatype->noChildrenAllowed();
        throw $ex;
    }

    /**
     * testCircularReferenceException
     *
     * @expectedException        BuildException
     * @expectedExceptionMessage This data type contains a circular reference.
     */
    public function testCircularReferenceException()
    {
        $ex = $this->datatype->circularReference();
        throw $ex;
    }

    public function testToString()
    {
        $str = "";
        $str .= $this->datatype;
        $this->assertEquals("DataType", $str);
    }
}
