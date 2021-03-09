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

namespace Phing\Support;

use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Parser\ProjectConfigurator;
use Phing\Project;
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

    /**
     * @var array
     */
    private $outBuffer;

    /**
     * @var array
     */
    private $errBuffer;

    /**
     * @var BuildException
     */
    private $buildException;

    /**
     * Asserts that the log buffer contains specified message at specified priority.
     * @param string $expected Message subsctring
     * @param int $priority Message priority (default: any)
     * @param string $errormsg The error message to display.
     */
    protected function assertInLogs($expected, $priority = null, $errormsg = "Expected to find '%s' in logs: %s")
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
            $representation[] = "[msg=\"{$log['message']}\",priority={$log['priority']}]";
        }
        $this->fail(sprintf($errormsg, $expected, var_export($representation, true)));
    }

    /**
     * Asserts that the log buffer contains specified message at specified priority.
     * @param string $expected Message subsctring
     * @param int $priority Message priority (default: any)
     * @param string $errormsg The error message to display.
     */
    protected function assertLogLineContaining(
        $expected,
        $priority = null,
        $errormsg = "Expected to find a log line that starts with '%s': %s"
    ) {
        $found = false;
        foreach ($this->logBuffer as $log) {
            if (false !== strpos($log['message'], $expected)) {
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
            $representation[] = "[msg=\"{$log['message']}\",priority={$log['priority']}]";
        }
        $this->fail(sprintf($errormsg, $expected, var_export($representation, true)));
    }

    /**
     * Asserts that the log buffer does NOT contain specified message at specified priority.
     * @param string $message Message subsctring
     * @param int $priority Message priority (default: any)
     * @param string $errormsg The error message to display.
     */
    protected function assertNotInLogs(
        $message,
        $priority = null,
        $errormsg = "Unexpected string '%s' found in logs: %s"
    ) {
        foreach ($this->logBuffer as $log) {
            if (false !== stripos($log['message'], $message)) {
                $representation = [];
                foreach ($this->logBuffer as $log) {
                    $representation[] = "[msg=\"{$log['message']}\",priority={$log['priority']}]";
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
     * @param string $cause information string to reader of report
     */
    protected function expectBuildException($target, $cause)
    {
        $this->expectSpecificBuildException($target, $cause, null);
    }

    /**
     * Assert that only the given message has been logged with a
     * priority &gt;= INFO when running the given target.
     */
    protected function expectLog($target, $log)
    {
        $this->executeTarget($target);
        $this->assertInLogs($log);
    }

    /**
     * Assert that the given message has been logged with a priority
     * &gt;= INFO when running the given target.
     */
    protected function expectLogContaining($target, $log)
    {
        $this->executeTarget($target);
        $this->assertInLogs($log);
    }

    /**
     * Assert that the given message has been logged with a priority
     * &gt;= DEBUG when running the given target.
     */
    protected function expectDebuglog($target, $log)
    {
        $this->executeTarget($target);
        $this->assertInLogs($log, Project::MSG_DEBUG);
    }

    /**
     *  execute the target, verify output matches expectations
     *
     * @param string $target target to execute
     * @param string $output output to look for
     */
    protected function expectOutput($target, $output)
    {
        $this->executeTarget($target);
        $realOutput = $this->getOutput();
        $this->assertEquals($output, $realOutput);
    }

    /**
     *  execute the target, verify output matches expectations
     *  and that we got the named error at the end
     * @param string $target target to execute
     * @param string $output output to look for
     * @param string $error Description of Parameter
     */

    protected function expectOutputAndError($target, $output, $error)
    {
        $this->executeTarget($target);
        $realOutput = $this->getOutput();
        $this->assertEquals($output, $realOutput);
        $realError = $this->getError();
        $this->assertEquals($error, $realError);
    }

    protected function getOutput()
    {
        return $this->cleanBuffer($this->outBuffer);
    }

    protected function getError()
    {
        return $this->cleanBuffer($this->errBuffer);
    }

    protected function getBuildException()
    {
        return $this->buildException;
    }

    private function cleanBuffer($buffer)
    {
        $cleanedBuffer = "";
        $cr = false;
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
     * @throws BuildException
     */
    protected function configureProject($filename)
    {
        $this->logBuffer = [];
        $this->project = new Project();
        $this->project->init();
        $f = new File($filename);
        $this->project->setUserProperty("phing.file", $f->getAbsolutePath());
        $this->project->setUserProperty("phing.dir", dirname($f->getAbsolutePath()));
        $this->project->addBuildListener(new PhingTestListener($this));
        ProjectConfigurator::configureProject($this->project, new File($filename));
    }

    /**
     *  execute a target we have set up
     * @pre configureProject has been called
     * @param string $targetName target to run
     */
    protected function executeTarget($targetName)
    {
        if (empty($this->project)) {
            return;
        }

        $this->outBuffer = "";
        $this->errBuffer = "";
        $this->logBuffer = [];
        $this->buildException = null;
        $this->project->executeTarget($targetName);
    }

    /**
     * Get the project which has been configured for a test.
     *
     * @return Project the Project instance for this test.
     */
    protected function getProject()
    {
        return $this->project;
    }

    /**
     * get the directory of the project
     * @return File the base dir of the project
     */
    protected function getProjectDir()
    {
        return $this->project->getBasedir();
    }

    /**
     *  run a target, wait for a build exception
     *
     * @param string $target target to run
     * @param string $cause information string to reader of report
     * @param string $msg the message value of the build exception we are waiting for
     * set to null for any build exception to be valid
     */
    protected function expectSpecificBuildException($target, $cause, $msg)
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
        $this->fail("Should throw BuildException because: " . $cause);
    }

    /**
     *  run a target, expect an exception string
     *  containing the substring we look for (case sensitive match)
     *
     * @param string $target target to run
     * @param string $cause information string to reader of report
     * @param string $contains substring of the build exception to look for
     */
    protected function expectBuildExceptionContaining($target, $cause, $contains)
    {
        try {
            $this->executeTarget($target);
        } catch (BuildException $ex) {
            $this->buildException = $ex;
            $found = false;
            while ($ex) {
                $msg = $ex->getMessage();
                if (false !== strpos($ex->getMessage(), $contains)) {
                    $found = true;
                }
                $ex = $ex->getPrevious();
            };

            if (!$found) {
                $this->fail(
                    "Should throw BuildException because '" . $cause . "' with message containing '" . $contains
                    . "' (actual message '" . $msg . "' instead)"
                );
            }

            $this->assertEquals(1, 1); // increase number of positive assertions
            return;
        }
        $this->fail("Should throw BuildException because: " . $cause);
    }

    /**
     * call a target, verify property is as expected
     *
     * @param string $target build file target
     * @param string $property property name
     * @param string $value expected value
     */

    protected function expectPropertySet($target, $property, $value = "true")
    {
        $this->executeTarget($target);
        $this->assertPropertyEquals($property, $value);
    }

    /**
     * assert that a property equals a value; comparison is case sensitive.
     * @param string $property property name
     * @param string $value expected value
     */
    protected function assertPropertyEquals($property, $value)
    {
        $result = $this->project->getProperty($property);
        $this->assertEquals($value, $result, "property " . $property);
    }

    /**
     * assert that a property equals &quot;true&quot;
     * @param string $property property name
     */
    protected function assertPropertySet($property)
    {
        $this->assertPropertyEquals($property, "true");
    }

    /**
     * assert that a property is null
     * @param string $property property name
     */
    protected function assertPropertyUnset($property)
    {
        $this->assertPropertyEquals($property, null);
    }

    /**
     * call a target, verify property is null
     * @param string $target build file target
     * @param string $property property name
     */
    protected function expectPropertyUnset($target, $property)
    {
        $this->expectPropertySet($target, $property, null);
    }

    /**
     * Retrieve a resource from the caller classloader to avoid
     * assuming a vm working directory. The resource path must be
     * relative to the package name or absolute from the root path.
     * @param resource $resource the resource to retrieve its url.
     * @throws BuildException if resource is not found.
     */
    protected function getResource($resource)
    {
        throw new BuildException("getResource() not yet implemented");
        //$url = ggetClass().getResource(resource);
        //assertNotNull("Could not find resource :" + resource, url);
        //return url;
    }

    protected function rmdir($dir)
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
     * @param int $timestamp Timestamp to us as pin-point
     * @param string $type Whether 'fulldate' or 'time'
     * @return string
     */
    protected function getRelativeDate($timestamp, $type = 'fulldate')
    {
        // calculate the diffrence
        $timediff = time() - $timestamp;

        if ($timediff < 3600) {
            if ($timediff < 120) {
                $returndate = "1 minute ago";
            } else {
                $returndate = ceil($timediff / 60) . " minutes ago";
            }
        } else {
            if ($timediff < 7200) {
                $returndate = "1 hour ago.";
            } else {
                if ($timediff < 86400) {
                    $returndate = ceil($timediff / 3600) . " hours ago";
                } else {
                    if ($timediff < 172800) {
                        $returndate = "1 day ago.";
                    } else {
                        if ($timediff < 604800) {
                            $returndate = ceil($timediff / 86400) . " days ago";
                        } else {
                            if ($timediff < 1209600) {
                                $returndate = ceil($timediff / 86400) . " days ago";
                            } else {
                                if ($timediff < 2629744) {
                                    $returndate = ceil($timediff / 86400) . " days ago";
                                } else {
                                    if ($timediff < 3024000) {
                                        $returndate = ceil($timediff / 604900) . " weeks ago";
                                    } else {
                                        if ($timediff > 5259486) {
                                            $returndate = ceil($timediff / 2629744) . " months ago";
                                        } else {
                                            $returndate = ceil($timediff / 604900) . " weeks ago";
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

    public function assertFileSizeAtLeast(string $filepath, int $bytes)
    {
        $actualSize = filesize($filepath);

        if (!is_int($actualSize)) {
            $this->fail("Error while reading file '$filepath'");
        }

        $this->assertGreaterThanOrEqual($bytes, $actualSize);
    }
}
