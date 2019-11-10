<?php


class IniFileTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(PHING_TEST_BASE . "/etc/tasks/ext/inifile/inifile.xml");
        $this->inifiletestdir = PHING_TEST_BASE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'inifile';
        $this->executeTarget("setup");
    }

    public function tearDown(): void
    {
        $this->executeTarget("clean");
    }

    public function testNoSourceOrDestSet()
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('Neither source nor dest is set');

        $this->executeTarget('noSourceOrDestSet');
    }

    public function testNonexistingSourceOnly()
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('doesnotexist.ini does not exist');

        $this->executeTarget('nonexistingSourceOnly');
    }

    public function testNonexistingDestOnly()
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('doesnotexist.ini does not exist');
        $this->executeTarget('nonexistingDestOnly');
    }

    public function testNonexistingDestAndSource()
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('sourcedoesnotexist.ini does not exist');
        $this->executeTarget('nonexistingDestAndSource');
    }

    public function testExistingSource()
    {
        $fill = ["[test]\n", "; a comment\n", "foo=bar\n"];
        file_put_contents($this->inifiletestdir . "/source.ini", $fill);
        $this->executeTarget("existingSource");

        $this->assertInLogs('Read from ./../../../../tmp/inifile/source.ini');
        $this->assertInLogs('[test] foo set to qux');
        $this->assertInLogs('Wrote to ./../../../../tmp/inifile/destination.ini');
    }

    public function testExistingSourceWithVerbose()
    {
        $fill = ["[test]\n", "; a comment\n", "foo=bar\n"];
        file_put_contents($this->inifiletestdir . "/source.ini", $fill);
        $this->executeTarget("existingSourceWithVerbose");

        $this->assertInLogs('Read from ./../../../../tmp/inifile/source.ini');
        $this->assertInLogs('[test] foo set to qux', Project::MSG_INFO);
        $this->assertInLogs('Wrote to ./../../../../tmp/inifile/destination.ini');
    }

    public function testRemoveKeyFromSectionInSourceFile()
    {
        $fill = ["[test]\n", "; a comment\n", "foo=bar\n"];
        file_put_contents($this->inifiletestdir . "/source.ini", $fill);
        $this->executeTarget("removeKeyFromSectionInSourceFile");

        $this->assertInLogs('Read from ./../../../../tmp/inifile/source.ini');
        $this->assertInLogs('foo in section [test] has been removed.');
        $this->assertInLogs('Wrote to ./../../../../tmp/inifile/destination.ini');
        $result = file_get_contents($this->inifiletestdir . "/destination.ini");
        $this->assertEquals($result, "[test]\n; a comment\n");
    }

    public function testRemoveSectionFromSourceFile()
    {
        $fill = ["[test]\n", "; a comment\n", "foo=bar\n"];
        file_put_contents($this->inifiletestdir . "/source.ini", $fill);
        $this->executeTarget("removeSectionFromSourceFile");

        $this->assertInLogs('Read from ./../../../../tmp/inifile/source.ini');
        $this->assertInLogs('[test] has been removed.');
        $this->assertInLogs('Wrote to ./../../../../tmp/inifile/destination.ini');
        $result = file_get_contents($this->inifiletestdir . "/destination.ini");
        $this->assertEquals($result, "");
    }

    public function testDefaultValueInSecondSection()
    {
        $fill = ["[test]\n", "foo=bar\n", "[test2]\n", "foo=\n"];
        file_put_contents($this->inifiletestdir . "/source.ini", $fill);
        $this->executeTarget("defaultValueInSecondSection");
        $this->assertInLogs("Set property qux to value 'bar' read from key foo in section test");
        $this->assertInLogs("Set property qux to value 'notSet' read from key foo in section test2");
    }
}
