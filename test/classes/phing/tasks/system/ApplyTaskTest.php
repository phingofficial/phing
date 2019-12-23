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
 * Tests the Apply Task
 *
 * @author  Utsav Handa <handautsav at hotmail dot com>
 * @package phing.tasks.system
 */
class ApplyTaskTest extends BuildFileTest
{
    /**
     * Whether test is being run on windows
     *
     * @var bool
     */
    private $windows;

    /**
     * Setup the test
     *
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     * @throws ConfigurationException
     */
    protected function setUp(): void
    {
        // Tests definitions
        $this->configureProject(PHING_TEST_BASE . '/etc/tasks/system/ApplyTest.xml');

        // Identifying the running environment
        $this->windows = strtoupper(substr(PHP_OS, 0, 3)) == 'WIN';
    }

    /************************************** T E S T S *********************************/

    /**
     * Tests the OS configuration setting
     *
     * @return void
     *
     * @throws ReflectionException
     */
    public function testPropertySetOs(): void
    {
        $this->assertAttributeIsSetTo('os', 'linux');
    }

    /**
     * Tests the dir configuration setting
     *
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     * @throws ReflectionException
     * @throws Exception
     */
    public function testPropertySetDir(): void
    {
        $this->assertAttributeIsSetTo('dir', new PhingFile($this->project->getProperty('php.tmpdir')));
    }

    /**
     * Tests the escape configuration setting
     *
     * @return void
     *
     * @throws ReflectionException
     */
    public function testPropertySetEscape(): void
    {
        $this->assertAttributeIsSetTo('escape', true);
    }

    /**
     * Tests the pass-thru configuration setting
     *
     * @return void
     *
     * @throws ReflectionException
     */
    public function testPropertySetPassthru(): void
    {
        $this->assertAttributeIsSetTo('passthru', true);
    }

    /**
     * Tests the spawn configuration setting
     *
     * @return void
     *
     * @throws ReflectionException
     */
    public function testPropertySetSpawn(): void
    {
        $this->assertAttributeIsSetTo('spawn', true);
    }

    /**
     * Tests the returnProperty configuration setting
     *
     * @return void
     *
     * @throws ReflectionException
     */
    public function testPropertySetReturnProperty(): void
    {
        $this->assertAttributeIsSetTo('returnProperty', 'retval');
    }

    /**
     * Tests the outputProperty configuration setting
     *
     * @return void
     *
     * @throws ReflectionException
     */
    public function testPropertySetOutputProperty(): void
    {
        $this->assertAttributeIsSetTo('outputProperty', 'outval');
    }

    /**
     * Tests the checkReturn/failonerror configuration setting
     *
     * @return void
     *
     * @throws ReflectionException
     */
    public function testPropertySetCheckReturn(): void
    {
        $this->assertAttributeIsSetTo('checkreturn', true);
    }

    /**
     * Tests the output configuration setting
     *
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     * @throws ReflectionException
     * @throws Exception
     */
    public function testPropertySetOutput(): void
    {
        $this->assertAttributeIsSetTo(
            'output',
            new PhingFile($this->project->getProperty('php.tmpdir') . '/outputfilename')
        );
    }

    /**
     * Tests the error configuration setting
     *
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     * @throws ReflectionException
     * @throws Exception
     */
    public function testPropertySetError(): void
    {
        $this->assertAttributeIsSetTo(
            'error',
            new PhingFile($this->project->getProperty('php.tmpdir') . '/errorfilename')
        );
    }

    /**
     * Tests the append configuration setting
     *
     * @return void
     *
     * @throws ReflectionException
     */
    public function testPropertySetAppend(): void
    {
        $this->assertAttributeIsSetTo('append', true, 'appendoutput');
    }

    /**
     * Tests the parallel configuration setting
     *
     * @return void
     *
     * @throws ReflectionException
     */
    public function testPropertySetParallel(): void
    {
        $this->assertAttributeIsSetTo('parallel', false);
    }

    /**
     * Tests the addsourcefile configuration setting
     *
     * @return void
     *
     * @throws ReflectionException
     */
    public function testPropertySetAddsourcefile(): void
    {
        $this->assertAttributeIsSetTo('addsourcefile', false);
    }

    /**
     * Tests the relative configuration setting
     *
     * @return void
     *
     * @throws ReflectionException
     */
    public function testPropertySetRelative(): void
    {
        $this->assertAttributeIsSetTo('relative', false);
    }

    /**
     * Tests the forwardslash configuration setting
     *
     * @return void
     *
     * @throws ReflectionException
     */
    public function testPropertySetForwardslash(): void
    {
        $this->assertAttributeIsSetTo('forwardslash', true);
    }

    /**
     * Tests the maxparallel configuration setting
     *
     * @return void
     *
     * @throws ReflectionException
     */
    public function testPropertySetMaxparallel(): void
    {
        $this->assertAttributeIsSetTo('maxparallel', 10);
    }

    /**
     * Tests the OS execution for the unspecified OS
     *
     * @return void
     *
     * @throws Exception
     */
    public function testDoNotExecuteOnWrongOs(): void
    {
        // Process
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('was not found in the specified list of valid OSes: unknownos');

        $this->assertNotContains('this should not be executed', $this->getOutput());
    }

    /**
     * Tests the OS execution for the specified OS list
     *
     * @return void
     *
     * @throws Exception
     */
    public function testExecuteOnCorrectOs(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('this should be executed');
    }

    /**
     * Tests the dir changing on a non-existent directory
     *
     * @return void
     *
     * @throws Exception
     */
    public function testFailOnNonExistingDir(): void
    {
        $nonExistentDir = $this->project->getProperty('php.tmpdir') . DIRECTORY_SEPARATOR
            . 'non' . DIRECTORY_SEPARATOR
            . 'existent' . DIRECTORY_SEPARATOR
            . 'dir';

        $this->expectBuildExceptionContaining(
            __FUNCTION__,
            __FUNCTION__,
            sprintf("'%s' is not a valid directory", $nonExistentDir)
        );
    }

    /**
     * Tests the dir changing on an existent directory
     *
     * @return void
     *
     * @throws Exception
     *
     * @requires OS ^(?:(?!Win).)*$
     */
    public function testChangeToDir(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('Working directory change successful');
    }

    /**
     * Tests the failonerror/checkreturn value for 'true'
     *
     * @return void
     *
     * @throws Exception
     *
     * @requires OS ^(?:(?!Win).)*$
     */
    public function testCheckreturnTrue(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertTrue(true);
    }

    /**
     * Tests the failonerror/checkreturn value for 'false'
     *
     * @return void
     *
     * @throws Exception
     *
     * @requires OS ^(?:(?!Win).)*$
     */
    public function testCheckreturnFalse(): void
    {
        $this->expectBuildExceptionContaining(__FUNCTION__, __FUNCTION__, 'Task exited with code (1)');
    }

    /**
     * Tests the outputProperty setting
     *
     * @return void
     *
     * @throws Exception
     */
    public function testOutputProperty(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('The output property\'s value is: "foo"');
    }

    /**
     * Tests the returnProperty setting
     *
     * @return void
     *
     * @throws Exception
     */
    public function testReturnProperty(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('The return property\'s value is: "1"');
    }

    /**
     * Tests the command escaping for execution
     *
     * @return void
     *
     * @throws Exception
     */
    public function testEscape(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs(
            $this->windows
                ? (escapeshellarg('echo') . ' ' . escapeshellarg('foo') . ' ' . escapeshellarg('|') . ' ' . escapeshellarg('cat'))
            : "'echo' 'foo' '|' 'cat'"
        );
    }

    /**
     * Tests the command execution with 'passthru' function
     *
     * @return void
     *
     * @throws Exception
     */
    public function testPassThru(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('Executing command:');
    }

    /**
     * Tests the output file functionality
     *
     * @return void
     *
     * @throws Exception
     */
    public function testOutput(): void
    {
        // Getting a temp. file
        $tempfile = tempnam(FileUtils::getTempDir(), 'phing-exectest-');

        // Setting the property
        $this->project->setProperty('execTmpFile', $tempfile);
        $this->executeTarget(__FUNCTION__);

        // Validating the output
        $output = @file_get_contents($tempfile);
        @unlink($tempfile);
        $this->assertEquals('outfoo', rtrim($output));
    }

    /**
     * Tests the error file functionality
     *
     * @return void
     *
     * @throws Exception
     *
     * @requires OS ^(?:(?!Win).)*$
     */
    public function testError(): void
    {
        // Getting a temp. file
        $tempfile = tempnam(FileUtils::getTempDir(), 'phing-exectest-');

        $scriptFile = getcwd() . '/error_output.sh';
        file_put_contents($scriptFile, 'echo errfoo 1>&2');
        chmod($scriptFile, 0744);

        // Setting the property
        $this->project->setProperty('executable', $scriptFile);
        $this->project->setProperty('execTmpFile', $tempfile);
        $this->executeTarget(__FUNCTION__);

        // Validating the output
        $output = @file_get_contents($tempfile);
        @unlink($tempfile);
        @unlink($scriptFile);
        $this->assertEquals('errfoo', rtrim($output));
    }

    /**
     * Tests the execution with the background process spawning
     *
     * @return void
     *
     * @throws Exception
     *
     * @requires OS ^(?:(?!Win).)*$
     */
    public function testSpawn(): void
    {
        // Process
        $start = time();
        $this->executeTarget(__FUNCTION__);
        $end = time();
        $this->assertLessThan(
            4,
            ($end - $start),
            'Execution time should be lower than 4 seconds, otherwise spawning did not work'
        );
    }

    /**
     * Tests the nested arguments specified for the execution
     *
     * @return void
     *
     * @throws Exception
     */
    public function testNestedArgs(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('echo Hello World');
    }

    /**
     * Tests the missing/unspecified executable information
     *
     * @return void
     *
     * @throws Exception
     */
    public function testMissingExecutable(): void
    {
        $this->expectBuildExceptionContaining(__FUNCTION__, __FUNCTION__, 'Please provide "executable" information');
    }

    /**
     * Tests the escape functionality for special characters in argument
     *
     * @return void
     *
     * @throws Exception
     */
    public function testEscapedArg(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('outval', $this->windows ? 'abc$b3 SB' : 'abc$b3!SB');
    }

    /**
     * Tests the relative source filenames functionality
     *
     * @return void
     *
     * @throws Exception
     *
     * @requires OS ^(?:(?!Win).)*$
     */
    public function testRelativeSourceFilenames(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertNotInLogs('/etc/');
    }

    /**
     * Tests the source filename addition functionality
     *
     * @return void
     *
     * @throws Exception
     *
     * @requires OS ^(?:(?!Win).)*$
     */
    public function testSourceFilename(): void
    {
        $this->executeTarget(__FUNCTION__);
        // As the addsourcefilename is 'off', only the executable should be processed in the execution
        $this->assertInLogs('Executing command: ls');
    }

    /**
     * Tests the output file append functionality
     *
     * @return void
     *
     * @throws Exception
     */
    public function testOutputAppend(): void
    {
        // Getting a temp. file
        $tempfile = tempnam(FileUtils::getTempDir(), 'phing-exectest-');

        // Setting the property
        $this->project->setProperty('execTmpFile', $tempfile);
        $this->executeTarget(__FUNCTION__);

        // Validating the output
        $output = @file_get_contents($tempfile);
        @unlink($tempfile);
        $this->assertEquals($this->windows ? "Append OK \r\nAppend OK" : "Append OK\nAppend OK", rtrim($output));
    }

    /**
     * Tests the parallel configuration
     *
     * @return void
     *
     * @throws Exception
     */
    public function testParallel(): void
    {
        $this->executeTarget(__FUNCTION__);
        $messages = [];
        foreach ($this->logBuffer as $log) {
            $messages[] = $log['message'];
        }
        $this->assertEquals(1, substr_count(implode("\n", $messages), 'Executing command:'));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testMapperSupport(): void
    {
        // Getting a temp. file
        $tempfile = tempnam(FileUtils::getTempDir(), 'phing-exectest-');

        // Setting the property
        $this->project->setProperty('execTmpFile', $tempfile);

        $this->executeTarget(__FUNCTION__);
        $messages = [];
        foreach ($this->logBuffer as $log) {
            $messages[] = $log['message'];
        }
        $this->assertContains('Applied echo to 4 files and 0 directories.', $messages);
    }

    /************************** H E L P E R  M E T H O D S ****************************/

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
     * @param Target|string $target
     * @param string        $taskName
     * @param int           $pos
     *
     * @return Task
     *
     * @throws Exception
     */
    protected function getTaskFromTarget($target, string $taskName, int $pos = 0)
    {
        if (is_string($target)) {
            $class = $target;
        } else {
            $class = get_class($target);

            if (false === $class) {
                throw new Exception(sprintf('could not get class from target %s', $target));
            }
        }

        $rchildren = new ReflectionProperty($class, 'children');
        $rchildren->setAccessible(true);
        $n = -1;
        foreach ($rchildren->getValue($target) as $child) {
            if ($child instanceof Task && ++$n == $pos) {
                return $child;
            }
        }

        throw new Exception(sprintf('%s #%d not found in task', $taskName, $pos));
    }

    /**
     * @param string $target
     * @param string $task
     *
     * @return Task
     *
     * @throws Exception
     */
    protected function getConfiguredTask(string $target, string $task): Task
    {
        $target = $this->getTargetByName($target);
        $task   = $this->getTaskFromTarget($target, $task);
        $task->maybeConfigure();

        if ($task instanceof UnknownElement) {
            return $task->getRuntimeConfigurableWrapper()->getProxy();
        }

        return $task;
    }

    /**
     * @param string                $property
     * @param string|bool|PhingFile $value
     * @param string|null           $propertyName
     *
     * @return void
     *
     * @throws ReflectionException
     * @throws Exception
     */
    protected function assertAttributeIsSetTo(string $property, $value, ?string $propertyName = null): void
    {
        $task = $this->getConfiguredTask('testPropertySet' . ucfirst($property), 'ApplyTask');

        $propertyName = $propertyName === null ? $property : $propertyName;
        $rprop        = new ReflectionProperty('ApplyTask', $propertyName);
        $rprop->setAccessible(true);
        $this->assertEquals($value, $rprop->getValue($task));
    }
}
