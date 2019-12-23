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
 * The FileSet class provides methods and properties for accessing
 * and managing filesets. It extends ProjectComponent and thus inherits
 * all methods and properties (not explicitly declared). See ProjectComponent
 * for further detail.
 *
 * TODO:
 *   - merge this with patternsets: FileSet extends PatternSet !!!
 *     requires additional mods to the parsing algo
 *         [HL] .... not sure if that really makes so much sense.  I think
 *            that perhaps they should use common utility class if there really
 *            is that much shared functionality
 *
 * @see     ProjectComponent
 *
 * @author  Andreas Aderhold <andi@binarycloud.com>
 * @author  Hans Lellelid <hans@xmpl.org>
 * @package phing.types
 */
abstract class AbstractFileSet extends DataType implements SelectorContainer, IteratorAggregate
{
    use SelectorAware;

    // These vars are public for cloning purposes

    /**
     * @var bool
     */
    public $useDefaultExcludes = true;

    /**
     * Whether to expand/dereference symbolic links, default is false
     *
     * @var bool
     */
    protected $expandSymbolicLinks = false;

    /**
     * @var PatternSet
     */
    public $defaultPatterns;

    public $additionalPatterns = [];

    /**
     * @var PhingFile
     */
    public $dir;

    /**
     * @var bool
     */
    public $isCaseSensitive = true;

    /**
     * @var bool
     */
    private $errorOnMissingDir = false;

    /**
     * @var
     */
    private $directoryScanner;

    /**
     * @param FileSet|null $fileset
     */
    public function __construct(?FileSet $fileset = null)
    {
        parent::__construct();

        if ($fileset !== null && ($fileset instanceof FileSet)) {
            $this->dir                 = $fileset->dir;
            $this->additionalPatterns  = $fileset->additionalPatterns;
            $this->useDefaultExcludes  = $fileset->useDefaultExcludes;
            $this->isCaseSensitive     = $fileset->isCaseSensitive;
            $this->selectorsList       = $fileset->selectorsList;
            $this->expandSymbolicLinks = $fileset->expandSymbolicLinks;
            $this->errorOnMissingDir   = $fileset->errorOnMissingDir;
            $this->setProject($fileset->getProject());
        }

        $this->defaultPatterns = new PatternSet();
    }

    /**
     * Sets whether to expand/dereference symbolic links, default is false
     *
     * @param bool $expandSymbolicLinks
     *
     * @return void
     */
    public function setExpandSymbolicLinks(bool $expandSymbolicLinks): void
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $this->expandSymbolicLinks = $expandSymbolicLinks;
    }

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
        if ((isset($this->dir) && null !== $this->dir) || $this->defaultPatterns->hasPatterns()) {
            throw $this->tooManyAttributes();
        }
        if (!empty($this->additionalPatterns)) {
            throw $this->noChildrenAllowed();
        }
        if (!empty($this->selectorsList)) {
            throw $this->noChildrenAllowed();
        }
        parent::setRefid($r);
    }

    /**
     * @param PhingFile|string $dir
     *
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     */
    public function setDir($dir): void
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        if ($dir instanceof PhingFile) {
            $dir = $dir->getPath();
        }
        $this->dir              = new PhingFile((string) $dir);
        $this->directoryScanner = null;
    }

    /**
     * @param Project|null $p
     *
     * @return string|PhingFile
     *
     * @throws BuildException
     * @throws ReflectionException
     */
    public function getDir(?Project $p = null)
    {
        if ($p === null) {
            $p = $this->getProject();
        }

        if ($this->isReference()) {
            return $this->getRef($p)->getDir($p);
        }

        return $this->dir;
    }

    /**
     * @return PatternSet
     *
     * @throws BuildException
     */
    public function createPatternSet(): PatternSet
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }
        $num = array_push($this->additionalPatterns, new PatternSet());

        return $this->additionalPatterns[$num - 1];
    }

    /**
     * add a name entry on the include list
     *
     * @return PatternSetNameEntry
     */
    public function createInclude(): PatternSetNameEntry
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }

        return $this->defaultPatterns->createInclude();
    }

    /**
     * add a name entry on the include files list
     *
     * @return PatternSetNameEntry
     */
    public function createIncludesFile(): PatternSetNameEntry
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }

        return $this->defaultPatterns->createIncludesFile();
    }

    /**
     * add a name entry on the exclude list
     *
     * @return PatternSetNameEntry
     */
    public function createExclude(): PatternSetNameEntry
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }

        return $this->defaultPatterns->createExclude();
    }

    /**
     * add a name entry on the include files list
     *
     * @return PatternSetNameEntry
     */
    public function createExcludesFile(): PatternSetNameEntry
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }

        return $this->defaultPatterns->createExcludesFile();
    }

    /**
     * @param PhingFile $file
     *
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     */
    public function setFile(PhingFile $file): void
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $this->setDir($file->getParentFile());
        $this->createInclude()->setName($file->getName());
    }

    /**
     * Sets the set of include patterns. Patterns may be separated by a comma
     * or a space.
     *
     * @param string $includes
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
        $this->defaultPatterns->setIncludes($includes);
    }

    /**
     * Sets the set of exclude patterns. Patterns may be separated by a comma
     * or a space.
     *
     * @param string $excludes
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
        $this->defaultPatterns->setExcludes($excludes);
    }

    /**
     * Sets the name of the file containing the includes patterns.
     *
     * @param PhingFile $incl The file to fetch the include patterns from.
     *
     * @return void
     *
     * @throws BuildException
     */
    public function setIncludesfile(PhingFile $incl): void
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $this->defaultPatterns->setIncludesFile($incl);
    }

    /**
     * Sets the name of the file containing the includes patterns.
     *
     * @param PhingFile $excl The file to fetch the exclude patterns from.
     *
     * @return void
     *
     * @throws BuildException
     */
    public function setExcludesfile(PhingFile $excl): void
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $this->defaultPatterns->setExcludesFile($excl);
    }

    /**
     * Sets whether default exclusions should be used or not.
     *
     * @param bool $useDefaultExcludes "true"|"on"|"yes" when default exclusions
     *                                 should be used, "false"|"off"|"no" when they
     *                                 shouldn't be used.
     *
     * @return void
     *
     * @throws BuildException
     */
    public function setDefaultexcludes(bool $useDefaultExcludes): void
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $this->useDefaultExcludes = $useDefaultExcludes;
    }

    /**
     * Sets case sensitivity of the file system
     *
     * @param bool $isCaseSensitive
     *
     * @return void
     */
    public function setCaseSensitive(bool $isCaseSensitive): void
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $this->isCaseSensitive = $isCaseSensitive;
    }

    /**
     * returns a reference to the dirscanner object belonging to this fileset
     *
     * @param Project|null $p
     *
     * @return DirectoryScanner
     *
     * @throws IOException
     * @throws NullPointerException
     * @throws ReflectionException
     * @throws BuildException
     */
    public function getDirectoryScanner(?Project $p = null): DirectoryScanner
    {
        if ($p === null) {
            $p = $this->getProject();
        }

        if ($this->isReference()) {
            $o = $this->getRef($p);

            return $o->getDirectoryScanner($p);
        }

        if ($this->dir === null) {
            throw new BuildException(sprintf('No directory specified for <%s>.', $this->getDataTypeName()));
        }
        if (!$this->dir->exists() && $this->errorOnMissingDir) {
            throw new BuildException('Directory ' . $this->dir->getAbsolutePath() . ' not found.');
        }
        if (!$this->dir->isLink() || !$this->expandSymbolicLinks) {
            if (!$this->dir->isDirectory()) {
                throw new BuildException($this->dir->getAbsolutePath() . ' is not a directory.');
            }
        }
        $ds = new DirectoryScanner();
        $ds->setExpandSymbolicLinks($this->expandSymbolicLinks);
        $ds->setErrorOnMissingDir($this->errorOnMissingDir);
        $this->setupDirectoryScanner($ds, $p);
        $ds->scan();

        return $ds;
    }

    /**
     * feed dirscanner with infos defined by this fileset
     *
     * @param DirectoryScanner $ds
     * @param Project|null     $p
     *
     * @return void
     *
     * @throws ReflectionException
     * @throws Exception
     * @throws IOException
     * @throws BuildException
     */
    protected function setupDirectoryScanner(DirectoryScanner $ds, ?Project $p = null): void
    {
        if ($p === null) {
            $p = $this->getProject();
        }

        if ($this->isReference()) {
            $this->getRef($p)->setupDirectoryScanner($ds, $p);
            return;
        }

        $stk[] = $this;
        $this->dieOnCircularReference($stk, $p);
        array_pop($stk);

        // FIXME - pass dir directly when dirscanner supports File
        $ds->setBasedir($this->dir->getPath());

        foreach ($this->additionalPatterns as $addPattern) {
            $this->defaultPatterns->append($addPattern, $p);
        }

        $ds->setIncludes($this->defaultPatterns->getIncludePatterns($p));
        $ds->setExcludes($this->defaultPatterns->getExcludePatterns($p));

        $p->log(
            $this->getDataTypeName() . ': Setup file scanner in dir ' . (string) $this->dir . ' with ' . (string) $this->defaultPatterns,
            Project::MSG_DEBUG
        );

        if ($ds instanceof SelectorScanner) {
            $selectors = $this->getSelectors($p);
            foreach ($selectors as $selector) {
                $p->log((string) $selector . PHP_EOL, Project::MSG_DEBUG);
            }
            $ds->setSelectors($selectors);
        }

        if ($this->useDefaultExcludes) {
            $ds->addDefaultExcludes();
        }
        $ds->setCaseSensitive($this->isCaseSensitive);
    }

    /**
     * @param array        $stk
     * @param Project|null $p
     *
     * @return void
     */
    public function dieOnCircularReference(array &$stk, ?Project $p = null): void
    {
        if ($this->checked) {
            return;
        }
        if ($this->isReference()) {
            parent::dieOnCircularReference($stk, $p);
        } else {
            foreach ($this->selectorsList as $fileSelector) {
                if ($fileSelector instanceof DataType) {
                    static::pushAndInvokeCircularReferenceCheck($fileSelector, $stk, $p);
                }
            }
            foreach ($this->additionalPatterns as $ps) {
                static::pushAndInvokeCircularReferenceCheck($ps, $stk, $p);
            }
            $this->setChecked(true);
        }
    }

    /**
     * Performs the check for circular references and returns the
     * referenced FileSet.
     *
     * @param Project $p
     *
     * @return FileSet
     *
     * @throws ReflectionException
     * @throws BuildException
     */
    public function getRef(Project $p)
    {
        return $this->getCheckedRef(self::class, $this->getDataTypeName());
    }

    // SelectorContainer methods

    /**
     * Indicates whether there are any selectors here.
     *
     * @return bool Whether any selectors are in this container
     *
     * @throws ReflectionException
     */
    public function hasSelectors(): bool
    {
        if ($this->isReference() && $this->getProject() !== null) {
            return $this->getRef($this->getProject())->hasSelectors();
        }
        $stk[] = $this;
        $this->dieOnCircularReference($stk, $this->getProject());

        return !empty($this->selectorsList);
    }

    /**
     * Indicates whether there are any patterns here.
     *
     * @return bool Whether any patterns are in this container.
     *
     * @throws ReflectionException
     */
    public function hasPatterns(): bool
    {
        if ($this->isReference() && $this->getProject() !== null) {
            return $this->getRef($this->getProject())->hasPatterns();
        }
        $stk[] = $this;
        $this->dieOnCircularReference($stk, $this->getProject());

        if ($this->defaultPatterns->hasPatterns()) {
            return true;
        }

        for ($i = 0, $size = count($this->additionalPatterns); $i < $size; $i++) {
            $ps = $this->additionalPatterns[$i];
            if ($ps->hasPatterns()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gives the count of the number of selectors in this container
     *
     * @return int The number of selectors in this container
     *
     * @throws Exception
     */
    public function count(): int
    {
        if ($this->isReference() && $this->getProject() !== null) {
            try {
                return $this->getRef($this->getProject())->count();
            } catch (Throwable $e) {
                throw $e;
            }
        }

        return count($this->selectorsList);
    }

    /**
     * Returns the set of selectors as an array.
     *
     * @param Project $p
     *
     * @return BaseSelectorContainer[] of selectors in this container
     *
     * @throws ReflectionException
     * @throws BuildException
     */
    public function getSelectors(Project $p): array
    {
        if ($this->isReference()) {
            return $this->getRef($p)->getSelectors($p);
        }

// *copy* selectors
        $result = [];
        for ($i = 0, $size = count($this->selectorsList); $i < $size; $i++) {
            $result[] = clone $this->selectorsList[$i];
        }

        return $result;
    }

    /**
     * Returns an array for accessing the set of selectors.
     *
     * @return BaseSelectorContainer[] The array of selectors
     *
     * @throws ReflectionException
     */
    public function selectorElements(): array
    {
        if ($this->isReference() && $this->getProject() !== null) {
            return $this->getRef($this->getProject())->selectorElements();
        }

        return $this->selectorsList;
    }

    /**
     * Add a new selector into this container.
     *
     * @param FileSelector $selector new selector to add
     *
     * @return void
     *
     * @throws BuildException
     */
    public function appendSelector(FileSelector $selector): void
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }
        $this->selectorsList[]  = $selector;
        $this->directoryScanner = null;
        $this->setChecked(false);
    }

    /**
     * @param array ...$options
     *
     * @return ArrayIterator
     *
     * @throws ReflectionException
     */
    public function getIterator(...$options): ArrayIterator
    {
        if ($this->isReference()) {
            return $this->getRef($this->getProject())->getIterator($options);
        }
        return new ArrayIterator($this->getFiles());
    }

    /**
     * @return array
     */
    abstract protected function getFiles(): array;

    /**
     * @return string
     *
     * @throws IOException
     * @throws NullPointerException
     * @throws ReflectionException
     */
    public function __toString(): string
    {
        try {
            if ($this->isReference()) {
                return (string) $this->getRef($this->getProject());
            }
            $stk[] = $this;
            $this->dieOnCircularReference($stk, $this->getProject());
            $ds     = $this->getDirectoryScanner($this->getProject());
            $files  = $ds->getIncludedFiles();
            $result = implode(';', $files);
        } catch (BuildException $e) {
            $result = '';
        }

        return $result;
    }
}
