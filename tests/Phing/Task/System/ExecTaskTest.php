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

namespace Phing\Test\Task\System;

use Exception;
use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Io\FileSystem;
use Phing\Io\FileUtils;
use Phing\Project;
use Phing\Target;
use Phing\Task;
use Phing\Task\System\ExecTask;
use Phing\Test\Support\BuildFileTest;
use Phing\Type\Commandline;
use Phing\UnknownElement;
use ReflectionProperty;

/**
 * Tests the Exec Task.
 *
 * @author  Michiel Rook <mrook@php.net>
 *
 * @internal
 */
class ExecTaskTest extends BuildFileTest
{
    /**
     * Whether test is being run on windows.
     *
     * @var bool
     */
    protected $windows;

    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/ExecTest.xml'
        );
        $this->windows = 'WIN' === strtoupper(substr(PHP_OS, 0, 3));
    }

    public function testPropertySetCommandline(): void
    {
        $this->assertAttributeIsSetTo('commandline', new Commandline("echo 'foo'"));
    }

    public function testPropertySetDir(): void
    {
        $this->assertAttributeIsSetTo(
            'dir',
            new File(
                realpath(__DIR__ . '/../../../etc/tasks/system')
            )
        );
    }

    public function testPropertySetOs(): void
    {
        $this->assertAttributeIsSetTo('os', 'linux');
    }

    public function testPropertySetEscape(): void
    {
        $this->assertAttributeIsSetTo('escape', true);
    }

    public function testPropertySetLogoutput(): void
    {
        $this->assertAttributeIsSetTo('logoutput', true, 'logOutput');
    }

    public function testPropertySetPassthru(): void
    {
        $this->assertAttributeIsSetTo('passthru', true);
    }

    public function testPropertySetSpawn(): void
    {
        $this->assertAttributeIsSetTo('spawn', true);
    }

    public function testPropertySetReturnProperty(): void
    {
        $this->assertAttributeIsSetTo('returnProperty', 'retval');
    }

    public function testPropertySetOutputProperty(): void
    {
        $this->assertAttributeIsSetTo('outputProperty', 'outval');
    }

    public function testPropertySetCheckReturn(): void
    {
        $this->assertAttributeIsSetTo('checkreturn', true);
    }

    public function testPropertySetOutput(): void
    {
        $this->assertAttributeIsSetTo(
            'output',
            new File(
                realpath(__DIR__ . '/../../../etc/tasks/system')
                . '/outputfilename'
            )
        );
    }

    public function testPropertySetError(): void
    {
        $this->assertAttributeIsSetTo(
            'error',
            new File(
                realpath(__DIR__ . '/../../../etc/tasks/system')
                . '/errorfilename'
            )
        );
    }

    public function testPropertySetLevelError(): void
    {
        $this->assertAttributeIsSetTo('levelError', Project::MSG_ERR, 'logLevel');
    }

    public function testPropertySetLevelWarning(): void
    {
        $this->assertAttributeIsSetTo('levelWarning', Project::MSG_WARN, 'logLevel');
    }

    public function testPropertySetLevelInfo(): void
    {
        $this->assertAttributeIsSetTo('levelInfo', Project::MSG_INFO, 'logLevel');
    }

    public function testPropertySetLevelVerbose(): void
    {
        $this->assertAttributeIsSetTo('levelVerbose', Project::MSG_VERBOSE, 'logLevel');
    }

    public function testPropertySetLevelDebug(): void
    {
        $this->assertAttributeIsSetTo('levelDebug', Project::MSG_DEBUG, 'logLevel');
    }

    public function testPropertySetLevelUnknown(): void
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('Unknown log level "unknown"');

        $this->getConfiguredTask('testPropertySetLevelUnknown', 'ExecTask');
    }

    public function testDoNotExecuteOnWrongOs(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('not found in the specified list of valid OSes: unknownos');
        $this->assertStringNotContainsString(
            'this should not be executed',
            $this->getOutput()
        );
    }

    public function testDoNotExecuteOnWrongOsFamily(): void
    {
        $this->expectBuildException(__FUNCTION__, "Don't know how to detect os family 'unknownos'");
    }

    public function testExecuteOnCorrectOs(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('this should be executed');
    }

    public function testExecuteOnCorrectOsFamily(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('this should be executed');
    }

    public function testFailOnNonExistingDir(): void
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote(str_replace('/', DIRECTORY_SEPARATOR, "'/this/dir/does/not/exist' does not exist"), '/') . '/');

//        try {
//            $this->executeTarget(__FUNCTION__);
//            $this->fail('Expected BuildException was not thrown');
//        } catch (BuildException $e) {
//            $this->assertContains(
//                str_replace('/', DIRECTORY_SEPARATOR, "'/this/dir/does/not/exist' does not exist"),
//                $e->getMessage()
//            );
//        }

        $this->executeTarget(__FUNCTION__);
    }

    /**
     * @requires OS Linux|Darwin
     */
    public function testChangeToDir(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('ExecTaskTest.php');
    }

    public function testCheckreturnTrue(): void
    {
        if (false === FileSystem::getFileSystem()->which('true')) {
            $this->markTestSkipped("'true' not found.");
        }
        $this->executeTarget(__FUNCTION__);
        $this->assertTrue(true);
    }

    public function testCheckreturnFalse(): void
    {
        if (false === FileSystem::getFileSystem()->which('false')) {
            $this->markTestSkipped("'false' not found.");
        }

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('exec returned: 1');

        $this->executeTarget(__FUNCTION__);
    }

    public function testOutputProperty(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('The output property\'s value is: "foo"');
    }

    public function testReturnProperty(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('The return property\'s value is: "1"');
    }

    public function testEscape(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs($this->windows ? '"foo" "|" "cat"' : 'foo | cat');
    }

    public function testPassthru(): void
    {
        ob_start();
        $this->executeTarget(__FUNCTION__);
        $out = ob_get_clean();
        $this->assertEquals('foo', rtrim($out, " \r\n"));
        //foo should not be in logs, except for the logged command
        $this->assertInLogs('echo foo');
        $this->assertNotContains('foo', $this->logBuffer);
    }

    public function testOutput(): void
    {
        $file = tempnam(FileUtils::getTempDir(), 'phing-exectest-');
        $this->project->setProperty('execTmpFile', $file);
        $this->executeTarget(__FUNCTION__);
        $this->assertStringContainsString('outfoo', file_get_contents($file));
        unlink($file);
    }

    public function testError(): void
    {
        $file = tempnam(FileUtils::getTempDir(), 'phing-exectest-');
        $this->project->setProperty('execTmpFile', $file);
        $this->executeTarget(__FUNCTION__);
        $this->assertStringContainsString('errfoo', file_get_contents($file));
        unlink($file);
    }

    public function testSpawn(): void
    {
        $start = time();
        $this->executeTarget(__FUNCTION__);
        $end = time();
        $this->assertLessThan(
            4,
            $end - $start,
            'Time between start and end should be lower than 4 seconds'
            . ' - otherwise it looks as spawning did not work'
        );
    }

    public function testNestedArg(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs($this->windows ? 'nested-arg "b  ar"' : 'nested-arg b  ar');
    }

    public function testMissingExecutableAndCommand(): void
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('ExecTask: Please provide "executable"');

        $this->executeTarget(__FUNCTION__);
    }

    /**
     * Inspired by {@link http://www.phing.info/trac/ticket/833}.
     */
    public function testEscapedArg(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('outval', 'abc$b3!SB');
    }

    public function testEscapedArgWithoutWhitespace(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs($this->windows ? '"echo" "foo|bar" 2>&1' : '\'echo\' \'foo|bar\' 2>&1');
        $this->assertNotInLogs($this->windows ? 'echo " foo|bar " 2>&1' : 'echo \' foo|bar \' 2>&1');
    }

    public function testNestedEnv(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertStringStartsWith('phploc', $this->getProject()->getProperty('envtest'));
    }

    public function testEnvVar(): void
    {
        $this->expectPropertySet(__FUNCTION__, 'hello', 'world');
    }

    public function testMultipleEnvVars(): void
    {
        $this->expectPropertySet(__FUNCTION__, 'outputProperty', 'hello world');
    }

    protected function getTargetByName($name): Target
    {
        foreach ($this->project->getTargets() as $target) {
            if ($target->getName() == $name) {
                return $target;
            }
        }

        throw new Exception(sprintf('Target "%s" not found', $name));
    }

    protected function getTaskFromTarget($target, $taskname, $pos = 0): Task
    {
        $rchildren = new ReflectionProperty(get_class($target), 'children');
        $rchildren->setAccessible(true);
        $n = -1;
        foreach ($rchildren->getValue($target) as $child) {
            if ($child instanceof Task && ++$n == $pos) {
                return $child;
            }
        }

        throw new Exception(
            sprintf('%s #%d not found in task', $taskname, $pos)
        );
    }

    protected function getConfiguredTask($target, $task, $pos = 0): Task
    {
        $target = $this->getTargetByName($target);
        $task = $this->getTaskFromTarget($target, $task);
        $task->maybeConfigure();

        return $task;
    }

    protected function assertAttributeIsSetTo($property, $value, $propertyName = null): void
    {
        $task = $this->getConfiguredTask(
            'testPropertySet' . ucfirst($property),
            'ExecTask'
        );

        if (null === $propertyName) {
            $propertyName = $property;
        }

        if ($task instanceof UnknownElement) {
            $task = $task->getRuntimeConfigurableWrapper()->getProxy();
        }

        $rprop = new ReflectionProperty(ExecTask::class, $propertyName);
        $rprop->setAccessible(true);
        $this->assertEquals($value, $rprop->getValue($task));
    }

    public function testSuggestion()
    {
        $this->executeTarget(__FUNCTION__);
        $hint = 'Consider using HttpRequestTask https://www.phing.info/guide/chunkhtml/HttpRequestTask.html';
        $this->assertInLogs($hint, Project::MSG_VERBOSE, 'ExecTask is not displaying suggestion');
    }
}
