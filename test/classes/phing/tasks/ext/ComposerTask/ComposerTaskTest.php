<?php

/*
 *  $Id$
 *
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

require_once 'phing/tasks/ext/Composer/ComposerTask.php';
require_once 'phing/tasks/ext/Composer/Arg.php';
use Phing\Tasks\Ext\Composer\Arg;
/**
 * Test class for the SymfonyConsoleTask.
 *
 * @author  Nuno Costa <nuno@francodacosta.com>
 * @version $Id$
 * @package phing.tasks.ext
 */
class ComposerTaskTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SymfonyConsoleTask
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new ComposerTask;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers SymfonyConsoleTask::setCommand
     * @covers SymfonyConsoleTask::getCommand
     */
    public function testSetGetCommand()
    {
        $o = $this->object;
        $o->setCommand('foo');
        $this->assertEquals('foo', $o->getCommand());
    }
    /**
     * @covers SymfonyConsoleTask::getPhp
     * @covers SymfonyConsoleTask::setPhp
     */
    public function testSetGetPhp()
    {
        $o = $this->object;
        $o->setPhp('foo');
        $this->assertEquals('foo', $o->getPhp());
    }

    /**
     * @covers SymfonyConsoleTask::setComposer
     * @covers SymfonyConsoleTask::getComposer
     */
    public function testSetGetComposer()
    {
        $o = $this->object;
        $o->setComposer('foo');
        $this->assertEquals('foo', $o->getComposer());
    }

    /**
     * @covers SymfonyConsoleTask::createArg
     */
    public function testCreateArg()
    {
        $o = $this->object;
        $arg = $o->createArg();
        $this->assertTrue(get_class($arg) == 'Arg');
    }

    /**
     * @covers SymfonyConsoleTask::getArgs
     */
    public function testGetArgs()
    {
        $o = $this->object;
        $arg = $o->createArg();
        $arg = $o->createArg();
        $arg = $o->createArg();
        $this->assertTrue(count($o->getArgs()) == 3);
    }

    /**
     * @covers SymfonyConsoleTask::getCmdString
     * @todo Implement testMain().
     */
    public function testGetCmdString()
    {
        $o = $this->object;
        $arg = $o->createArg();
        $arg->setName('name');
        $arg->setValue('value');

        $o->setCommand('command');
        $o->setComposer('composer');
        $o->setPhp('php');

        $ret = "php composer command --name=value";

        $this->assertEquals($ret, $o->getCmdString());
    }
}
?>
