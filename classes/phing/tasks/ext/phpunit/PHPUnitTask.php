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

use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\Version;
use PHPUnit\Util\Configuration;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Filter;

/**
 * Runs PHPUnit tests.
 *
 * @see     BatchTest
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.tasks.ext.phpunit
 * @since   2.1.0
 */
class PHPUnitTask extends Task
{
    private $batchtests = [];
    /**
     * @var FormatterElement[] $formatters
     */
    private $formatters = [];

    /**
     * @var string
     */
    private $bootstrap = '';

    /**
     * @var bool
     */
    private $haltonerror = false;

    /**
     * @var bool
     */
    private $haltonfailure = false;

    /**
     * @var bool
     */
    private $haltonincomplete = false;

    /**
     * @var bool
     */
    private $haltonskipped = false;

    /**
     * @var string
     */
    private $errorproperty;

    /**
     * @var string
     */
    private $failureproperty;

    /**
     * @var string
     */
    private $incompleteproperty;

    /**
     * @var string
     */
    private $skippedproperty;

    /**
     * @var bool
     */
    private $printsummary       = false;
    private $testfailed         = false;
    private $testfailuremessage = '';
    private $codecoverage       = null;

    /**
     * @var array
     */
    private $groups = [];

    /**
     * @var array
     */
    private $excludeGroups = [];

    /**
     * @var bool
     */
    private $processIsolation = false;

    /**
     * @var bool
     */
    private $usecustomerrorhandler = true;

    /**
     * @var TestListener[]
     */
    private $listeners = [];

    /**
     * @var string
     */
    private $pharLocation = '';

    /**
     * @var PhingFile
     */
    private $configuration = null;

    /**
     * Initialize Task.
     * This method includes any necessary PHPUnit libraries and triggers
     * appropriate error if they cannot be found.  This is not done in header
     * because we may want this class to be loaded w/o triggering an error.
     *
     * @return void
     */
    public function init(): void
    {
    }

    /**
     * @return void
     */
    private function loadPHPUnit(): void
    {
        /**
         * Determine PHPUnit version number, try
         * PEAR old-style, then composer, then PHAR
         */
        @include_once 'PHPUnit/Runner/Version.php';
        if (!class_exists('PHPUnit_Runner_Version')) {
            @include_once 'phpunit/Runner/Version.php';
        }
        if (!empty($this->pharLocation)) {
            $GLOBALS['_SERVER']['SCRIPT_NAME'] = '-';
            ob_start();
            @include $this->pharLocation;
            ob_end_clean();
        }

        @include_once 'PHPUnit/Autoload.php';
        if (!class_exists(Version::class)) {
            throw new BuildException('PHPUnitTask requires PHPUnit to be installed', $this->getLocation());
        }
    }

    /**
     * Sets the name of a bootstrap file that is run before
     * executing the tests
     *
     * @param string $bootstrap the name of the bootstrap file
     *
     * @return void
     */
    public function setBootstrap(string $bootstrap): void
    {
        $this->bootstrap = $bootstrap;
    }

    /**
     * @param string $value
     *
     * @return void
     */
    public function setErrorproperty(string $value): void
    {
        $this->errorproperty = $value;
    }

    /**
     * @param string $value
     *
     * @return void
     */
    public function setFailureproperty(string $value): void
    {
        $this->failureproperty = $value;
    }

    /**
     * @param string $value
     *
     * @return void
     */
    public function setIncompleteproperty(string $value): void
    {
        $this->incompleteproperty = $value;
    }

    /**
     * @param string $value
     *
     * @return void
     */
    public function setSkippedproperty(string $value): void
    {
        $this->skippedproperty = $value;
    }

    /**
     * @param bool $value
     *
     * @return void
     */
    public function setHaltonerror(bool $value): void
    {
        $this->haltonerror = $value;
    }

    /**
     * @param bool $value
     *
     * @return void
     */
    public function setHaltonfailure(bool $value): void
    {
        $this->haltonfailure = $value;
    }

    /**
     * @return bool
     */
    public function getHaltonfailure(): bool
    {
        return $this->haltonfailure;
    }

    /**
     * @param bool $value
     *
     * @return void
     */
    public function setHaltonincomplete(bool $value): void
    {
        $this->haltonincomplete = $value;
    }

    /**
     * @return bool
     */
    public function getHaltonincomplete(): bool
    {
        return $this->haltonincomplete;
    }

    /**
     * @param bool $value
     *
     * @return void
     */
    public function setHaltonskipped(bool $value): void
    {
        $this->haltonskipped = $value;
    }

    /**
     * @return bool
     */
    public function getHaltonskipped(): bool
    {
        return $this->haltonskipped;
    }

    /**
     * @param bool $printsummary
     *
     * @return void
     */
    public function setPrintsummary(bool $printsummary): void
    {
        $this->printsummary = $printsummary;
    }

    /**
     * @param bool $codecoverage
     *
     * @return void
     */
    public function setCodecoverage($codecoverage): void
    {
        $this->codecoverage = $codecoverage;
    }

    /**
     * @param bool $processIsolation
     *
     * @return void
     */
    public function setProcessIsolation(bool $processIsolation): void
    {
        $this->processIsolation = $processIsolation;
    }

    /**
     * @param bool $usecustomerrorhandler
     *
     * @return void
     */
    public function setUseCustomErrorHandler(bool $usecustomerrorhandler): void
    {
        $this->usecustomerrorhandler = $usecustomerrorhandler;
    }

    /**
     * @param string $groups
     *
     * @return void
     */
    public function setGroups(string $groups): void
    {
        $token        = ' ,;';
        $this->groups = [];
        $tok          = strtok($groups, $token);
        while ($tok !== false) {
            $this->groups[] = $tok;
            $tok            = strtok($token);
        }
    }

    /**
     * @param string $excludeGroups
     *
     * @return void
     */
    public function setExcludeGroups(string $excludeGroups): void
    {
        $token               = ' ,;';
        $this->excludeGroups = [];
        $tok                 = strtok($excludeGroups, $token);
        while ($tok !== false) {
            $this->excludeGroups[] = $tok;
            $tok                   = strtok($token);
        }
    }

    /**
     * Add a new formatter to all tests of this task.
     *
     * @param FormatterElement $fe formatter element
     *
     * @return void
     */
    public function addFormatter(FormatterElement $fe): void
    {
        $fe->setParent($this);
        $this->formatters[] = $fe;
    }

    /**
     * Add a new listener to all tests of this taks
     *
     * @param TestListener $listener
     *
     * @return void
     */
    private function addListener($listener): void
    {
        $this->listeners[] = $listener;
    }

    /**
     * @param PhingFile $configuration
     *
     * @return void
     */
    public function setConfiguration(PhingFile $configuration): void
    {
        $this->configuration = $configuration;
    }

    /**
     * @param string $pharLocation
     *
     * @return void
     */
    public function setPharLocation(string $pharLocation): void
    {
        $this->pharLocation = $pharLocation;
    }

    /**
     * Load and processes the PHPUnit configuration
     *
     * @param PhingFile $configuration
     *
     * @return array
     *
     * @throws ReflectionException
     * @throws BuildException
     * @throws IOException
     */
    protected function handlePHPUnitConfiguration(PhingFile $configuration): array
    {
        if (!$configuration->exists()) {
            throw new BuildException("Unable to find PHPUnit configuration file '" . (string) $configuration . "'");
        }

        $config = Configuration::getInstance($configuration->getAbsolutePath());

        if (empty($config)) {
            return [];
        }

        $phpunit = $config->getPHPUnitConfiguration();

        if (empty($phpunit)) {
            return [];
        }

        $config->handlePHPConfiguration();

        if (isset($phpunit['bootstrap'])) {
            $this->setBootstrap($phpunit['bootstrap']);
        }

        if (isset($phpunit['stopOnFailure'])) {
            $this->setHaltonfailure($phpunit['stopOnFailure']);
        }

        if (isset($phpunit['stopOnError'])) {
            $this->setHaltonerror($phpunit['stopOnError']);
        }

        if (isset($phpunit['stopOnSkipped'])) {
            $this->setHaltonskipped($phpunit['stopOnSkipped']);
        }

        if (isset($phpunit['stopOnIncomplete'])) {
            $this->setHaltonincomplete($phpunit['stopOnIncomplete']);
        }

        if (isset($phpunit['processIsolation'])) {
            $this->setProcessIsolation($phpunit['processIsolation']);
        }

        foreach ($config->getListenerConfiguration() as $listener) {
            if (
                !class_exists($listener['class'], false)
                && $listener['file'] !== ''
            ) {
                include_once $listener['file'];
            }

            if (class_exists($listener['class'])) {
                if (count($listener['arguments']) == 0) {
                    $listener = new $listener['class']();
                } else {
                    $listenerClass = new ReflectionClass(
                        $listener['class']
                    );
                    $listener      = $listenerClass->newInstanceArgs(
                        $listener['arguments']
                    );
                }

                if ($listener instanceof TestListener) {
                    $this->addListener($listener);
                }
            }
        }

        if (method_exists($config, 'getSeleniumBrowserConfiguration')) {
            $browsers = $config->getSeleniumBrowserConfiguration();

            if (
                !empty($browsers)
                && class_exists('PHPUnit_Extensions_SeleniumTestCase')
            ) {
                PHPUnit_Extensions_SeleniumTestCase::$browsers = $browsers;
            }
        }

        return $phpunit;
    }

    /**
     * The main entry point
     *
     * @return void
     *
     * @throws IOException
     * @throws ReflectionException
     */
    public function main(): void
    {
        if ($this->codecoverage && !extension_loaded('xdebug')) {
            throw new BuildException('PHPUnitTask depends on Xdebug being installed to gather code coverage information.');
        }

        $this->loadPHPUnit();
        $suite        = new TestSuite('AllTests');
        $autoloadSave = spl_autoload_functions();

        if ($this->bootstrap) {
            include $this->bootstrap;
        }

        if ($this->configuration) {
            $arguments = $this->handlePHPUnitConfiguration($this->configuration);

            if ($arguments['backupGlobals'] === false) {
                $suite->setBackupGlobals(false);
            }

            if ($arguments['backupStaticAttributes'] === true) {
                $suite->setBackupStaticAttributes(true);
            }
        }

        if ($this->printsummary) {
            $fe = new FormatterElement();
            $fe->setParent($this);
            $fe->setType('summary');
            $fe->setUseFile(false);
            $this->formatters[] = $fe;
        }

        foreach ($this->batchtests as $batchTest) {
            try {
                $this->appendBatchTestToTestSuite($batchTest, $suite);
            } catch (ReflectionException $e) {
                throw new BuildException($this->testfailuremessage, $e);
            }
        }

        try {
            $this->execute($suite);
        } catch (ReflectionException $e) {
            throw new BuildException($this->testfailuremessage, $e);
        }

        if ($this->testfailed) {
            throw new BuildException($this->testfailuremessage);
        }

        $autoloadNew = spl_autoload_functions();
        if (is_array($autoloadNew)) {
            foreach ($autoloadNew as $autoload) {
                spl_autoload_unregister($autoload);
            }
        }

        if (is_array($autoloadSave)) {
            foreach ($autoloadSave as $autoload) {
                spl_autoload_register($autoload);
            }
        }
    }

    /**
     * @param TestSuite $suite
     *
     * @return void
     *
     * @throws BuildException
     * @throws ReflectionException
     * @throws IOException
     */
    protected function execute(TestSuite $suite): void
    {
        if (
            class_exists(Version::class, false) &&
            version_compare(Version::id(), '8.0.0', '<')
        ) {
            $runner = new PHPUnitTestRunner7(
                $this->project,
                $this->groups,
                $this->excludeGroups,
                $this->processIsolation
            );
        } else {
            $runner = new PHPUnitTestRunner8(
                $this->project,
                $this->groups,
                $this->excludeGroups,
                $this->processIsolation
            );
        }

        if ($this->codecoverage) {
            /**
             * Add some defaults to the PHPUnit filter
             */
            $pwd  = __DIR__;
            $path = realpath($pwd . '/../../../');

            if (class_exists(Filter::class)) {
                $filter = new Filter();
                if (method_exists($filter, 'addDirectoryToBlacklist')) {
                    $filter->addDirectoryToBlacklist($path);
                }
                if (class_exists(CodeCoverage::class)) {
                    $codeCoverage = new CodeCoverage(null, $filter);
                    $runner->setCodecoverage($codeCoverage);
                }
            }
        }

        $runner->setUseCustomErrorHandler($this->usecustomerrorhandler);

        foreach ($this->listeners as $listener) {
            $runner->addListener($listener);
        }

        foreach ($this->formatters as $fe) {
            $formatter = $fe->getFormatter();

            if ($fe->getUseFile()) {
                try {
                    $destFile = new PhingFile($fe->getToDir(), $fe->getOutfile());
                } catch (Throwable $e) {
                    throw new BuildException('Unable to create destination.', $e);
                }

                $writer = new FileWriter($destFile->getAbsolutePath());

                $formatter->setOutput($writer);
            } else {
                $formatter->setOutput($this->getDefaultOutput());
            }

            $runner->addFormatter($formatter);

            $formatter->startTestRun();
        }

        $runner->run($suite);

        foreach ($this->formatters as $fe) {
            $formatter = $fe->getFormatter();
            $formatter->endTestRun();
        }

        if ($runner->hasErrors()) {
            if ($this->errorproperty) {
                $this->project->setNewProperty($this->errorproperty, true);
            }
            if ($this->haltonerror) {
                $this->testfailed         = true;
                $this->testfailuremessage = $runner->getLastErrorMessage();
            }
        }

        if ($runner->hasFailures()) {
            if ($this->failureproperty) {
                $this->project->setNewProperty($this->failureproperty, true);
            }

            if ($this->haltonfailure) {
                $this->testfailed         = true;
                $this->testfailuremessage = $runner->getLastFailureMessage();
            }
        }

        if ($runner->hasIncomplete()) {
            if ($this->incompleteproperty) {
                $this->project->setNewProperty($this->incompleteproperty, true);
            }

            if ($this->haltonincomplete) {
                $this->testfailed         = true;
                $this->testfailuremessage = $runner->getLastIncompleteMessage();
            }
        }

        if ($runner->hasSkipped()) {
            if ($this->skippedproperty) {
                $this->project->setNewProperty($this->skippedproperty, true);
            }

            if ($this->haltonskipped) {
                $this->testfailed         = true;
                $this->testfailuremessage = $runner->getLastSkippedMessage();
            }
        }
    }

    /**
     * Add the tests in this batchtest to a test suite
     *
     * @param BatchTest $batchTest
     * @param TestSuite $suite
     *
     * @return void
     *
     * @throws ReflectionException
     * @throws \Exception
     * @throws BuildException
     */
    protected function appendBatchTestToTestSuite(BatchTest $batchTest, TestSuite $suite): void
    {
        foreach ($batchTest->elements() as $element) {
            $testClass = new $element();
            if (!($testClass instanceof TestSuite)) {
                $testClass = new ReflectionClass($element);
            }
            try {
                $suite->addTestSuite($testClass);
            } catch (Exception $e) {
                throw new BuildException('Unable to add TestSuite ' . get_class($testClass), $e);
            }
        }
    }

    /**
     * @return LogWriter
     */
    protected function getDefaultOutput(): LogWriter
    {
        return new LogWriter($this);
    }

    /**
     * Adds a set of tests based on pattern matching.
     *
     * @return BatchTest a new instance of a batch test.
     */
    public function createBatchTest(): BatchTest
    {
        $batchtest = new BatchTest($this->getProject());

        $this->batchtests[] = $batchtest;

        return $batchtest;
    }
}
