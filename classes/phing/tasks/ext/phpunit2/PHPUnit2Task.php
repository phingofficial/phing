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
 * Runs PHPUnit2 tests.
 *
 * @author Michiel Rook <michiel.rook@gmail.com>
 * @version $Id$
 * @package phing.tasks.ext.phpunit2
 * @see BatchTest
 * @since 2.1.0
 */
class PHPUnit2Task extends Task
{
	private $batchtests = array();
	private $formatters = array();
	private $haltonerror = false;
	private $haltonfailure = false;
	private $failureproperty;
	private $errorproperty;
	private $printsummary = false;
	private $testfailed = false;
	private $codecoverage = false;

	/**
	 * Initialize Task.
 	 * This method includes any necessary PHPUnit2 libraries and triggers
	 * appropriate error if they cannot be found.  This is not done in header
	 * because we may want this class to be loaded w/o triggering an error.
	 */
	function init() {
		include_once 'PHPUnit2/Util/Filter.php';
		if (!class_exists('PHPUnit2_Util_Filter')) {
			throw new BuildException("PHPUnit2Task depends on PEAR PHPUnit2 package being installed.", $this->getLocation());
		}
		
		if (version_compare(PHP_VERSION, '5.0.3') < 0) {
		    throw new BuildException("PHPUnit2Task requires PHP version >= 5.0.3.", $this->getLocation());
		}

		// other dependencies that should only be loaded when class is actually used.
		require_once 'phing/tasks/ext/phpunit2/PHPUnit2TestRunner.php';
		require_once 'phing/tasks/ext/phpunit2/BatchTest.php';
		require_once 'phing/tasks/ext/phpunit2/FormatterElement.php';
		require_once 'phing/tasks/ext/phpunit2/SummaryPHPUnit2ResultFormatter.php';

		// add some defaults to the PHPUnit2 Filter
		PHPUnit2_Util_Filter::addFileToFilter('PHPUnit2Task.php');
		PHPUnit2_Util_Filter::addFileToFilter('PHPUnit2TestRunner.php');
		PHPUnit2_Util_Filter::addFileToFilter('phing/Task.php');
		PHPUnit2_Util_Filter::addFileToFilter('phing/Target.php');
		PHPUnit2_Util_Filter::addFileToFilter('phing/Project.php');
		PHPUnit2_Util_Filter::addFileToFilter('phing/Phing.php');
		PHPUnit2_Util_Filter::addFileToFilter('phing.php');

	}
	
	function setFailureproperty($value)
	{
		$this->failureproperty = $value;
	}
	
	function setErrorproperty($value)
	{
		$this->errorproperty = $value;
	}
	
	function setHaltonerror($value)
	{
		$this->haltonerror = $value;
	}

	function setHaltonfailure($value)
	{
		$this->haltonfailure = $value;
	}

	function setPrintsummary($printsummary)
	{
		$this->printsummary = $printsummary;
	}
	
	function setCodecoverage($codecoverage)
	{
		$this->codecoverage = $codecoverage;
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
		$tests = array();
		
		if ($this->printsummary)
		{
			$fe = new FormatterElement();
			$fe->setClassName('SummaryPHPUnit2ResultFormatter');
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
		
		foreach ($tests as $test)
		{
			$this->execute(new PHPUnit2_Framework_TestSuite(new ReflectionClass($test)));
		}

		foreach ($this->formatters as $fe)
		{
			$formatter = $fe->getFormatter();
			$formatter->endTestRun();
		}
		
		if ($this->testfailed)
		{
			throw new BuildException("One or more tests failed");
		}
	}

	/**
	 * @throws BuildException
	 */
	private function execute($suite)
	{
		$runner = new PHPUnit2TestRunner($suite, $this->project);
		
		$runner->setCodecoverage($this->codecoverage);

		foreach ($this->formatters as $fe)
		{
			$formatter = $fe->getFormatter();

			$runner->addFormatter($formatter);
		}

		$runner->run();

		$retcode = $runner->getRetCode();
		
		if ($retcode == PHPUnit2TestRunner::ERRORS) {
		    if ($this->errorproperty) {
				$this->project->setNewProperty($this->errorproperty, true);
			}
			if ($this->haltonerror) {
			    $this->testfailed = true;
			}
		} elseif ($retcode == PHPUnit2TestRunner::FAILURES) {
			if ($this->failureproperty) {
				$this->project->setNewProperty($this->failureproperty, true);
			}
			
			if ($this->haltonfailure) {
				$this->testfailed = true;
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
?>