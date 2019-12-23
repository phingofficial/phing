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

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Test class for the ComposerTask.
 *
 * @author  Nuno Costa <nuno@francodacosta.com>
 * @package phing.tasks.ext
 */
class ComposerTaskTest extends TestCase
{
    /**
     * @var ComposerTask
     */
    private $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->object = new ComposerTask();
        $this->object->setProject(new Project());
    }

    /**
     * @return void
     *
     * @covers ComposerTask::setCommand
     * @covers ComposerTask::getCommand
     */
    public function testSetGetCommand(): void
    {
        $o = $this->object;
        $o->setCommand('foo');
        self::assertEquals('foo', $o->getCommand());
    }

    /**
     * @return void
     *
     * @covers ComposerTask::getPhp
     * @covers ComposerTask::setPhp
     */
    public function testSetGetPhp(): void
    {
        $o = $this->object;
        $o->setPhp('foo');
        self::assertEquals('foo', $o->getPhp());
    }

    /**
     * @return void
     *
     * @throws IOException
     *
     * @covers ComposerTask::setComposer
     * @covers ComposerTask::getComposer
     */
    public function testSetGetComposer(): void
    {
        $composer = 'foo';
        $o        = $this->object;
        $o->setComposer($composer);
        $composerFile = new SplFileInfo($composer);
        if (false === $composerFile->isFile()) {
            $composer = FileSystem::getFileSystem()->which('composer');
        }
        self::assertEquals($composer, $o->getComposer());
    }

    /**
     * @return void
     *
     * @covers ComposerTask::createArg
     */
    public function testCreateArg(): void
    {
        $o   = $this->object;
        $arg = $o->createArg();
        self::assertTrue(get_class($arg) == 'CommandlineArgument');
    }

    /**
     * @return void
     *
     * @throws IOException
     * @throws ReflectionException
     */
    public function testMultipleCalls(): void
    {
        $o = $this->object;
        $o->setPhp('php');
        $o->setCommand('install');
        $o->createArg()->setValue('--dry-run');
        $composer = $o->getComposer();
        $method   = new ReflectionMethod('ComposerTask', 'prepareCommandLine');
        $method->setAccessible(true);
        self::assertEquals('php ' . $composer . ' install --dry-run', (string) $method->invoke($o));
        $o->setCommand('update');
        $o->createArg()->setValue('--dev');
        self::assertEquals('php ' . $composer . ' update --dev', (string) $method->invoke($o));
    }
}
