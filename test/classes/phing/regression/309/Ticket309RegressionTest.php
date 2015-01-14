<?php

use \Phing\Test\AbstractBuildFileTest;

class Ticket309RegressionTest extends AbstractBuildFileTest {

    /**
     * The project.basedir property denotes the project root directory.
     * This root directory can be set by the "basedir" attribute on the
     * <project> tag. It denotes the path to the project root, relative to
     * the buildfile.
     *
     * The default is ".", meaning that the build.xml file is locate in the
     * project root.
     *
     * This test uses several buildfiles that reference the /etc/regression/309
     * directory as their project root in various ways.
     */
    public function testPhingCallTask()
    {
        $testBasedir = PHING_TEST_BASE . "/etc/regression/309";

        foreach (array('basedir-dot.xml', 'basedir-default.xml', 'sub/basedir-dotdot.xml') as $buildfile) {
            $this->configureProject("$testBasedir/$buildfile");
            $this->executeTarget("main");
            $this->assertInLogs("project.basedir: $testBasedir");
        }
    }

} 
