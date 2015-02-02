<?php

use Phing\Test\AbstractBuildFileTest;

class Ticket320RegressionTest extends AbstractBuildFileTest
{
    public function testPropertiesAreLateExpanded()
    {
        $this->configureProject(PHING_TEST_BASE . "/etc/regression/320/build.xml");
        $this->scanAssertionsInLogs("late-expansion");
    }
}
