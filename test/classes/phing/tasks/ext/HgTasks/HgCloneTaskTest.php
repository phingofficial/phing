<?php

class HgCloneTaskTest extends BuildFileTest
{
    use HgTaskTestSkip;

    public function setUp()
    {
        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/tasks/ext/hg/HgCloneTaskTest.xml'
        );
    }

    public function testWrongRepository()
    {
        $this->markTestAsSkippedWhenHgNotInstalled();

        $this->expectBuildExceptionContaining(
            'wrongRepository',
            'wrong repository',
            'abort'
        );
    }

    public function testNoRepositorySpecified()
    {
        $this->expectBuildExceptionContaining(
            'noRepository',
            'repository is not specified',
            '"repository" is a required parameter'
        );
    }

    public function testNoTargetPathSpecified()
    {
        $this->expectBuildExceptionContaining(
            'noTargetPath',
            'targetPath is not specified',
            '"targetPath" is a required parameter'
        );
    }
}
