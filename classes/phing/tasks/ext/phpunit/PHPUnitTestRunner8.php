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

/**
 * Simple Testrunner for PHPUnit that runs all tests of a testsuite.
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.tasks.ext.phpunit
 */
class PHPUnitTestRunner8 implements \PHPUnit\Runner\TestHook, \PHPUnit\Framework\TestListener
{
    private $hasErrors             = false;
    private $hasFailures           = false;
    private $hasWarnings           = false;
    private $hasIncomplete         = false;
    private $hasSkipped            = false;
    private $hasRisky              = false;
    private $lastErrorMessage      = '';
    private $lastFailureMessage    = '';
    private $lastWarningMessage    = '';
    private $lastIncompleteMessage = '';
    private $lastSkippedMessage    = '';
    private $lastRiskyMessage      = '';

    /**
     * @var PHPUnitResultFormatter7[]
     */
    private $formatters = [];

    /**
     * @var \PHPUnit\Framework\TestListener[]
     */
    private $listeners = [];

    /**
     * @var \SebastianBergmann\CodeCoverage\CodeCoverage|null
     */
    private $codecoverage;

    /**
     * @var Project $project
     */
    private $project;

    private $groups        = [];
    private $excludeGroups = [];

    private $processIsolation = false;

    private $useCustomErrorHandler = true;

    /**
     * @param Project $project
     * @param array   $groups
     * @param array   $excludeGroups
     * @param bool    $processIsolation
     */
    public function __construct(
        Project $project,
        $groups = [],
        $excludeGroups = [],
        $processIsolation = false
    ) {
        $this->project          = $project;
        $this->groups           = $groups;
        $this->excludeGroups    = $excludeGroups;
        $this->processIsolation = $processIsolation;
    }

    /**
     * @param \SebastianBergmann\CodeCoverage\CodeCoverage $codecoverage
     */
    public function setCodecoverage($codecoverage): void
    {
        $this->codecoverage = $codecoverage;
    }

    /**
     * @param bool $useCustomErrorHandler
     */
    public function setUseCustomErrorHandler($useCustomErrorHandler): void
    {
        $this->useCustomErrorHandler = $useCustomErrorHandler;
    }

    /**
     * @param PHPUnitResultFormatter7 $formatter
     */
    public function addFormatter($formatter): void
    {
        $this->addListener($formatter);
        $this->formatters[] = $formatter;
    }

    /**
     * @param \PHPUnit\Framework\TestListener $listener
     */
    public function addListener($listener): void
    {
        $this->listeners[] = $listener;
    }

    /**
     * @param int    $level
     * @param string $message
     * @param string $file
     * @param int    $line
     *
     * @return bool
     */
    public function handleError($level, $message, $file, $line): bool
    {
        $invoke = new PHPUnit\Util\ErrorHandler(true, true, true, true);
        return $invoke($level, $message, $file, $line);
    }

    /**
     * Run a test
     *
     * @param \PHPUnit\Framework\TestSuite $suite
     *
     * @throws \BuildException
     * @throws ReflectionException
     */
    public function run(\PHPUnit\Framework\TestSuite $suite)
    {
        $res = new PHPUnit\Framework\TestResult();

        if ($this->codecoverage) {
            $whitelist = CoverageMerger::getWhiteList($this->project);

            $this->codecoverage->filter()->addFilesToWhiteList($whitelist);

            $res->setCodeCoverage($this->codecoverage);
        }

        // $res->addListener($this);

        foreach ($this->formatters as $formatter) {
            $res->addListener($formatter);
        }

        /* Set PHPUnit error handler */
        if ($this->useCustomErrorHandler) {
            set_error_handler([$this, 'handleError'], E_ALL | E_STRICT);
        }

        $this->injectFilters($suite);
        $suite->run($res);

        foreach ($this->formatters as $formatter) {
            $formatter->processResult($res);
        }

        /* Restore Phing error handler */
        if ($this->useCustomErrorHandler) {
            restore_error_handler();
        }

        if ($this->codecoverage) {
            try {
                CoverageMerger::merge($this->project, $this->codecoverage->getData());
            } catch (IOException $e) {
                throw new BuildException('Merging code coverage failed.', $e);
            }
        }

        $this->checkResult($res);
    }

    /**
     * @param PHPUnit\Framework\TestSuite $suite
     *
     * @throws ReflectionException
     */
    private function injectFilters(PHPUnit\Framework\TestSuite $suite): void
    {
        $filterFactory = new PHPUnit\Runner\Filter\Factory();

        if (empty($this->excludeGroups) && empty($this->groups)) {
            return;
        }

        if (!empty($this->excludeGroups)) {
            $filterFactory->addFilter(
                new ReflectionClass(\PHPUnit\Runner\Filter\ExcludeGroupFilterIterator::class),
                $this->excludeGroups
            );
        }

        if (!empty($this->groups)) {
            $filterFactory->addFilter(
                new ReflectionClass(\PHPUnit\Runner\Filter\IncludeGroupFilterIterator::class),
                $this->groups
            );
        }

        $suite->injectFilter($filterFactory);
    }

    /**
     * @param \PHPUnit\Framework\TestResult $res
     */
    private function checkResult(\PHPUnit\Framework\TestResult $res): void
    {
        if ($res->skippedCount() > 0) {
            $this->hasSkipped = true;
        }

        if ($res->notImplementedCount() > 0) {
            $this->hasIncomplete = true;
        }

        if ($res->warningCount() > 0) {
            $this->hasWarnings = true;
        }

        if ($res->failureCount() > 0) {
            $this->hasFailures = true;
        }

        if ($res->errorCount() > 0) {
            $this->hasErrors = true;
        }

        if ($res->riskyCount() > 0) {
            $this->hasRisky = true;
        }
    }

    /**
     * @return bool
     */
    public function hasErrors(): bool
    {
        return $this->hasErrors;
    }

    /**
     * @return bool
     */
    public function hasFailures(): bool
    {
        return $this->hasFailures;
    }

    /**
     * @return bool
     */
    public function hasWarnings(): bool
    {
        return $this->hasWarnings;
    }

    /**
     * @return bool
     */
    public function hasIncomplete(): bool
    {
        return $this->hasIncomplete;
    }

    /**
     * @return bool
     */
    public function hasSkipped(): bool
    {
        return $this->hasSkipped;
    }

    /**
     * @return bool
     */
    public function hasRisky(): bool
    {
        return $this->hasRisky;
    }

    /**
     * @return string
     */
    public function getLastErrorMessage(): string
    {
        return $this->lastErrorMessage;
    }

    /**
     * @return string
     */
    public function getLastFailureMessage(): string
    {
        return $this->lastFailureMessage;
    }

    /**
     * @return string
     */
    public function getLastIncompleteMessage(): string
    {
        return $this->lastIncompleteMessage;
    }

    /**
     * @return string
     */
    public function getLastSkippedMessage(): string
    {
        return $this->lastSkippedMessage;
    }

    /**
     * @return string
     */
    public function getLastWarningMessage(): string
    {
        return $this->lastWarningMessage;
    }

    /**
     * @return string
     */
    public function getLastRiskyMessage(): string
    {
        return $this->lastRiskyMessage;
    }

    /**
     * An error occurred.
     *
     * @param PHPUnit\Framework\Test $test
     * @param Throwable              $e
     * @param float                  $time
     */
    public function addError(PHPUnit\Framework\Test $test, Throwable $e, float $time): void
    {
        $this->lastErrorMessage = $this->composeMessage('ERROR', $test, $e);
    }

    /**
     * @param string                 $message
     * @param PHPUnit\Framework\Test $test
     * @param Throwable              $e
     *
     * @return string
     */
    protected function composeMessage($message, PHPUnit\Framework\Test $test, Throwable $e)
    {
        $name    = ($test instanceof \PHPUnit\Framework\TestCase ? $test->getName() : '');
        $message = 'Test ' . $message . ' (' . $name . ' in class ' . get_class($test) . '): ' . $e->getMessage();

        if ($e instanceof PHPUnit\Framework\ExpectationFailedException && $e->getComparisonFailure()) {
            $message .= "\n" . $e->getComparisonFailure()->getDiff();
        }

        return $message;
    }

    /**
     * A failure occurred.
     *
     * @param PHPUnit\Framework\Test                 $test
     * @param PHPUnit\Framework\AssertionFailedError $e
     * @param float                                  $time
     */
    public function addFailure(
        PHPUnit\Framework\Test $test,
        PHPUnit\Framework\AssertionFailedError $e,
        float $time
    ): void {
        $this->lastFailureMessage = $this->composeMessage('FAILURE', $test, $e);
    }

    /**
     * A failure occurred.
     *
     * @param PHPUnit\Framework\Test                 $test
     * @param PHPUnit\Framework\AssertionFailedError $e
     * @param float                                  $time
     */
    public function addWarning(PHPUnit\Framework\Test $test, \PHPUnit\Framework\Warning $e, float $time): void
    {
        $this->lastWarningMessage = $this->composeMessage('WARNING', $test, $e);
    }

    /**
     * Incomplete test.
     *
     * @param PHPUnit\Framework\Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addIncompleteTest(PHPUnit\Framework\Test $test, Throwable $e, float $time): void
    {
        $this->lastIncompleteMessage = $this->composeMessage('INCOMPLETE', $test, $e);
    }

    /**
     * Skipped test.
     *
     * @param PHPUnit\Framework\Test $test
     * @param Exception              $e
     * @param float                  $time
     *
     * @since Method available since Release 3.0.0
     */
    public function addSkippedTest(PHPUnit\Framework\Test $test, Throwable $e, float $time): void
    {
        $this->lastSkippedMessage = $this->composeMessage('SKIPPED', $test, $e);
    }

    /**
     * Risky test
     *
     * @param PHPUnit\Framework\Test $test
     * @param Throwable              $e
     * @param float                  $time
     */
    public function addRiskyTest(PHPUnit\Framework\Test $test, Throwable $e, float $time): void
    {
        $this->lastRiskyMessage = $this->composeMessage('RISKY', $test, $e);
    }

    /**
     * A test started.
     *
     * @param string $testName
     */
    public function testStarted($testName): void
    {
    }

    /**
     * A test ended.
     *
     * @param string $testName
     */
    public function testEnded($testName): void
    {
    }

    /**
     * A test failed.
     *
     * @param int                                    $status
     * @param PHPUnit\Framework\Test                 $test
     * @param PHPUnit\Framework\AssertionFailedError $e
     */
    public function testFailed($status, PHPUnit\Framework\Test $test, PHPUnit\Framework\AssertionFailedError $e): void
    {
    }

    /**
     * A test suite started.
     *
     * @param PHPUnit\Framework\TestSuite $suite
     */
    public function startTestSuite(PHPUnit\Framework\TestSuite $suite): void
    {
    }

    /**
     * A test suite ended.
     *
     * @param PHPUnit\Framework\TestSuite $suite
     */
    public function endTestSuite(PHPUnit\Framework\TestSuite $suite): void
    {
    }

    /**
     * A test started.
     *
     * @param PHPUnit\Framework\Test $test
     */
    public function startTest(PHPUnit\Framework\Test $test): void
    {
    }

    /**
     * A test ended.
     *
     * @param PHPUnit\Framework\Test $test
     * @param float                  $time
     */
    public function endTest(PHPUnit\Framework\Test $test, float $time): void
    {
        if (($test instanceof PHPUnit\Framework\TestCase) && !$test->hasExpectationOnOutput()) {
            echo $test->getActualOutput();
        }
    }

    /**
     * Override to define how to handle a failed loading of
     * a test suite.
     *
     * @param string $message
     *
     * @throws BuildException
     */
    protected function runFailed($message): void
    {
        throw new BuildException($message);
    }
}
