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
 * Test class for the SymfonyConsoleTask.
 *
 * @author  Nuno Costa <nuno@francodacosta.com>
 * @package phing.tasks.ext
 */
class SymfonyConsoleTest extends TestCase
{
    /**
     * @var SymfonyConsoleTask
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
        $this->object = new SymfonyConsoleTask();
    }

    /**
     * @return void
     *
     * @covers SymfonyConsoleTask::setCommand
     * @covers SymfonyConsoleTask::getCommand
     */
    public function testSetGetCommand(): void
    {
        $o = $this->object;
        $o->setCommand('foo');
        $this->assertEquals('foo', $o->getCommand());
    }

    /**
     * @return void
     *
     * @covers SymfonyConsoleTask::setConsole
     * @covers SymfonyConsoleTask::getConsole
     */
    public function testSetGetConsole(): void
    {
        $o = $this->object;
        $o->setConsole('foo');
        $this->assertEquals('foo', $o->getConsole());
    }

    /**
     * @return void
     *
     * @covers SymfonyConsoleTask::setDebug
     * @covers SymfonyConsoleTask::getDebug
     */
    public function testSetGetDebug(): void
    {
        $o = $this->object;
        $o->setDebug(false);
        $this->assertEquals(false, $o->getDebug());
    }

    /**
     * @return void
     *
     * @covers SymfonyConsoleTask::setSilent
     * @covers SymfonyConsoleTask::getSilent
     */
    public function testSetGetSilent(): void
    {
        $o = $this->object;
        $o->setSilent(true);
        $this->assertTrue($o->getSilent());
    }

    /**
     * @return void
     *
     * @covers SymfonyConsoleTask::createArg
     */
    public function testCreateArg(): void
    {
        $o   = $this->object;
        $arg = $o->createArg();
        $this->assertTrue(get_class($arg) == 'Arg');
    }

    /**
     * @return void
     *
     * @covers SymfonyConsoleTask::getArgs
     */
    public function testGetArgs(): void
    {
        $o = $this->object;
        $o->createArg();
        $o->createArg();
        $o->createArg();
        $this->assertCount(3, $o->getArgs());
    }

    /**
     * @return void
     *
     * @covers SymfonyConsoleTask::getCmdString
     * @todo Implement testMain().
     */
    public function testGetCmdString(): void
    {
        $o   = $this->object;
        $arg = $o->createArg();
        $arg->setName('name');
        $arg->setValue('value');

        $o->setCommand('command');
        $o->setConsole('console');

        $ret = 'console command --name=value';

        $this->assertEquals($ret, $o->getCmdString());
    }

    /**
     * @return void
     *
     * @covers SymfonyConsoleTask::getCmdString
     */
    public function testNoDebugGetCmdString(): void
    {
        $o   = $this->object;
        $arg = $o->createArg();
        $arg->setName('name');
        $arg->setValue('value');

        $o->setCommand('command');
        $o->setConsole('console');
        $o->setDebug(false);

        $ret = 'console command --name=value --no-debug';

        $this->assertEquals($ret, $o->getCmdString());
    }

    /**
     * @return void
     *
     * @covers SymfonyConsoleTask::getCmdString
     */
    public function testNoDebugOnlyOnce(): void
    {
        $o   = $this->object;
        $arg = $o->createArg();
        $arg->setName('no-debug');

        $o->setCommand('command');
        $o->setConsole('console');
        $o->setDebug(false);

        $ret = 'console command --no-debug';

        $this->assertEquals($ret, $o->getCmdString());
    }
}
