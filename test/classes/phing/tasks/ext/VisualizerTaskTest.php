<?php

/**
 * @requires extension xsl
 */
class VisualizerTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(PHING_TEST_BASE . '/etc/tasks/ext/visualizer/VisualizerTaskTest.xml');
    }

    public function tearDown(): void
    {
        $this->executeTarget('clean');
    }

    /**
     * Test VisualizerTask with all the default values
     */
    public function testDefaultValues()
    {
        $this->executeTarget(__FUNCTION__);
        self::assertFileExists(PHING_TEST_BASE . '/etc/tasks/ext/visualizer/VisualizerTaskTest.png');
        $this->assertInLogs('VisualizerTaskTest.png');
        $this->assertFileSizeAtLeast(PHING_TEST_BASE . '/etc/tasks/ext/visualizer/VisualizerTaskTest.png', 80000);
    }

    /**
     * Testing different diagram formats: png, puml, svg and eps
     */
    public function testFormat()
    {
        $this->executeTarget(__FUNCTION__);
        self::assertFileExists(PHING_TEST_BASE . '/etc/tasks/ext/visualizer/VisualizerTaskTest.png');
        self::assertFileExists(PHING_TEST_BASE . '/etc/tasks/ext/visualizer/VisualizerTaskTest.puml');
        self::assertFileExists(PHING_TEST_BASE . '/etc/tasks/ext/visualizer/VisualizerTaskTest.svg');
        self::assertFileExists(PHING_TEST_BASE . '/etc/tasks/ext/visualizer/VisualizerTaskTest.eps');
        $this->assertInLogs('VisualizerTaskTest.png');
        $this->assertInLogs('VisualizerTaskTest.puml');
        $this->assertInLogs('VisualizerTaskTest.svg');
        $this->assertInLogs('VisualizerTaskTest.eps');
        $this->assertFileSizeAtLeast(PHING_TEST_BASE . '/etc/tasks/ext/visualizer/VisualizerTaskTest.png', 80000);
        $this->assertFileSizeAtLeast(PHING_TEST_BASE . '/etc/tasks/ext/visualizer/VisualizerTaskTest.puml', 1200);
        $this->assertFileSizeAtLeast(PHING_TEST_BASE . '/etc/tasks/ext/visualizer/VisualizerTaskTest.svg', 23000);
        $this->assertFileSizeAtLeast(PHING_TEST_BASE . '/etc/tasks/ext/visualizer/VisualizerTaskTest.eps', 100000);
    }

    /**
     * Test that an exception is raised when invalid format is used
     */
    public function testInvalidFormat()
    {
        $this->expectBuildException(__FUNCTION__, "'jpg' is not a valid format");
        $this->assertInLogs("'jpg' is not a valid format");
    }

    /**
     * Testing custom destination including filename
     */
    public function testDestinationFile()
    {
        $this->executeTarget(__FUNCTION__);
        self::assertFileExists(PHING_TEST_BASE . '/tmp/my-diagram.png');
        $this->assertInLogs('my-diagram.png');
        $this->assertFileSizeAtLeast(PHING_TEST_BASE . '/tmp/my-diagram.png', 80000);
    }

    /**
     * Testing custom destination without filename
     */
    public function testDestinationDirectory()
    {
        $this->executeTarget(__FUNCTION__);
        self::assertFileExists(PHING_TEST_BASE . '/tmp/VisualizerTaskTest.png');
        $this->assertInLogs('VisualizerTaskTest.png');
        $this->assertFileSizeAtLeast(PHING_TEST_BASE . '/tmp/VisualizerTaskTest.png', 80000);
    }

    /**
     * Testing that an exception is raised when an invalid directory is used as destination
     */
    public function testInvalidDestination()
    {
        $this->expectBuildException(__FUNCTION__, "Directory 'foo/bar/baz/' is invalid");
        $this->assertInLogs("Directory 'foo/bar/baz/' is invalid");
    }

    /**
     * Testing that a custom PlantUML server can be used
     */
    public function testCustomServer()
    {
        $this->executeTarget(__FUNCTION__);
        self::assertFileExists(PHING_TEST_BASE . '/etc/tasks/ext/visualizer/VisualizerTaskTest.png');
        $this->assertInLogs('VisualizerTaskTest.png');
        $this->assertFileSizeAtLeast(PHING_TEST_BASE . '/etc/tasks/ext/visualizer/VisualizerTaskTest.png', 80000);
    }

    /**
     * Testing that exception is raised when an invalid URL is used
     */
    public function testInvalidServer()
    {
        $this->expectBuildException(__FUNCTION__, 'Invalid PlantUml server');
        $this->assertInLogs('Invalid PlantUml server');
    }

}
