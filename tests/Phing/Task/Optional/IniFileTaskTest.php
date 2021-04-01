<?php
/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

namespace Phing\Test\Task\Optional;

use Phing\Exception\BuildException;
use Phing\Project;
use Phing\Test\Support\BuildFileTest;

class IniFileTaskTest extends BuildFileTest
{
    /**
     * @var string
     */
    private $inifiletestdir;

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
