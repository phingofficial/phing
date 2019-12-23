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
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;

/**
 * This abstract class describes classes that format the results of a PHPUnit testrun.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.ext.phpunit.formatter
 */
abstract class PHPUnitResultFormatter7 implements TestListener
{
    /**
     * @var Writer
     */
    protected $out;

    /** @var Project */
    protected $project;

    /**
     * @var array
     */
    private $timers = [];

    /**
     * @var array
     */
    private $runCounts = [];

    /**
     * @var array
     */
    private $failureCounts = [];

    /**
     * @var array
     */
    private $errorCounts = [];

    /**
     * @var array
     */
    private $incompleteCounts = [];

    /**
     * @var array
     */
    private $skipCounts = [];

    /**
     * @var array
     */
    private $warningCounts = [];

    /**
     * Constructor
     *
     * @param PHPUnitTask $parentTask Calling Task
     */
    public function __construct(PHPUnitTask $parentTask)
    {
        $this->project = $parentTask->getProject();
    }

    /**
     * Sets the writer the formatter is supposed to write its results to.
     *
     * @param Writer $out
     *
     * @return void
     */
    public function setOutput(Writer $out): void
    {
        $this->out = $out;
    }

    /**
     * Returns the extension used for this formatter
     *
     * @return string|null the extension
     */
    public function getExtension(): ?string
    {
        return '';
    }

    /**
     * @return string
     */
    public function getPreferredOutfile(): string
    {
        return '';
    }

    /**
     * @param TestResult $result
     *
     * @return void
     */
    public function processResult(TestResult $result): void
    {
    }

    /**
     * @return void
     */
    public function startTestRun(): void
    {
        $this->timers           = [$this->getMicrotime()];
        $this->runCounts        = [0];
        $this->failureCounts    = [0];
        $this->errorCounts      = [0];
        $this->warningCounts    = [0];
        $this->incompleteCounts = [0];
        $this->skipCounts       = [0];
    }

    /**
     * @return void
     */
    public function endTestRun(): void
    {
    }

    /**
     * @param TestSuite $suite
     *
     * @return void
     */
    public function startTestSuite(TestSuite $suite): void
    {
        $this->timers[]           = $this->getMicrotime();
        $this->runCounts[]        = 0;
        $this->failureCounts[]    = 0;
        $this->errorCounts[]      = 0;
        $this->incompleteCounts[] = 0;
        $this->skipCounts[]       = 0;
    }

    /**
     * @param TestSuite $suite
     *
     * @return void
     */
    public function endTestSuite(TestSuite $suite): void
    {
        $lastRunCount                                  = array_pop($this->runCounts);
        $this->runCounts[count($this->runCounts) - 1] += $lastRunCount;

        $lastFailureCount                                      = array_pop($this->failureCounts);
        $this->failureCounts[count($this->failureCounts) - 1] += $lastFailureCount;

        $lastErrorCount                                    = array_pop($this->errorCounts);
        $this->errorCounts[count($this->errorCounts) - 1] += $lastErrorCount;

        $lastIncompleteCount                                         = array_pop($this->incompleteCounts);
        $this->incompleteCounts[count($this->incompleteCounts) - 1] += $lastIncompleteCount;

        $lastSkipCount                                   = array_pop($this->skipCounts);
        $this->skipCounts[count($this->skipCounts) - 1] += $lastSkipCount;

        array_pop($this->timers);
    }

    /**
     * @param Test $test
     *
     * @return void
     */
    public function startTest(Test $test): void
    {
        $this->runCounts[count($this->runCounts) - 1]++;
    }

    /**
     * @param Test  $test
     * @param float $time
     *
     * @return void
     */
    public function endTest(Test $test, float $time): void
    {
    }

    /**
     * @param Test      $test
     * @param Throwable $e
     * @param float     $time
     *
     * @return void
     */
    public function addError(Test $test, Throwable $e, float $time): void
    {
        $this->errorCounts[count($this->errorCounts) - 1]++;
    }

    /**
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
        $this->failureCounts[count($this->failureCounts) - 1]++;
    }

    /**
     * @param Test    $test
     * @param Warning $e
     * @param float   $time
     *
     * @return void
     */
    public function addWarning(Test $test, Warning $e, float $time): void
    {
        $this->warningCounts[count($this->warningCounts) - 1]++;
    }

    /**
     * @param Test      $test
     * @param Throwable $e
     * @param float     $time
     *
     * @return void
     */
    public function addIncompleteTest(Test $test, Throwable $e, float $time): void
    {
        $this->incompleteCounts[count($this->incompleteCounts) - 1]++;
    }

    /**
     * @param Test      $test
     * @param Throwable $e
     * @param float     $time
     *
     * @return void
     */
    public function addSkippedTest(Test $test, Throwable $e, float $time): void
    {
        $this->skipCounts[count($this->skipCounts) - 1]++;
    }

    /**
     * @param Test      $test
     * @param Throwable $e
     * @param float     $time
     *
     * @return void
     */
    public function addRiskyTest(Test $test, Throwable $e, float $time): void
    {
    }

    /**
     * @return mixed
     */
    public function getRunCount()
    {
        return end($this->runCounts);
    }

    /**
     * @return mixed
     */
    public function getFailureCount()
    {
        return end($this->failureCounts);
    }

    /**
     * @return mixed
     */
    public function getWarningCount()
    {
        return end($this->warningCounts);
    }

    /**
     * @return mixed
     */
    public function getErrorCount()
    {
        return end($this->errorCounts);
    }

    /**
     * @return mixed
     */
    public function getIncompleteCount()
    {
        return end($this->incompleteCounts);
    }

    /**
     * @return mixed
     */
    public function getSkippedCount()
    {
        return end($this->skipCounts);
    }

    /**
     * @return float|int
     */
    public function getElapsedTime()
    {
        if (end($this->timers)) {
            return $this->getMicrotime() - end($this->timers);
        }

        return 0;
    }

    /**
     * @return float
     */
    private function getMicrotime(): float
    {
        return microtime(true);
    }
}
