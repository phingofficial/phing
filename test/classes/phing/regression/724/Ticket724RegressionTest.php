<?php

use Phing\Test\AbstractBuildFileTest;

class Ticket724RegressionTest extends AbstractBuildFileTest
{
    public function testFilesetIncludesBasedirOnly()
    {
        $testBasedir = PHING_TEST_BASE . "/etc/regression/724";

        $this->configureProject("$testBasedir/build.xml");
        $this->executeTarget("test-include");
        $this->assertInLogs("-$testBasedir/-");
        $this->assertNotInLogs("-$testBasedir/foo");
    }

    public function testOnlyBasedirIsExcluded()
    {
        $testBasedir = PHING_TEST_BASE . "/etc/regression/724";

        $this->configureProject("$testBasedir/build.xml");
        $this->executeTarget("test-exclude");
        $this->assertNotInLogs("-$testBasedir/-");
        $this->assertInLogs("-$testBasedir/foo-");
        $this->assertInLogs("-$testBasedir/foo/file-");
        $this->assertInLogs("-$testBasedir/foo/bar-");
        $this->assertInLogs("-$testBasedir/foo/bar/file-");
    }
}
