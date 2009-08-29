<?php
/**
 * $Id$
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

require_once 'phing/Task.php';
require_once 'phing/system/io/PhingFile.php';
require_once 'phing/system/io/Writer.php';
require_once 'phing/util/LogWriter.php';

/**
 * Runs PHPUnit tests.
 *
 * @author Michiel Rook <michiel.rook@gmail.com>
 * @version $Id$
 * @package phing.tasks.ext.phpunit
 * @see BatchTest
 * @since 2.1.0
 */
class PHPUnitTask extends Task
{
    private $batchtests = array();
    private $formatters = array();
    private $bootstrap = "";
    private $haltonerror = false;
    private $haltonfailure = false;
    private $haltonincomplete = false;
    private $haltonskipped = false;
    private $errorproperty;
    private $failureproperty;
    private $incompleteproperty;
    private $skippedproperty;
    private $printsummary = false;
    private $testfailed = false;
    private $testfailuremessage = "";
    private $codecoverage = false;
    private $groups = array();
    private $excludeGroups = array();

    /**
     * Initialize Task.
     * This method includes any necessary PHPUnit2 libraries and triggers
     * appropriate error if they cannot be found.  This is not done in header
     * because we may want this class to be loaded w/o triggering an error.
     */
    function init() {
        if (version_compare(PHP_VERSION, '5.0.3') < 0)
        {
            throw new BuildException("PHPUnitTask requires PHP version >= 5.0.3", $this->getLocation());
        }
        
        /**
         * Determine PHPUnit version number
         */
        @include_once 'PHPUnit/Runner/Version.php';

        $version = PHPUnit_Runner_Version::id();

        if (version_compare($version, '3.2.0') < 0)
        {
            throw new BuildException("PHPUnitTask requires PHPUnit version >= 3.2.0", $this->getLocation());
        }
            
        /**
         * Other dependencies that should only be loaded when class is actually used.
         */
        require_once 'phing/tasks/ext/phpunit/PHPUnitTestRunner.php';
        require_once 'phing/tasks/ext/phpunit/BatchTest.php';
        require_once 'phing/tasks/ext/phpunit/FormatterElement.php';

        /**
         * Add some defaults to the PHPUnit filter
         */
        $pwd = dirname(__FILE__);

        require_once 'PHPUnit/Framework.php';
        require_once 'PHPUnit/Util/Filter.php';
            
        // point PHPUnit_MAIN_METHOD define to non-existing method
        if (!defined('PHPUnit_MAIN_METHOD'))
        {
            define('PHPUnit_MAIN_METHOD', 'PHPUnitTask::undefined');
        }
        
        $path = realpath($pwd . '/../../../');
        PHPUnit_Util_Filter::addDirectoryToFilter($path);
    }
    
    /**
     * Sets the name of a bootstrap file that is run before
     * executing the tests
     *
     * @param string $bootstrap the name of the bootstrap file
     */
    function setBootstrap($bootstrap)
    {
        $this->bootstrap = $bootstrap;
    }
    
    function setErrorproperty($value)
    {
        $this->errorproperty = $value;
    }
    
    function setFailureproperty($value)
    {
        $this->failureproperty = $value;
    }
    
    function setIncompleteproperty($value)
    {
        $this->incompleteproperty = $value;
    }
    
    function setSkippedproperty($value)
    {
        $this->skippedproperty = $value;
    }
    
    function setHaltonerror($value)
    {
        $this->haltonerror = $value;
    }

    function setHaltonfailure($value)
    {
        $this->haltonfailure = $value;
    }

    function setHaltonincomplete($value)
    {
        $this->haltonincomplete = $value;
    }

    function setHaltonskipped($value)
    {
        $this->haltonskipped = $value;
    }

    function setPrintsummary($printsummary)
    {
        $this->printsummary = $printsummary;
    }
    
    function setCodecoverage($codecoverage)
    {
        $this->codecoverage = $codecoverage;
    }

    function setGroups($groups)
    {
        $token = ' ,;';
        $this->groups = array();
        $tok = strtok($groups, $token);
        while ($tok !== false) {
            $this->groups[] = $tok;
            $tok = strtok($token);
        }
    }

    function setExcludeGroups($excludeGroups)
    {
        $token = ' ,;';
        $this->excludeGroups = array();
        $tok = strtok($groups, $token);
        while ($tok !== false) {
            $this->excludeGroups[] = $tok;
            $tok = strtok($token);
        }
    }

    /**
     * Add a new formatter to all tests of this task.
     *
     * @param FormatterElement formatter element
     */
    function addFormatter(FormatterElement $fe)
    {
        $this->formatters[] = $fe;
    }

    /**
     * The main entry point
     *
     * @throws BuildException
     */
    function main()
    {
        if ($this->codecoverage && !extension_loaded('xdebug'))
        {
            throw new Exception("PHPUnitTask depends on Xdebug being installed to gather code coverage information.");
        }

        $tests = array();
        
        if ($this->printsummary)
        {
            $fe = new FormatterElement();
            $fe->setType("summary");
            $fe->setUseFile(false);
            $this->formatters[] = $fe;
        }
        
        foreach ($this->batchtests as $batchtest)
        {
            $tests = array_merge($tests, $batchtest->elements());
        }           
        
        foreach ($this->formatters as $fe)
        {
            $formatter = $fe->getFormatter();
            $formatter->setProject($this->getProject());

            if ($fe->getUseFile())
            {
                $destFile = new PhingFile($fe->getToDir(), $fe->getOutfile());
                
                $writer = new FileWriter($destFile->getAbsolutePath());

                $formatter->setOutput($writer);
            }
            else
            {
                $formatter->setOutput($this->getDefaultOutput());
            }

            $formatter->startTestRun();
        }
        
        if ($this->bootstrap)
        {
            require_once $this->bootstrap;
        }
        
        foreach ($tests as $test)
        {
            $this->execute($test);
        }

        foreach ($this->formatters as $fe)
        {
            $formatter = $fe->getFormatter();
            $formatter->endTestRun();
        }
        
        if ($this->testfailed)
        {
            throw new BuildException($this->testfailuremessage);
        }
    }

    /**
     * @throws BuildException
     */
    private function execute($test)
    {
        $runner = new PHPUnitTestRunner($this->project, $this->groups, $this->excludeGroups);
        
        $runner->setCodecoverage($this->codecoverage);

        foreach ($this->formatters as $fe)
        {
            $formatter = $fe->getFormatter();

            $runner->addFormatter($formatter);      
        }
        
        /* Invoke the 'suite' method when it exists in the test class */
        $testClass = new ReflectionClass($test);
        
        if ($testClass->hasMethod('suite'))
        {
            $suiteMethod = $testClass->getMethod('suite');
            
            $suite = $suiteMethod->invoke(NULL, $testClass->getName());
        }
        else
        {
            $suite = new PHPUnit_Framework_TestSuite($test);
        }
        
        $runner->run($suite);

        $retcode = $runner->getRetCode();
        
        if ($retcode == PHPUnitTestRunner::ERRORS) {
            if ($this->errorproperty) {
                $this->project->setNewProperty($this->errorproperty, true);
            }
            if ($this->haltonerror) {
                $this->testfailed = true;
                $this->testfailuremessage = $runner->getLastFailureMessage();
            }
        } elseif ($retcode == PHPUnitTestRunner::FAILURES) {
            if ($this->failureproperty) {
                $this->project->setNewProperty($this->failureproperty, true);
            }
            
            if ($this->haltonfailure) {
                $this->testfailed = true;
                $this->testfailuremessage = $runner->getLastFailureMessage();
            }
        } elseif ($retcode == PHPUnitTestRunner::INCOMPLETES) {
            if ($this->incompleteproperty) {
                $this->project->setNewProperty($this->incompleteproperty, true);
            }
            
            if ($this->haltonincomplete) {
                $this->testfailed = true;
                $this->testfailuremessage = $runner->getLastFailureMessage();
            }
        } elseif ($retcode == PHPUnitTestRunner::SKIPPED) {
            if ($this->skippedproperty) {
                $this->project->setNewProperty($this->skippedproperty, true);
            }
            
            if ($this->haltonskipped) {
                $this->testfailed = true;
                $this->testfailuremessage = $runner->getLastFailureMessage();
            }
        }
    }

    private function getDefaultOutput()
    {
        return new LogWriter($this);
    }

    /**
     * Adds a set of tests based on pattern matching.
     *
     * @return BatchTest a new instance of a batch test.
     */
    function createBatchTest()
    {
        $batchtest = new BatchTest($this->getProject());

        $this->batchtests[] = $batchtest;

        return $batchtest;
    }
}

