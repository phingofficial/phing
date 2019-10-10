<?php
class PatternSetTest extends \PHPUnit\Framework\TestCase
{
    private $patternset;

    protected function setUp(): void
    {
        $this->patternset = new PatternSet();
    }

    public function testBothEmpty()
    {
        $s = "" . $this->patternset;
        $this->assertEquals($s, "patternSet{ includes: empty  excludes: empty }");
        $this->assertEquals(false, $this->hasPatterns);
    }

    /**
     * @expectedException BuildException
     * @expectedExceptionMessage You must not specify nested elements when using refid
     */
    public function testIfReferenceSetThenCreateIncludeThrowsException()
    {
        $project = new Project();
        $reference = new Reference($project);
        $this->patternset->setRefId($reference);
        $this->patternset->createInclude();
    }
    /**
     * @expectedException BuildException
     * @expectedExceptionMessage You must not specify nested elements when using refid
     */
    public function testIfReferenceSetThenCreateExcludeThrowsException()
    {
        $project = new Project();
        $reference = new Reference($project);
        $this->patternset->setRefId($reference);
        $this->patternset->createExclude();
    }
}
