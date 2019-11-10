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
        $this->assertEquals(false, $this->patternset->hasPatterns());
    }

    public function testIfReferenceSetThenCreateIncludeThrowsException()
    {
        $project = new Project();
        $reference = new Reference($project);
        $this->patternset->setRefId($reference);

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('You must not specify nested elements when using refid');

        $this->patternset->createInclude();
    }

    public function testIfReferenceSetThenCreateExcludeThrowsException()
    {
        $project = new Project();
        $reference = new Reference($project);
        $this->patternset->setRefId($reference);

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('You must not specify nested elements when using refid');

        $this->patternset->createExclude();
    }

    public function testIfReferencesSetThenCreatExcludesFileThrowsException()
    {
        $project = new Project();
        $reference = new Reference($project);
        $this->patternset->setRefId($reference);

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('You must not specify nested elements when using refid');

        $this->patternset->createExcludesFile();
    }

    public function testIfReferencesSetThenCreatIncludesFileThrowsException()
    {
        $project = new Project();
        $reference = new Reference($project);
        $this->patternset->setRefId($reference);

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('You must not specify nested elements when using refid');

        $this->patternset->createIncludesFile();
    }

}
