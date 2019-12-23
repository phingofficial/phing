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

/**
 * Datatype which handles excluded files, classes and methods.
 *
 * @package phing.types
 * @author  Benjamin Schultz <bschultz@proqrent.de>
 * @since   2.4.6
 */
class Excludes extends DataType
{
    /**
     * The directory scanner for getting the excluded files
     *
     * @var DirectoryScanner
     */
    private $directoryScanner = null;

    /**
     * Holds the excluded file patterns
     *
     * @var ExcludesNameEntry[]
     */
    private $files = [];

    /**
     * Holds the excluded classes
     *
     * @var ExcludesNameEntry[]
     */
    private $classes = [];

    /**
     * Holds the excluded methods
     *
     * @var ExcludesNameEntry[]
     */
    private $methods = [];

    /**
     * @param Project $project
     *
     * @throws IOException
     * @throws NullPointerException
     */
    public function __construct(Project $project)
    {
        parent::__construct();
        $this->directoryScanner = new DirectoryScanner();
        $this->directoryScanner->setBasedir($project->getBasedir());
    }

    /**
     * Add a name entry on the exclude file list
     *
     * @return ExcludesNameEntry Reference to object
     */
    public function createFile(): ExcludesNameEntry
    {
        return $this->addExcludesNameEntry($this->files);
    }

    /**
     * Add a name entry on the exclude class list
     *
     * @return ExcludesNameEntry Reference to object
     */
    public function createClass(): ExcludesNameEntry
    {
        return $this->addExcludesNameEntry($this->classes);
    }

    /**
     * Add a name entry on the exclude method list
     *
     * @return ExcludesNameEntry Reference to object
     */
    public function createMethod(): ExcludesNameEntry
    {
        return $this->addExcludesNameEntry($this->methods);
    }

    /**
     * Adds a new ExcludesNameEntry to the given exclusion list.
     *
     * @param ExcludesNameEntry[] $excludesNameEntryList
     *
     * @return ExcludesNameEntry Reference to the created ExcludesNameEntry instance
     */
    private function addExcludesNameEntry(array &$excludesNameEntryList): ExcludesNameEntry
    {
        $excludesNameEntry       = new ExcludesNameEntry();
        $excludesNameEntryList[] = $excludesNameEntry;

        return $excludesNameEntry;
    }

    /**
     * Returns the excluded files
     *
     * @return array
     *
     * @throws IOException
     * @throws NullPointerException
     */
    public function getExcludedFiles(): array
    {
        $includes = [];

        foreach ($this->files as $file) {
            $includes[] = $file->getName();
        }

        $this->directoryScanner->setIncludes($includes);
        $this->directoryScanner->scan();

        $files    = $this->directoryScanner->getIncludedFiles();
        $dir      = $this->directoryScanner->getBasedir();
        $fileList = [];

        foreach ($files as $file) {
            $fileList[] = $dir . DIRECTORY_SEPARATOR . $file;
        }

        return $fileList;
    }

    /**
     * Returns the excluded class names
     *
     * @return array
     */
    public function getExcludedClasses(): array
    {
        $excludedClasses = [];

        foreach ($this->classes as $excludedClass) {
            $excludedClasses[] = $excludedClass->getName();
        }

        return $excludedClasses;
    }

    /**
     * Returns the excluded method names
     *
     * @return array
     */
    public function getExcludedMethods(): array
    {
        $excludedMethods = [];

        foreach ($this->methods as $excludedMethod) {
            $classAndMethod = explode('::', $excludedMethod->getName());
            $className      = $classAndMethod[0];
            $methodName     = $classAndMethod[1];

            $excludedMethods[$className][] = $methodName;
        }

        return $excludedMethods;
    }
}
