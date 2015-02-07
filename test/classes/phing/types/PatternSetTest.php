<?php

use Phing\Project;

class PatternSetInitializationTest extends PHPUnit_Framework_TestCase
{
    /** @var PatternSet */
    protected $ps;

    /** @var Project */
    protected $project;

    public function setUp()
    {
        $this->project = new Project();
        $this->project->init();
        $this->project->setProperty('pattern', 'foo');

        $this->ps = new PatternSet();
        $this->ps->setProject($this->project);
    }

    protected function assert($method)
    {
        $this->assertEquals(array('foo', 'bar'), $this->ps->$method($this->project));
    }

    public function testIncludesSetter()
    {
        $this->ps->setIncludes('foo, bar');
        $this->assert('getIncludePatterns');
    }

    public function testIncludeCreator()
    {
        $this->ps->createInclude()->setName('foo, bar');
        $this->assert('getIncludePatterns');
    }

    public function testExcludesSetter()
    {
        $this->ps->setExcludes('foo, bar');
        $this->assert('getExcludePatterns');
    }

    public function testExcludeCreator()
    {
        $this->ps->createExclude()->setName('foo, bar');
        $this->assert('getExcludePatterns');
    }

    public function testIncludesFileSetter()
    {
        $this->ps->setIncludesFile(PHING_TEST_BASE . '/etc/types/patternset/patterns');
        $this->assert('getIncludePatterns');
    }

    public function testExcludesFileSetter()
    {
        $this->ps->setExcludesFile(PHING_TEST_BASE . '/etc/types/patternset/patterns');
        $this->assert('getExcludePatterns');
    }

    public function testIfUnlessConditions()
    {
        $this->ps->createInclude()->setName('foo')->setIf('foo');
        $this->ps->createInclude()->setName('bar')->setUnless('bar');

        $this->assertEquals(array('bar'), $this->ps->getIncludePatterns($this->project));

        $this->project->setProperty('foo', true);

        $this->assertEquals(array('foo', 'bar'), $this->ps->getIncludePatterns($this->project));

        $this->project->setProperty('bar', true);

        $this->assertEquals(array('foo'), $this->ps->getIncludePatterns($this->project));
    }

}
