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
 * Prints XML output of the test to a specified Writer
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.tasks.ext.formatter
 * @since   2.1.0
 */
class XMLPHPUnitResultFormatter7 extends PHPUnitResultFormatter7
{
    /**
     * @var PHPUnit_Util_Log_JUnit
     */
    private $logger = null;

    /**
     * @param PHPUnitTask $parentTask
     */
    public function __construct(PHPUnitTask $parentTask)
    {
        parent::__construct($parentTask);

        $logIncompleteSkipped = $parentTask->getHaltonincomplete() || $parentTask->getHaltonskipped();

        $this->logger = new PHPUnit\Util\Log\JUnit(null, $logIncompleteSkipped);
        $this->logger->setWriteDocument(false);
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return ".xml";
    }

    /**
     * @return string
     */
    public function getPreferredOutfile()
    {
        return "testsuites";
    }

    /**
     * @param PHPUnit\Framework\TestSuite $suite
     */
    public function startTestSuite(PHPUnit\Framework\TestSuite $suite): void
    {
        parent::startTestSuite($suite);

        $this->logger->startTestSuite($suite);
    }

    /**
     * @param PHPUnit\Framework\TestSuite $suite
     */
    public function endTestSuite(PHPUnit\Framework\TestSuite $suite): void
    {
        parent::endTestSuite($suite);

        $this->logger->endTestSuite($suite);
    }

    /**
     * @param PHPUnit\Framework\Test $test
     */
    public function startTest(PHPUnit\Framework\Test $test): void
    {
        parent::startTest($test);

        $this->logger->startTest($test);
    }

    /**
     * @param PHPUnit\Framework\Test $test
     * @param float $time
     */
    public function endTest(PHPUnit\Framework\Test $test, float $time): void
    {
        parent::endTest($test, $time);

        $this->logger->endTest($test, $time);
    }

    /**
     * @param PHPUnit\Framework\Test $test
     * @param Exception $e
     * @param float $time
     */
    public function addError(PHPUnit\Framework\Test $test, Throwable $e, float $time): void
    {
        parent::addError($test, $e, $time);

        $this->logger->addError($test, $e, $time);
    }

    /**
     * @param PHPUnit\Framework\Test $test
     * @param PHPUnit\Framework\AssertionFailedError $e
     * @param float $time
     */
    public function addFailure(
        PHPUnit\Framework\Test $test,
        PHPUnit\Framework\AssertionFailedError $e,
        float $time
    ): void {
        parent::addFailure($test, $e, $time);

        $this->logger->addFailure($test, $e, $time);
    }

    /**
     * @param PHPUnit\Framework\Test $test
     * @param PHPUnit\Framework\AssertionFailedError $e
     * @param float $time
     */
    public function addWarning(PHPUnit\Framework\Test $test, \PHPUnit\Framework\Warning $e, float $time): void
    {
        parent::addWarning($test, $e, $time);

        $this->logger->addWarning($test, $e, $time);
    }

    /**
     * @param PHPUnit\Framework\Test $test
     * @param Exception $e
     * @param float $time
     */
    public function addIncompleteTest(PHPUnit\Framework\Test $test, Throwable $e, float $time): void
    {
        parent::addIncompleteTest($test, $e, $time);

        $this->logger->addIncompleteTest($test, $e, $time);
    }

    public function endTestRun()
    {
        parent::endTestRun();

        if ($this->out) {
            $this->out->write($this->logger->getXML());
            $this->out->close();
        }
    }
}
