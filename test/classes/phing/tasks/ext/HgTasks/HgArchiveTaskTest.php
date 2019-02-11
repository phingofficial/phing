<?php

class HgArchiveTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        mkdir(PHING_TEST_BASE . '/tmp/hgtest');
        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/tasks/ext/hg/HgArchiveTaskTest.xml'
        );
    }

    public function tearDown(): void
    {
        $this->rmdir(PHING_TEST_BASE . "/tmp/hgtest");
    }

    public function testDestinationNotSpecified()
    {
        $this->expectBuildExceptionContaining(
            'destinationNotSpecified',
            "destinationNotSpecified",
            "Destination must be set."
        );
    }
}
