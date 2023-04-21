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

use Phing\Io\FileSystem;
use Phing\Project;
use Phing\Task\Ext\ComposerTask;
use Phing\Type\CommandlineArgument;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Test class for the ComposerTask.
 *
 * @author  Nuno Costa <nuno@francodacosta.com>
 *
 * @internal
 */
class ComposerTaskTest extends TestCase
{
    /**
     * @var ComposerTask
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new ComposerTask();
        $this->object->setProject(new Project());
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
    }

    /**
     * @covers \ComposerTask::getCommand
     * @covers \ComposerTask::setCommand
     */
    public function testSetGetCommand(): void
    {
        $o = $this->object;
        $o->setCommand('foo');
        $this->assertEquals('foo', $o->getCommand());
    }

    /**
     * @covers \ComposerTask::getPhp
     * @covers \ComposerTask::setPhp
     */
    public function testSetGetPhp(): void
    {
        $o = $this->object;
        $o->setPhp('foo');
        $this->assertEquals('foo', $o->getPhp());
    }

    /**
     * @covers \ComposerTask::setComposer
     */
    public function testSetComposer(): void
    {
        $composer = 'foobar';
        $o = $this->object;
        $o->setComposer($composer);

        $prop = new ReflectionProperty(ComposerTask::class, 'composer');
        $prop->setAccessible(true);

        $this->assertEquals($composer, $prop->getValue($o));
    }

    /**
     * @covers \ComposerTask::getComposer
     */
    public function testGetComposerNotOnPath(): void
    {
        $composer = 'bar';
        $o = $this->object;

        $orgPath = getenv('PATH');

        $prop = new ReflectionProperty(ComposerTask::class, 'composer');
        $prop->setAccessible(true);
        $prop->setValue($o, $composer);

        putenv('PATH=/foo/bar');

        $pathComposer = $o->getComposer();

        putenv("PATH={$orgPath}");

        $this->assertEquals($composer, $pathComposer);
    }

    /**
     * @covers \ComposerTask::getComposer
     */
    public function testGetComposerFromPath(): void
    {
        $composer = 'foo';
        $o = $this->object;
        $o->setComposer($composer);

        $testPath = PHING_TEST_BASE . '/etc/tasks/ext/composer';
        $orgPath = getenv('PATH');

        $pathSeparator = FileSystem::getFileSystem()->getPathSeparator();
        putenv("PATH={$testPath}{$pathSeparator}{$orgPath}");

        $pathComposer = $o->getComposer();

        putenv("PATH={$orgPath}");

        // The composer found shouldn't be the one we set
        $this->assertNotEquals($composer, $pathComposer);
    }

    /**
     * @covers \ComposerTask::createArg
     */
    public function testCreateArg(): void
    {
        $o = $this->object;
        $arg = $o->createArg();
        $this->assertInstanceOf(CommandlineArgument::class, $arg);
    }

    public function testMultipleCalls(): void
    {
        $o = $this->object;
        $o->setPhp('php');
        $o->setCommand('install');
        $o->createArg()->setValue('--dry-run');
        $composer = $o->getComposer();
        $method = new ReflectionMethod(ComposerTask::class, 'prepareCommandLine');
        $method->setAccessible(true);
        $this->assertEquals('php ' . $composer . ' install --dry-run', (string) $method->invoke($o));
        $o->setCommand('update');
        $o->createArg()->setValue('--dev');
        $this->assertEquals('php ' . $composer . ' update --dev', (string) $method->invoke($o));
    }
}
