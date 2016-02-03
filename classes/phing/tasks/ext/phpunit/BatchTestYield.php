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

require_once 'phing/tasks/ext/phpunit/BatchTest.php';

/**
 * Scans a list of files given by the fileset attribute, generates valid test cases
 * through a PHP 5.5 generator (http://php.net/manual/en/language.generators.syntax.php)
 *
 * @author Laurent Laville <pear@laurent-laville.org>
 * @package phing.tasks.ext.phpunit
 * @since 2.13.0
 */
class BatchTestYield extends BatchTest
{
    /**
     * The list of filesets containing the testcase filename rules.
     *
     * @var array $filesets
     */
    protected $filesets = array();

    /** the reference to the project */
    protected $project = null;

    /** the classpath to use with Phing::__import() calls */
    protected $classpath = null;

    /** names of classes to exclude */
    protected $excludeClasses = array();

    /** name of the batchtest/suite */
    protected $name = "Phing Batchtest Yield";

    /**
     * Create a new batchtestyeild instance
     *
     * @param Project the project it depends on.
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

   /**
     * Add a new fileset containing the XML results to aggregate.
     *
     * @param FileSet $fileset the new fileset containing XML results.
     *
     * @return void
     */
    public function addFileSet(FileSet $fileset)
    {
        $this->filesets[] = $fileset;
    }

    /**
     * Returns an array of test cases and test suites that are declared
     * by the files included by the filesets
     *
     * @return Generator (http://www.php.net/manual/en/class.generator.php)
     */
    public function elements()
    {
        $filenames = $this->getFilenames();

        $declaredClasses = array();

        foreach ($filenames as $filename) {
            $definedClasses = PHPUnitUtil::getDefinedClasses($filename, $this->classpath);

            foreach ($definedClasses as $definedClass) {
                $this->project->log("(PHPUnit) Adding $definedClass (from $filename) to tests.", Project::MSG_DEBUG);
            }

            $declaredClasses = array_merge($declaredClasses, $definedClasses);
        }

        $elements = array_filter($declaredClasses, array($this, "filterTests"));

        for ($i = 0; $i < count($elements); $i++) {
            $this->project->log("Yielding $elements[$i] to tests.", Project::MSG_VERBOSE);
            yield $elements[$i];
        }
    }

    /**
     * Iterate over all filesets and return the filename of all files.
     *
     * @return array an array of filenames
     */
    protected function getFilenames()
    {
        $filenames = array();

        foreach ($this->filesets as $fileset) {
            $ds = $fileset->getDirectoryScanner($this->project);
            $ds->scan();

            $files = $ds->getIncludedFiles();

            foreach ($files as $file) {
                $filenames[] = $ds->getBaseDir() . "/" . $file;
            }
        }

        return $filenames;
    }

    /**
     * Filters an array of classes, removes all classes that are not test cases or test suites,
     * or classes that are declared abstract.
     *
     * @param object $input
     *
     * @return bool
     */
    protected function filterTests($input)
    {
        $reflect = new ReflectionClass($input);

        return $this->isTestCase($input) && (!$reflect->isAbstract());
    }

    /**
     * Checks wheter $input is a PHPUnit Test.
     *
     * @param $input
     *
     * @return bool
     */
    protected function isTestCase($input)
    {
        return is_subclass_of($input, 'PHPUnit_Framework_TestCase') || is_subclass_of(
            $input,
            'PHPUnit_Framework_TestSuite'
        );
    }

}
