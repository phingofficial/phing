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
 * The patternset storage component. Carries all necessary data and methods
 * for the patternset stuff.
 *
 * @author  Andreas Aderhold, andi@binarycloud.com
 * @package phing.types
 */
class PatternSet extends DataType
{
    private $includeList      = [];
    private $excludeList      = [];
    private $includesFileList = [];
    private $excludesFileList = [];

    /**
     * Makes this instance in effect a reference to another PatternSet
     * instance.
     * You must not set another attribute or nest elements inside
     * this element if you make it a reference.
     *
     * @param Reference $r
     *
     * @return void
     *
     * @throws BuildException
     */
    public function setRefid(Reference $r): void
    {
        if (!empty($this->includeList) || !empty($this->excludeList)) {
            throw $this->tooManyAttributes();
        }
        parent::setRefid($r);
    }

    /**
     * Add a name entry on the include list
     *
     * @return PatternSetNameEntry Reference to object
     *
     * @throws BuildException
     */
    public function createInclude(): PatternSetNameEntry
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }

        return $this->addPatternToList($this->includeList);
    }

    /**
     * Add a name entry on the include files list
     *
     * @return PatternSetNameEntry Reference to object
     *
     * @throws BuildException
     */
    public function createIncludesFile(): PatternSetNameEntry
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }

        return $this->addPatternToList($this->includesFileList);
    }

    /**
     * Add a name entry on the exclude list
     *
     * @return PatternSetNameEntry Reference to object
     *
     * @throws BuildException
     */
    public function createExclude(): PatternSetNameEntry
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }

        return $this->addPatternToList($this->excludeList);
    }

    /**
     * add a name entry on the exclude files list
     *
     * @return PatternSetNameEntry Reference to object
     *
     * @throws BuildException
     */
    public function createExcludesFile(): PatternSetNameEntry
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }

        return $this->addPatternToList($this->excludesFileList);
    }

    /**
     * Sets the set of include patterns. Patterns may be separated by a comma
     * or a space.
     *
     * @param string $includes the string containing the include patterns
     *
     * @return void
     *
     * @throws BuildException
     */
    public function setIncludes(string $includes): void
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        if ($includes !== null && strlen($includes) > 0) {
            $tok = strtok($includes, ', ');
            while ($tok !== false) {
                $o = $this->createInclude();
                $o->setName($tok);
                $tok = strtok(', ');
            }
        }
    }

    /**
     * Sets the set of exclude patterns. Patterns may be separated by a comma
     * or a space.
     *
     * @param string $excludes the string containing the exclude patterns
     *
     * @return void
     *
     * @throws BuildException
     */
    public function setExcludes(string $excludes): void
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        if ($excludes !== null && strlen($excludes) > 0) {
            $tok = strtok($excludes, ', ');
            while ($tok !== false) {
                $o = $this->createExclude();
                $o->setName($tok);
                $tok = strtok(', ');
            }
        }
    }

    /**
     * add a name entry to the given list
     *
     * @param PatternSetNameEntry[] $list List onto which the nameentry should be added
     *
     * @return PatternSetNameEntry Reference to the created PsetNameEntry instance
     */
    private function addPatternToList(array &$list): PatternSetNameEntry
    {
        $num = array_push($list, new PatternSetNameEntry());

        return $list[$num - 1];
    }

    /**
     * Sets the name of the file containing the includes patterns.
     *
     * @param PhingFile $includesFile file to fetch the include patterns from.
     *
     * @return void
     *
     * @throws BuildException
     */
    public function setIncludesFile(PhingFile $includesFile): void
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        if ($includesFile instanceof PhingFile) {
            $includesFile = $includesFile->getPath();
        }
        $o = $this->createIncludesFile();
        $o->setName($includesFile);
    }

    /**
     * Sets the name of the file containing the excludes patterns.
     *
     * @param PhingFile $excludesFile file to fetch the exclude patterns from.
     *
     * @return void
     *
     * @throws BuildException
     */
    public function setExcludesFile(PhingFile $excludesFile): void
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        if ($excludesFile instanceof PhingFile) {
            $excludesFile = $excludesFile->getPath();
        }
        $o = $this->createExcludesFile();
        $o->setName($excludesFile);
    }

    /**
     * Reads path matching patterns from a file and adds them to the
     * includes or excludes list
     *
     * @param PhingFile             $patternfile
     * @param PatternSetNameEntry[] $patternlist
     * @param Project               $p
     *
     * @return void
     *
     * @throws BuildException
     * @throws IOException
     */
    private function readPatterns(PhingFile $patternfile, array &$patternlist, Project $p): void
    {
        $patternReader = null;
        try {
            // Get a FileReader
            $patternReader = new BufferedReader(new FileReader($patternfile));

            // Create one NameEntry in the appropriate pattern list for each
            // line in the file.
            $line = $patternReader->readLine();
            while ($line !== null) {
                if (!empty($line)) {
                    $line = $p->replaceProperties($line);
                    $this->addPatternToList($patternlist)->setName($line);
                }
                $line = $patternReader->readLine();
            }
        } catch (IOException $ioe) {
            $msg = 'An error occurred while reading from pattern file: ' . $patternfile->__toString();
            if ($patternReader) {
                $patternReader->close();
            }
            throw new BuildException($msg, $ioe);
        }

        $patternReader->close();
    }

    /**
     * Adds the patterns of the other instance to this set.
     *
     * @param PatternSet $other
     * @param Project    $p
     *
     * @return void
     *
     * @throws IOException
     * @throws BuildException
     */
    public function append(PatternSet $other, Project $p): void
    {
        if ($this->isReference()) {
            throw new BuildException('Cannot append to a reference');
        }

        $incl = $other->getIncludePatterns($p);
        if ($incl !== null) {
            foreach ($incl as $incl_name) {
                $o = $this->createInclude();
                $o->setName($incl_name);
            }
        }

        $excl = $other->getExcludePatterns($p);
        if ($excl !== null) {
            foreach ($excl as $excl_name) {
                $o = $this->createExclude();
                $o->setName($excl_name);
            }
        }
    }

    /**
     * Returns the filtered include patterns.
     *
     * @param Project $p
     *
     * @return array|null
     *
     * @throws IOException
     * @throws BuildException
     */
    public function getIncludePatterns(Project $p): ?array
    {
        if ($this->isReference()) {
            $o = $this->getRef($p);

            return $o->getIncludePatterns($p);
        }

        $this->readFiles($p);

        return $this->makeArray($this->includeList, $p);
    }

    /**
     * Returns the filtered exclude patterns.
     *
     * @param Project $p
     *
     * @return array|null
     *
     * @throws IOException
     * @throws BuildException
     */
    public function getExcludePatterns(Project $p): ?array
    {
        if ($this->isReference()) {
            $o = $this->getRef($p);

            return $o->getExcludePatterns($p);
        }

        $this->readFiles($p);

        return $this->makeArray($this->excludeList, $p);
    }

    /**
     * helper for FileSet.
     *
     * @return bool
     */
    public function hasPatterns(): bool
    {
        return (bool) count($this->includesFileList) > 0 || count($this->excludesFileList) > 0
            || count($this->includeList) > 0 || count($this->excludeList) > 0;
    }

    /**
     * Performs the check for circular references and returns the
     * referenced PatternSet.
     *
     * @param Project $p
     *
     * @return Reference
     *
     * @throws BuildException
     */
    public function getRef(Project $p): Reference
    {
        $dataTypeName = StringHelper::substring(self::class, strrpos(self::class, '\\') + 1);
        return $this->getCheckedRef(self::class, $dataTypeName);
    }

    /**
     * Convert a array of PatternSetNameEntry elements into an array of Strings.
     *
     * @param array   $list
     * @param Project $p
     *
     * @return array|null
     */
    private function makeArray(array &$list, Project $p): ?array
    {
        if (count($list) === 0) {
            return null;
        }

        $tmpNames = [];
        foreach ($list as $ne) {
            $pattern = (string) $ne->evalName($p);
            if ($pattern !== null && strlen($pattern) > 0) {
                $tmpNames[] = $pattern;
            }
        }

        return $tmpNames;
    }

    /**
     * Read includesfile or excludesfile if not already done so.
     *
     * @param Project $p
     *
     * @return void
     *
     * @throws IOException
     * @throws BuildException
     */
    private function readFiles(Project $p): void
    {
        if (!empty($this->includesFileList)) {
            foreach ($this->includesFileList as $ne) {
                $fileName = (string) $ne->evalName($p);
                if ($fileName !== null) {
                    $inclFile = $p->resolveFile($fileName);
                    if (!$inclFile->exists()) {
                        throw new BuildException('Includesfile ' . $inclFile->getAbsolutePath() . ' not found.');
                    }
                    $this->readPatterns($inclFile, $this->includeList, $p);
                }
            }
            $this->includesFileList = [];
        }

        if (!empty($this->excludesFileList)) {
            foreach ($this->excludesFileList as $ne) {
                $fileName = (string) $ne->evalName($p);
                if ($fileName !== null) {
                    $exclFile = $p->resolveFile($fileName);
                    if (!$exclFile->exists()) {
                        throw new BuildException('Excludesfile ' . $exclFile->getAbsolutePath() . ' not found.');
                    }
                    $this->readPatterns($exclFile, $this->excludeList, $p);
                }
            }
            $this->excludesFileList = [];
        }
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        // We can't compile includeList into array because, toString() does
        // not know about project:
        //
        // $includes = $this->makeArray($this->includeList, $this->project);
        // $excludes = $this->makeArray($this->excludeList, $this->project);

        if (empty($this->includeList)) {
            $includes = 'empty';
        } else {
            $includes = '';
            foreach ($this->includeList as $ne) {
                $includes .= (string) $ne . ',';
            }
            $includes = rtrim($includes, ',');
        }

        if (empty($this->excludeList)) {
            $excludes = 'empty';
        } else {
            $excludes = '';
            foreach ($this->excludeList as $ne) {
                $excludes .= (string) $ne . ',';
            }
            $excludes = rtrim($excludes, ',');
        }

        return sprintf('patternSet{ includes: %s  excludes: %s }', $includes, $excludes);
    }
}
