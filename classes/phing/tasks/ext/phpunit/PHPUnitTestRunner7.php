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

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use PHPUnit\Runner\Filter\ExcludeGroupFilterIterator;
use PHPUnit\Runner\Filter\Factory;
use PHPUnit\Runner\Filter\IncludeGroupFilterIterator;
use PHPUnit\Util\ErrorHandler;
use SebastianBergmann\CodeCoverage\CodeCoverage;

/**
 * Simple Testrunner for PHPUnit that runs all tests of a testsuite.
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.tasks.ext.phpunit
 */
class PHPUnitTestRunner7 implements TestListener
{
    /**
     * @var bool
     */
    private $hasErrors = false;

    /**
     * @var bool
     */
    private $hasFailures = false;

    /**
     * @var bool
     */
    private $hasWarnings = false;

    /**
     * @var bool
     */
    private $hasIncomplete = false;

    /**
     * @var bool
     */
    private $hasSkipped = false;

    /**
     * @var bool
     */
    private $hasRisky = false;

    /**
     * @var string
     */
    private $lastErrorMessage = '';

    /**
     * @var string
     */
    private $lastFailureMessage = '';

    /**
     * @var string
     */
    private $lastWarningMessage = '';

    /**
     * @var string
     */
    private $lastIncompleteMessage = '';

    /**
     * @var string
     */
    private $lastSkippedMessage = '';

    /**
     * @var string
     */
    private $lastRiskyMessage = '';

    /**
     * @var array
     */
    private $formatters = [];

    /**
     * @var TestListener[]
     */
    private $listeners = [];

    /**
     * @var CodeCoverage|null
     */
    private $codecoverage = null;

    /**
     * @var Project|null
     */
    private $project = null;

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
    private $useCustomErrorHandler = true;

    /**
     * @param Project $project
     * @param array   $groups
     * @param array   $excludeGroups
     * @param bool    $processIsolation
     */
    public function __construct(
        Project $project,
        array $groups = [],
        array $excludeGroups = [],
        bool $processIsolation = false
    ) {
        $this->project          = $project;
        $this->groups           = $groups;
        $this->excludeGroups    = $excludeGroups;
        $this->processIsolation = $processIsolation;
    }

    /**
     * @param CodeCoverage $codecoverage
     *
     * @return void
     */
    public function setCodecoverage($codecoverage): void
    {
        $this->codecoverage = $codecoverage;
    }

    /**
     * @param bool $useCustomErrorHandler
     *
     * @return void
     */
    public function setUseCustomErrorHandler(bool $useCustomErrorHandler): void
    {
        $this->useCustomErrorHandler = $useCustomErrorHandler;
    }

    /**
     * @param PHPUnitResultFormatter7 $formatter
     *
     * @return void
     */
    public function addFormatter(TestListener $formatter): void
    {
        $this->addListener($formatter);
        $this->formatters[] = $formatter;
    }

    /**
     * @param int    $level
     * @param string $message
     * @param string $file
     * @param int    $line
     *
     * @return bool
     */
    public function handleError(int $level, string $message, string $file, int $line): bool
    {
        return ErrorHandler::handleError($level, $message, $file, $line);
    }

    /**
     * @param TestListener $listener
     *
     * @return void
     */
    public function addListener($listener)
    {
        $this->listeners[] = $listener;
    }

    /**
     * Run a test
     *
     * @param TestSuite $suite
     *
     * @return void
     *
     * @throws ReflectionException
     * @throws BuildException
     */
    public function run(TestSuite $suite)
    {
        $res = new TestResult();

        if ($this->codecoverage) {
            $whitelist = CoverageMerger::getWhiteList($this->project);

            $this->codecoverage->filter()->addFilesToWhiteList($whitelist);

            $res->setCodeCoverage($this->codecoverage);
        }

        $res->addListener($this);

        foreach ($this->formatters as $formatter) {
            $res->addListener($formatter);
        }

        /* Set PHPUnit error handler */
        if ($this->useCustomErrorHandler) {
            $oldErrorHandler = set_error_handler([$this, 'handleError'], E_ALL | E_STRICT);
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
     * @param TestResult $res
     *
     * @return void
     */
    private function checkResult(TestResult $res): void
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
     * @param string    $message
     * @param Test      $test
     * @param Throwable $e
     *
     * @return string
     */
    protected function composeMessage(string $message, Test $test, Throwable $e): string
    {
        $name    = ($test instanceof TestCase ? $test->getName() : '');
        $message = 'Test ' . $message . ' (' . $name . ' in class ' . get_class($test) . ' ' . $e->getFile()
            . ' on line ' . $e->getLine() . '): ' . $e->getMessage();

        if ($e instanceof ExpectationFailedException && $e->getComparisonFailure()) {
            $message .= "\n" . $e->getComparisonFailure()->getDiff();
        }

        return $message;
    }

    /**
     * An error occurred.
     *
     * @param Test      $test
     * @param Throwable $e
     * @param float     $time
     *
     * @return void
     */
    public function addError(Test $test, Throwable $e, float $time): void
    {
        $this->lastErrorMessage = $this->composeMessage('ERROR', $test, $e);
    }

    /**
     * A failure occurred.
     *
     * @param Test                 $test
     * @param AssertionFailedError $e
     * @param float                $time
     *
     * @return void
     */
    public function addFailure(
        Test $test,
        AssertionFailedError $e,
        float $time
    ): void {
        $this->lastFailureMessage = $this->composeMessage('FAILURE', $test, $e);
    }

    /**
     * A failure occurred.
     *
     * @param Test                 $test
     * @param AssertionFailedError $e
     * @param float                $time
     *
     * @return void
     */
    public function addWarning(Test $test, Warning $e, float $time): void
    {
        $this->lastWarningMessage = $this->composeMessage('WARNING', $test, $e);
    }

    /**
     * Incomplete test.
     *
     * @param Test      $test
     * @param Exception $e
     * @param float     $time
     *
     * @return void
     */
    public function addIncompleteTest(Test $test, Throwable $e, float $time): void
    {
        $this->lastIncompleteMessage = $this->composeMessage('INCOMPLETE', $test, $e);
    }

    /**
     * Skipped test.
     *
     * @param Test      $test
     * @param Exception $e
     * @param float     $time
     *
     * @return void
     *
     * @since Method available since Release 3.0.0
     */
    public function addSkippedTest(Test $test, Throwable $e, float $time): void
    {
        $this->lastSkippedMessage = $this->composeMessage('SKIPPED', $test, $e);
    }

    /**
     * Risky test
     *
     * @param Test      $test
     * @param Exception $e
     * @param float     $time
     *
     * @return void
     */
    public function addRiskyTest(Test $test, Throwable $e, float $time): void
    {
        $this->lastRiskyMessage = $this->composeMessage('RISKY', $test, $e);
    }

    /**
     * A test started.
     *
     * @param string $testName
     *
     * @return void
     */
    public function testStarted(string $testName): void
    {
    }

    /**
     * A test ended.
     *
     * @param string $testName
     *
     * @return void
     */
    public function testEnded(string $testName): void
    {
    }

    /**
     * A test failed.
     *
     * @param int                  $status
     * @param Test                 $test
     * @param AssertionFailedError $e
     *
     * @return void
     */
    public function testFailed(int $status, Test $test, AssertionFailedError $e): void
    {
    }

    /**
     * Override to define how to handle a failed loading of
     * a test suite.
     *
     * @param string $message
     *
     * @return void
     *
     * @throws BuildException
     */
    protected function runFailed(string $message): void
    {
        throw new BuildException($message);
    }

    /**
     * A test suite started.
     *
     * @param TestSuite $suite
     *
     * @return void
     *
     * @since Method available since Release 2.2.0
     */
    public function startTestSuite(TestSuite $suite): void
    {
    }

    /**
     * A test suite ended.
     *
     * @param TestSuite $suite
     *
     * @return void
     *
     * @since Method available since Release 2.2.0
     */
    public function endTestSuite(TestSuite $suite): void
    {
    }

    /**
     * A test started.
     *
     * @param Test $test
     *
     * @return void
     */
    public function startTest(Test $test): void
    {
    }

    /**
     * A test ended.
     *
     * @param Test  $test
     * @param float $time
     *
     * @return void
     */
    public function endTest(Test $test, float $time): void
    {
        if ($test instanceof TestCase) {
            if (!$test->hasExpectationOnOutput()) {
                echo $test->getActualOutput();
            }
        }
    }

    /**
     * @param TestSuite $suite
     *
     * @return void
     *
     * @throws ReflectionException
     */
    private function injectFilters(TestSuite $suite): void
    {
        $filterFactory = new Factory();

        if (empty($this->excludeGroups) && empty($this->groups)) {
            return;
        }

        if (!empty($this->excludeGroups)) {
            $filterFactory->addFilter(
                new ReflectionClass(ExcludeGroupFilterIterator::class),
                $this->excludeGroups
            );
        }

        if (!empty($this->groups)) {
            $filterFactory->addFilter(
                new ReflectionClass(IncludeGroupFilterIterator::class),
                $this->groups
            );
        }

        $suite->injectFilter($filterFactory);
    }
}
