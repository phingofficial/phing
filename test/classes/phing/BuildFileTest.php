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
 * A BuildFileTest is a TestCase which executes targets from a Phing buildfile
 * for testing.
 *
 * This class provides a number of utility methods for particular build file
 * tests which extend this class.
 *
 * @author Nico Seessle <nico@seessle.de>
 * @author Conor MacNeill
 * @author Victor Farazdagi <simple.square@gmail.com>
 */
abstract class BuildFileTest extends TestCase
{
    /** @var Project */
    protected $project;

    /**
     * @var array Array of log BuildEvent objects.
     */
    public $logBuffer = [];

    protected $outBuffer;
    protected $errBuffer;

    /** @var BuildException|null */
    protected $buildException;

    /**
     * Asserts that the log buffer contains specified message at specified priority.
     *
     * @param string   $expected Message subsctring
     * @param int|null $priority Message priority (default: any)
     * @param string   $errormsg The error message to display.
     *
     * @return void
     */
    protected function assertInLogs(string $expected, ?int $priority = null, string $errormsg = "Expected to find '%s' in logs: %s"): void
    {
        $found = false;
        foreach ($this->logBuffer as $log) {
            if (false !== stripos($log['message'], $expected)) {
                $this->assertEquals(1, 1); // increase number of positive assertions
                if ($priority === null) {
                    return;
                } elseif ($priority !== null) {
                    if ($priority >= $log['priority']) {
                        $found = true;
                    }
                }
            }
            if ($found) {
                return;
            }
        }
        $representation = [];
        foreach ($this->logBuffer as $log) {
            $representation[] = sprintf('[msg="%s",priority=%s]', $log['message'], $log['priority']);
        }
        $this->fail(sprintf($errormsg, $expected, var_export($representation, true)));
    }

    /**
     * Asserts that the log buffer contains specified message at specified priority.
     *
     * @param string $expected Message subsctring
     * @param int    $priority Message priority (default: any)
     * @param string $errormsg The error message to display.
     *
     * @return void
     */
    protected function assertLogLineContaining(
        string $expected,
        ?int $priority = null,
        string $errormsg = "Expected to find a log line that starts with '%s': %s"
    ): void {
        $found = false;
        foreach ($this->logBuffer as $log) {
            if (false !== strpos($log['message'], $expected)) {
                $this->assertEquals(1, 1); // increase number of positive assertions
                if ($priority === null) {
                    return;
                }

                if ($priority >= $log['priority']) {
                    $found = true;
                }
            }
            if ($found) {
                return;
            }
        }
        $representation = [];
        foreach ($this->logBuffer as $log) {
            $representation[] = sprintf('[msg="%s",priority=%s]', $log['message'], $log['priority']);
        }
        $this->fail(sprintf($errormsg, $expected, var_export($representation, true)));
    }

    /**
     * Asserts that the log buffer does NOT contain specified message at specified priority.
     *
     * @param string   $message  Message subsctring
     * @param int|null $priority Message priority (default: any)
     * @param string   $errormsg The error message to display.
     *
     * @return void
     */
    protected function assertNotInLogs(
        string $message,
        ?int $priority = null,
        string $errormsg = "Unexpected string '%s' found in logs: %s"
    ): void {
        foreach ($this->logBuffer as $log) {
            if (false !== stripos($log['message'], $message)) {
                $representation = [];
                foreach ($this->logBuffer as $log2) {
                    $representation[] = sprintf('[msg="%s",priority=%s]', $log2['message'], $log2['priority']);
                }
                $this->fail(sprintf($errormsg, $message, var_export($representation, true)));
            }
        }

        $this->assertEquals(1, 1); // increase number of positive assertions
    }

    /**
     *  run a target, expect for any build exception
     *
     * @param string $target target to run
     * @param string $cause  information string to reader of report
     *
     * @return void
     *
     * @throws Exception
     */
    protected function expectBuildException(string $target, string $cause): void
    {
        $this->expectSpecificBuildException($target, $cause, null);
    }

    /**
     * Assert that only the given message has been logged with a
     * priority &gt;= INFO when running the given target.
     *
     * @param string $target
     * @param string $log
     *
     * @return void
     *
     * @throws Exception
     */
    protected function expectLog(string $target, string $log): void
    {
        $this->executeTarget($target);
        $this->assertInLogs($log);
    }

    /**
     * Assert that the given message has been logged with a priority
     * &gt;= INFO when running the given target.
     *
     * @param string $target
     * @param string $log
     *
     * @return void
     *
     * @throws Exception
     */
    protected function expectLogContaining(string $target, string $log): void
    {
        $this->executeTarget($target);
        $this->assertInLogs($log);
    }

    /**
     * Assert that the given message has been logged with a priority
     * &gt;= DEBUG when running the given target.
     *
     * @param string $target
     * @param string $log
     *
     * @return void
     *
     * @throws Exception
     */
    protected function expectDebuglog(string $target, string $log): void
    {
        $this->executeTarget($target);
        $this->assertInLogs($log, Project::MSG_DEBUG);
    }

    /**
     *  execute the target, verify output matches expectations
     *
     * @param string $target target to execute
     * @param string $output output to look for
     *
     * @return void
     *
     * @throws Exception
     */
    protected function expectOutput(string $target, string $output): void
    {
        $this->executeTarget($target);
        $realOutput = $this->getOutput();
        $this->assertEquals($output, $realOutput);
    }

    /**
     *  execute the target, verify output matches expectations
     *  and that we got the named error at the end
     *
     * @param string $target target to execute
     * @param string $output output to look for
     * @param string $error  Description of Parameter
     *
     * @return void
     *
     * @throws Exception
     */
    protected function expectOutputAndError(string $target, string $output, string $error): void
    {
        $this->executeTarget($target);
        $realOutput = $this->getOutput();
        $this->assertEquals($output, $realOutput);
        $realError = $this->getError();
        $this->assertEquals($error, $realError);
    }

    /**
     * @return string
     */
    protected function getOutput(): string
    {
        return $this->cleanBuffer($this->outBuffer);
    }

    /**
     * @return string
     */
    protected function getError(): string
    {
        return $this->cleanBuffer($this->errBuffer);
    }

    /**
     * @return BuildException|null
     */
    protected function getBuildException(): ?BuildException
    {
        return $this->buildException;
    }

    /**
     * @param string $buffer
     *
     * @return string
     */
    private function cleanBuffer(string $buffer): string
    {
        $cleanedBuffer = '';
        $cr            = false;
        for ($i = 0, $bufflen = strlen($buffer); $i < $bufflen; $i++) {
            $ch = $buffer[$i];
            if ($ch == "\r") {
                $cr = true;
                continue;
            }

            if (!$cr) {
                $cleanedBuffer .= $ch;
            } else {
                if ($ch == "\n") {
                    $cleanedBuffer .= $ch;
                } else {
                    $cleanedBuffer .= "\r" . $ch;
                }
            }
        }

        return $cleanedBuffer;
    }

    /**
     *  set up to run the named project
     *
     * @param string $filename name of project file to run
     *
     * @return void
     *
     * @throws NullPointerException
     * @throws ConfigurationException
     * @throws Exception
     * @throws IOException
     * @throws BuildException
     */
    protected function configureProject(string $filename): void
    {
        $this->logBuffer     = [];
        $this->fullLogBuffer = '';
        $this->project       = new Project();
        $this->project->init();
        $f = new PhingFile($filename);
        $this->project->setUserProperty('phing.file', $f->getAbsolutePath());
        $this->project->setUserProperty('phing.dir', dirname($f->getAbsolutePath()));
        $this->project->addBuildListener(new PhingTestListener($this));
        ProjectConfigurator::configureProject($this->project, new PhingFile($filename));
    }

    /**
     *  execute a target we have set up
     *
     * @param string $targetName target to run
     *
     * @return void
     *
     * @throws Exception
     *
     * @pre configureProject has been called
     */
    protected function executeTarget(string $targetName): void
    {
        if (empty($this->project)) {
            return;
        }

        $this->outBuffer      = '';
        $this->errBuffer      = '';
        $this->logBuffer      = [];
        $this->fullLogBuffer  = '';
        $this->buildException = null;
        $this->project->executeTarget($targetName);
    }

    /**
     * Get the project which has been configured for a test.
     *
     * @return Project the Project instance for this test.
     */
    protected function getProject(): Project
    {
        return $this->project;
    }

    /**
     * get the directory of the project
     *
     * @return PhingFile the base dir of the project
     *
     * @throws IOException
     * @throws NullPointerException
     */
    protected function getProjectDir(): PhingFile
    {
        return $this->project->getBasedir();
    }

    /**
     * run a target, wait for a build exception
     *
     * @param string      $target target to run
     * @param string      $cause  information string to reader of report
     * @param string|null $msg    the message value of the build exception we are waiting for
     *                            set to null for any build exception to be valid
     *
     * @return void
     *
     * @throws Exception
     */
    protected function expectSpecificBuildException(string $target, string $cause, ?string $msg = null): void
    {
        try {
            $this->executeTarget($target);
        } catch (BuildException $ex) {
            $this->buildException = $ex;
            if (($msg !== null) && ($ex->getMessage() != $msg)) {
                $this->fail(
                    "Should throw BuildException because '" . $cause
                    . "' with message '" . $msg
                    . "' (actual message '" . $ex->getMessage() . "' instead)"
                );
            }
            $this->assertEquals(1, 1); // increase number of positive assertions

            return;
        }
        $this->fail('Should throw BuildException because: ' . $cause);
    }

    /**
     *  run a target, expect an exception string
     *  containing the substring we look for (case sensitive match)
     *
     * @param string $target   target to run
     * @param string $cause    information string to reader of report
     * @param string $contains substring of the build exception to look for
     *
     * @return void
     *
     * @throws Exception
     */
    protected function expectBuildExceptionContaining(string $target, string $cause, string $contains): void
    {
        try {
            $this->executeTarget($target);
        } catch (BuildException $ex) {
            $this->buildException = $ex;
            $found                = false;
            while ($ex) {
                $msg = $ex->getMessage();
                if (false !== strpos($ex->getMessage(), $contains)) {
                    $found = true;
                }
                $ex = $ex->getPrevious();
            }

            if (!$found) {
                $this->fail(
                    "Should throw BuildException because '" . $cause . "' with message containing '" . $contains
                    . "' (actual message '" . $msg . "' instead)"
                );
            }

            $this->assertEquals(1, 1); // increase number of positive assertions
            return;
        }
        $this->fail('Should throw BuildException because: ' . $cause);
    }

    /**
     * call a target, verify property is as expected
     *
     * @param string      $target   build file target
     * @param string      $property property name
     * @param string|null $value    expected value
     *
     * @return void
     *
     * @throws Exception
     */
    protected function expectPropertySet(string $target, string $property, ?string $value = 'true'): void
    {
        $this->executeTarget($target);
        $this->assertPropertyEquals($property, $value);
    }

    /**
     * assert that a property equals a value; comparison is case sensitive.
     *
     * @param string          $property property name
     * @param string|int|null $value    expected value
     *
     * @return void
     *
     * @throws Exception
     */
    protected function assertPropertyEquals(string $property, $value): void
    {
        $result = $this->project->getProperty($property);
        $this->assertEquals($value, $result, 'property ' . $property);
    }

    /**
     * assert that a property equals &quot;true&quot;
     *
     * @param string $property property name
     *
     * @return void
     *
     * @throws Exception
     */
    protected function assertPropertySet(string $property): void
    {
        $this->assertPropertyEquals($property, 'true');
    }

    /**
     * assert that a property is null
     *
     * @param string $property property name
     *
     * @return void
     *
     * @throws Exception
     */
    protected function assertPropertyUnset(string $property): void
    {
        $this->assertPropertyEquals($property, null);
    }

    /**
     * call a target, verify property is null
     *
     * @param string $target   build file target
     * @param string $property property name
     *
     * @return void
     *
     * @throws Exception
     */
    protected function expectPropertyUnset(string $target, string $property): void
    {
        $this->expectPropertySet($target, $property, null);
    }

    /**
     * Retrieve a resource from the caller classloader to avoid
     * assuming a vm working directory. The resource path must be
     * relative to the package name or absolute from the root path.
     *
     * @param resource $resource the resource to retrieve its url.
     *
     * @return void
     *
     * @throws BuildException if resource is not found.
     */
    protected function getResource($resource): void
    {
        $this->markTestIncomplete('getResource() not yet implemented');
        throw new BuildException('getResource() not yet implemented');
        //$url = ggetClass().getResource(resource);
        //assertNotNull("Could not find resource :" + resource, url);
        //return url;
    }

    /**
     * @param string $dir
     *
     * @return bool
     */
    protected function rmdir(string $dir): bool
    {
        if (!file_exists($dir)) {
            return true;
        }
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            if (!$this->rmdir($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }

    /**
     * Get relative date
     *
     * @param int    $timestamp Timestamp to us as pin-point
     * @param string $type      Whether 'fulldate' or 'time'
     *
     * @return string
     */
    protected function getRelativeDate(int $timestamp, string $type = 'fulldate'): string
    {
        // calculate the diffrence
        $timediff = time() - $timestamp;

        if ($timediff < 3600) {
            if ($timediff < 120) {
                $returndate = '1 minute ago';
            } else {
                $returndate = ceil($timediff / 60) . ' minutes ago';
            }
        } else {
            if ($timediff < 7200) {
                $returndate = '1 hour ago.';
            } else {
                if ($timediff < 86400) {
                    $returndate = ceil($timediff / 3600) . ' hours ago';
                } else {
                    if ($timediff < 172800) {
                        $returndate = '1 day ago.';
                    } else {
                        if ($timediff < 604800) {
                            $returndate = ceil($timediff / 86400) . ' days ago';
                        } else {
                            if ($timediff < 1209600) {
                                $returndate = ceil($timediff / 86400) . ' days ago';
                            } else {
                                if ($timediff < 2629744) {
                                    $returndate = ceil($timediff / 86400) . ' days ago';
                                } else {
                                    if ($timediff < 3024000) {
                                        $returndate = ceil($timediff / 604900) . ' weeks ago';
                                    } else {
                                        if ($timediff > 5259486) {
                                            $returndate = ceil($timediff / 2629744) . ' months ago';
                                        } else {
                                            $returndate = ceil($timediff / 604900) . ' weeks ago';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $returndate;
    }

    /**
     * @param string $filepath
     * @param int    $bytes
     *
     * @return void
     */
    public function assertFileSizeAtLeast(string $filepath, int $bytes): void
    {
        $actualSize = filesize($filepath);

        if (!is_int($actualSize)) {
            $this->fail(sprintf("Error while reading file '%s'", $filepath));
        }

        $this->assertGreaterThanOrEqual($bytes, $actualSize);
    }
}
