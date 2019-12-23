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

/**
 * Tests the Exec Task
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.tasks.system
 */
class ExecTaskTest extends BuildFileTest
{
    /**
     * Whether test is being run on windows
     *
     * @var bool
     */
    private $windows;

    /**
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     */
    protected function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/ExecTest.xml'
        );
        $this->windows = strtoupper(substr(PHP_OS, 0, 3)) == 'WIN';
    }

    /**
     * @param string $name
     *
     * @return Target
     *
     * @throws Exception
     */
    protected function getTargetByName(string $name): Target
    {
        foreach ($this->project->getTargets() as $target) {
            if ($target->getName() === $name) {
                return $target;
            }
        }
        throw new Exception(sprintf('Target "%s" not found', $name));
    }

    /**
     * @param Target $target
     * @param string $taskname
     * @param int    $pos
     *
     * @return mixed
     *
     * @throws ReflectionException
     * @throws Exception
     */
    protected function getTaskFromTarget(Target $target, string $taskname, int $pos = 0)
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

    /**
     * @param string $target
     * @param string $task
     *
     * @return mixed|string
     *
     * @throws ReflectionException
     * @throws Exception
     */
    protected function getConfiguredTask(string $target, string $task)
    {
        $target = $this->getTargetByName($target);
        $task   = $this->getTaskFromTarget($target, $task);
        $task->maybeConfigure();

        return $task;
    }

    /**
     * @param string      $property
     * @param mixed       $value
     * @param string|null $propertyName
     *
     * @return void
     *
     * @throws ReflectionException
     */
    protected function assertAttributeIsSetTo(string $property, $value, ?string $propertyName = null): void
    {
        $task = $this->getConfiguredTask(
            'testPropertySet' . ucfirst($property),
            'ExecTask'
        );

        if ($propertyName === null) {
            $propertyName = $property;
        }

        if ($task instanceof UnknownElement) {
            $task = $task->getRuntimeConfigurableWrapper()->getProxy();
        }

        $rprop = new ReflectionProperty('ExecTask', $propertyName);
        $rprop->setAccessible(true);
        $this->assertEquals($value, $rprop->getValue($task));
    }

    /**
     * @return void
     *
     * @throws ReflectionException
     */
    public function testPropertySetCommandline(): void
    {
        $this->assertAttributeIsSetTo('commandline', new Commandline("echo 'foo'"));
    }

    /**
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     * @throws ReflectionException
     */
    public function testPropertySetDir(): void
    {
        $this->assertAttributeIsSetTo(
            'dir',
            new PhingFile(
                realpath(__DIR__ . '/../../../../etc/tasks/system')
            )
        );
    }

    /**
     * @return void
     *
     * @throws ReflectionException
     */
    public function testPropertySetOs(): void
    {
        $this->assertAttributeIsSetTo('os', 'linux');
    }

    /**
     * @return void
     *
     * @throws ReflectionException
     */
    public function testPropertySetEscape(): void
    {
        $this->assertAttributeIsSetTo('escape', true);
    }

    /**
     * @return void
     *
     * @throws ReflectionException
     */
    public function testPropertySetLogoutput(): void
    {
        $this->assertAttributeIsSetTo('logoutput', true, 'logOutput');
    }

    /**
     * @return void
     *
     * @throws ReflectionException
     */
    public function testPropertySetPassthru(): void
    {
        $this->assertAttributeIsSetTo('passthru', true);
    }

    /**
     * @return void
     *
     * @throws ReflectionException
     */
    public function testPropertySetSpawn(): void
    {
        $this->assertAttributeIsSetTo('spawn', true);
    }

    /**
     * @return void
     *
     * @throws ReflectionException
     */
    public function testPropertySetReturnProperty(): void
    {
        $this->assertAttributeIsSetTo('returnProperty', 'retval');
    }

    /**
     * @return void
     *
     * @throws ReflectionException
     */
    public function testPropertySetOutputProperty(): void
    {
        $this->assertAttributeIsSetTo('outputProperty', 'outval');
    }

    /**
     * @return void
     *
     * @throws ReflectionException
     */
    public function testPropertySetCheckReturn(): void
    {
        $this->assertAttributeIsSetTo('checkreturn', true);
    }

    /**
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     * @throws ReflectionException
     */
    public function testPropertySetOutput(): void
    {
        $this->assertAttributeIsSetTo(
            'output',
            new PhingFile(
                realpath(__DIR__ . '/../../../../etc/tasks/system')
                . '/outputfilename'
            )
        );
    }

    /**
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     * @throws ReflectionException
     */
    public function testPropertySetError(): void
    {
        $this->assertAttributeIsSetTo(
            'error',
            new PhingFile(
                realpath(__DIR__ . '/../../../../etc/tasks/system')
                . '/errorfilename'
            )
        );
    }

    /**
     * @return void
     *
     * @throws ReflectionException
     */
    public function testPropertySetLevelError(): void
    {
        $this->assertAttributeIsSetTo('levelError', Project::MSG_ERR, 'logLevel');
    }

    /**
     * @return void
     *
     * @throws ReflectionException
     */
    public function testPropertySetLevelWarning(): void
    {
        $this->assertAttributeIsSetTo('levelWarning', Project::MSG_WARN, 'logLevel');
    }

    /**
     * @return void
     *
     * @throws ReflectionException
     */
    public function testPropertySetLevelInfo(): void
    {
        $this->assertAttributeIsSetTo('levelInfo', Project::MSG_INFO, 'logLevel');
    }

    /**
     * @return void
     *
     * @throws ReflectionException
     */
    public function testPropertySetLevelVerbose(): void
    {
        $this->assertAttributeIsSetTo('levelVerbose', Project::MSG_VERBOSE, 'logLevel');
    }

    /**
     * @return void
     *
     * @throws ReflectionException
     */
    public function testPropertySetLevelDebug(): void
    {
        $this->assertAttributeIsSetTo('levelDebug', Project::MSG_DEBUG, 'logLevel');
    }

    /**
     * @return void
     *
     * @throws ReflectionException
     */
    public function testPropertySetLevelUnknown(): void
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('Unknown log level "unknown"');

        $this->getConfiguredTask('testPropertySetLevelUnknown', 'ExecTask');
    }

    /**
     * @return void
     */
    public function testDoNotExecuteOnWrongOs(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('not found in the specified list of valid OSes: unknownos');
        $this->assertNotContains(
            'this should not be executed',
            $this->getOutput()
        );
    }

    /**
     * @return void
     */
    public function testDoNotExecuteOnWrongOsFamily(): void
    {
        $this->expectBuildException(__FUNCTION__, "Don't know how to detect os family 'unknownos'");
    }

    /**
     * @return void
     */
    public function testExecuteOnCorrectOs(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('this should be executed');
    }

    /**
     * @return void
     */
    public function testExecuteOnCorrectOsFamily(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('this should be executed');
    }

    /**
     * @return void
     */
    public function testFailOnNonExistingDir(): void
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessageRegExp('/' . preg_quote(str_replace('/', DIRECTORY_SEPARATOR, "'/this/dir/does/not/exist' does not exist"), '/') . '/');

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
     * @return void
     *
     * @requires OS ^(?:(?!Win).)*$
     */
    public function testChangeToDir(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('ExecTaskTest.php');
    }

    /**
     * @return void
     *
     * @throws IOException
     */
    public function testCheckreturnTrue(): void
    {
        if (FileSystem::getFileSystem()->which('true') === false) {
            $this->markTestSkipped("'true' not found.");
        }
        $this->executeTarget(__FUNCTION__);
        $this->assertTrue(true);
    }

    /**
     * @return void
     *
     * @throws IOException
     */
    public function testCheckreturnFalse(): void
    {
        if (FileSystem::getFileSystem()->which('false') === false) {
            $this->markTestSkipped("'false' not found.");
        }

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('exec returned: 1');

        $this->executeTarget(__FUNCTION__);
    }

    /**
     * @return void
     */
    public function testOutputProperty(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('The output property\'s value is: "foo"');
    }

    /**
     * @return void
     */
    public function testReturnProperty(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('The return property\'s value is: "1"');
    }

    /**
     * @return void
     */
    public function testEscape(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs($this->windows ? '"foo" "|" "cat"' : 'foo | cat');
    }

    /**
     * @return void
     */
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

    /**
     * @return void
     */
    public function testOutput(): void
    {
        $file = tempnam(FileUtils::getTempDir(), 'phing-exectest-');
        $this->project->setProperty('execTmpFile', $file);
        $this->executeTarget(__FUNCTION__);
        $this->assertStringContainsString('outfoo', file_get_contents($file));
        unlink($file);
    }

    /**
     * @return void
     */
    public function testError(): void
    {
        $file = tempnam(FileUtils::getTempDir(), 'phing-exectest-');
        $this->project->setProperty('execTmpFile', $file);
        $this->executeTarget(__FUNCTION__);
        $this->assertStringContainsString('errfoo', file_get_contents($file));
        unlink($file);
    }

    /**
     * @return void
     */
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

    /**
     * @return void
     */
    public function testNestedArg(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs($this->windows ? 'nested-arg "b  ar"' : 'nested-arg b  ar');
    }

    /**
     * @return void
     */
    public function testMissingExecutableAndCommand(): void
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('ExecTask: Please provide "executable"');

        $this->executeTarget(__FUNCTION__);
    }

    /**
     * Inspired by {@link http://www.phing.info/trac/ticket/833}
     *
     * @return void
     */
    public function testEscapedArg(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('outval', $this->windows ? 'abc$b3 SB' : 'abc$b3!SB');
    }

    /**
     * @return void
     */
    public function testEscapedArgWithoutWhitespace(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs($this->windows ? '"echo" "foo|bar" 2>&1' : '\'echo\' \'foo|bar\' 2>&1');
        $this->assertNotInLogs($this->windows ? 'echo " foo|bar " 2>&1' : 'echo \' foo|bar \' 2>&1');
    }

    /**
     * @return void
     */
    public function testNestedEnv(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertStringStartsWith('phploc', $this->getProject()->getProperty('envtest'));
    }
}
