<?php

namespace Phing\Test;

use PHPUnit_Framework_TestCase;
use Phing\Project;
use Phing\Io\File;
use AssertionFailureException;
use Description;
use Phing\Exception\BuildException;
use Phing\Parser\ProjectConfigurator;

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
abstract class AbstractBuildFileTest extends PHPUnit_Framework_TestCase
{

    /** @var Project */
    protected $project;

    /**
     * @var array Array of log BuildEvent objects.
     */
    public $logBuffer = array();

    private $outBuffer;
    private $errBuffer;
    private $buildException;

    /**
     * Asserts that the log buffer contains specified message at specified priority.
     * @param string $expected Message subsctring
     * @param int $priority Message priority (default: any)
     * @param string $errmsg The error message to display.
     */
    protected function assertInLogs($expected, $priority = null, $errormsg = "Expected to find '%s' in logs: %s")
    {
        foreach ($this->logBuffer as $log) {
            if (false !== stripos($log, $expected)) {
                $this->assertEquals(1, 1); // increase number of positive assertions

                return;
            }
        }
        $this->fail(sprintf($errormsg, $expected, var_export($this->logBuffer, true)));
    }

    /**
     * Asserts that the log buffer does NOT contain specified message at specified priority.
     * @param string $expected Message subsctring
     * @param int $priority Message priority (default: any)
     * @param string $errmsg The error message to display.
     */
    protected function assertNotInLogs(
        $message,
        $priority = null,
        $errormsg = "Unexpected string '%s' found in logs: %s"
    ) {
        foreach ($this->logBuffer as $log) {
            if (false !== stripos($log, $message)) {
                $this->fail(sprintf($errormsg, $message, var_export($this->logBuffer, true)));
            }
        }

        $this->assertEquals(1, 1); // increase number of positive assertions
    }

    /**
     * Scan the log buffer for all lines matching the pattern
     * "assert [value1] == [value2]" and assert that value1 and
     * value2 are equal.
     *
     * That way, simple test buildfiles can be
     * written that can easily be interpreted when run alone,
     * but are also easier to understand because everything
     * is kept together in the buildfile. Of course, that only works
     * for simple cases like checking the value, but does not guarantee
     * that a particular line occurs at all.
     *
     * @param $target Optional name of a target that is to be executed before scanning the log.
     */
    protected function scanAssertionsInLogs($target = null)
    {
        if ($target) $this->executeTarget($target);
        foreach ($this->logBuffer as $log) {
            if (preg_match('/assert\s*(.*)==(.*)/i', $log, $m)) {
                $this->assertEquals(trim($m[1]), trim($m[2]), $log);
            }
        }
    }

    /**
     *  run a target, expect for any build exception
     *
     * @param  target target to run
     * @param  cause  information string to reader of report
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
     * @param  target  target to execute
     * @param  output  output to look for
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
     * @param  target  target to execute
     * @param  output  output to look for
     * @param  error   Description of Parameter
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
            $ch = $buffer{$i};
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
     * @param  filename name of project file to run
     * @throws \Phing\Exception\BuildException
     */
    protected function configureProject($filename)
    {
        $this->logBuffer = "";
        $this->fullLogBuffer = "";
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

        $this->outBuffer = "";
        $this->errBuffer = "";
        $this->logBuffer = "";
        $this->fullLogBuffer = "";
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
     * @return the base dir of the project
     */
    protected function getProjectDir()
    {
        return $this->project->getBaseDir();
    }

    /**
     *  run a target, wait for a build exception
     *
     * @param  target target to run
     * @param  cause  information string to reader of report
     * @param  msg    the message value of the build exception we are waiting for
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
     * @param  target target to run
     * @param  cause  information string to reader of report
     * @param  msg    the message value of the build exception we are waiting for
     * @param  contains  substring of the build exception to look for
     */
    protected function expectBuildExceptionContaining($target, $cause, $contains)
    {
        try {
            $this->executeTarget($target);
        } catch (BuildException $ex) {
            $this->buildException = $ex;
            if ((null != $contains) && (false === strpos($ex->getMessage(), $contains))) {
                $this->fail(
                    "Should throw BuildException because '" . $cause . "' with message containing '" . $contains . "' (actual message '" . $ex->getMessage(
                    ) . "' instead)"
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
     * @param target build file target
     * @param property property name
     * @param value expected value
     */

    protected function expectPropertySet($target, $property, $value = "true")
    {
        $this->executeTarget($target);
        $this->assertPropertyEquals($property, $value);
    }

    /**
     * assert that a property equals a value; comparison is case sensitive.
     * @param property property name
     * @param value expected value
     */
    protected function assertPropertyEquals($property, $value)
    {
        $result = $this->project->getProperty($property);
        $this->assertEquals($value, $result, "property " . $property);
    }

    /**
     * assert that a property equals &quot;true&quot;
     * @param property property name
     */
    protected function assertPropertySet($property)
    {
        $this->assertPropertyEquals($property, "true");
    }

    /**
     * assert that a property is null
     * @param property property name
     */
    protected function assertPropertyUnset($property)
    {
        $this->assertPropertyEquals($property, null);
    }

    /**
     * call a target, verify property is null
     * @param target build file target
     * @param property property name
     */
    protected function expectPropertyUnset($target, $property)
    {
        $this->expectPropertySet($target, $property, null);
    }

    /**
     * Retrieve a resource from the caller classloader to avoid
     * assuming a vm working directory. The resource path must be
     * relative to the package name or absolute from the root path.
     * @param resource the resource to retrieve its url.
     * @throws AssertionFailureException if resource is not found.
     */
    protected function getResource($resource)
    {
        throw new BuildException("getResource() not yet implemented");
        //$url = ggetClass().getResource(resource);
        //assertNotNull("Could not find resource :" + resource, url);
        //return url;
    }

}
