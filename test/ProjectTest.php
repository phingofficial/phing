<?php

namespace Phing\Test;


use Phing\Project;

/**
 * @covers \Phing\Project
 */
class ProjectTest extends \PHPUnit_Framework_TestCase
{

    /** @var Project */
    protected $project;

    protected function setUp()
    {
        $this->project = new Project();
    }

    public function testSettingAndGettingSimpleProperty()
    {
        $this->project->setProperty('foo', 'bar');
        $this->assertEquals('bar', $this->project->getProperty('foo'));
    }

    public function testPropertyGetWillExpandPropertyValues()
    {
        $this->project->setProperty('foo', 'foo');
        $this->project->setProperty('test', '${foo}bar');

        $this->assertEquals('foobar', $this->project->getProperty('test'));

    }

    public function testGetProperties()
    {
        $this->project->setProperty('foo', 'foo');
        $this->project->setProperty('bar', 'bar');

        $this->assertEquals(array('foo' => 'foo', 'bar' => 'bar'), $this->project->getProperties());
    }

    public function testGetPropertiesWillExpandPropertyValues()
    {
        $this->project->setProperty('foo', 'foo');
        $this->project->setProperty('bar', '${foo}bar');

        $this->assertEquals(array('foo' => 'foo', 'bar' => 'foobar'), $this->project->getProperties());
    }

    public function testPropertyCanBeOverridden()
    {
        $this->project->setProperty('foo', 'foo');
        $this->project->setProperty('foo', 'bar');

        $this->assertEquals('bar', $this->project->getProperty('foo'));
    }

    public function testUserPropertyOverridesPlainProperty()
    {
        $this->project->setProperty('foo', 'foo');
        $this->project->setUserProperty('foo', 'bar');

        $this->assertEquals('bar', $this->project->getProperty('foo'));
    }

    public function testPlainPropertyDoesNotOverrideUserProperty()
    {
        $this->project->setUserProperty('foo', 'foo');
        $this->project->setProperty('foo', 'bar');

        $this->assertEquals('foo', $this->project->getProperty('foo'));
    }

    public function testSetNewProperty()
    {
        $this->project->setNewProperty('foo', 'foo');
        $this->assertEquals('foo', $this->project->getProperty('foo'));
    }

    public function testSetNewPropertyDoesNotChangeExistingValue()
    {
        $this->project->setProperty('foo', 'foo');
        $this->project->setNewProperty('foo', 'bar');

        $this->assertEquals('foo', $this->project->getProperty('foo'));
    }

}
