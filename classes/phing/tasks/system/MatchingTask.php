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
 * This is an abstract task that should be used by all those tasks that
 * require to include or exclude files based on pattern matching.
 *
 * This is very closely based on the ANT class of the same name.
 *
 * @author  Hans Lellelid <hans@xmpl.org> (Phing)
 * @author  Arnout J. Kuiper <ajkuiper@wxs.nl> (Ant)
 * @author  Stefano Mazzocchi  <stefano@apache.org> (Ant)
 * @author  Sam Ruby <rubys@us.ibm.com> (Ant)
 * @author  Jon S. Stevens <jon@clearink.com> (Ant
 * @author  Stefan Bodewig <stefan.bodewig@epost.de> (Ant)
 * @author  Bruce Atherton <bruce@callenish.com> (Ant)
 * @package phing.tasks.system
 */
abstract class MatchingTask extends Task implements SelectorContainer
{
    /**
     * @var bool
     */
    protected $useDefaultExcludes = true;

    /**
     * @var FileSet
     */
    protected $fileset;

    /**
     * Create instance; set fileset to new FileSet.
     */
    public function __construct()
    {
        parent::__construct();
        $this->fileset = new FileSet();
    }

    /**
     * @see ProjectComponent::setProject()
     *
     * @param Project|null $project
     *
     * @return void
     */
    public function setProject(?Project $project): void
    {
        parent::setProject($project);
        $this->fileset->setProject($project);
    }

    /**
     * add a name entry on the include list
     *
     * @return PatternSetNameEntry
     */
    public function createInclude(): PatternSetNameEntry
    {
        return $this->fileset->createInclude();
    }

    /**
     * add a name entry on the include files list
     *
     * @return PatternSetNameEntry
     */
    public function createIncludesFile(): PatternSetNameEntry
    {
        return $this->fileset->createIncludesFile();
    }

    /**
     * add a name entry on the exclude list
     *
     * @return PatternSetNameEntry
     */
    public function createExclude(): PatternSetNameEntry
    {
        return $this->fileset->createExclude();
    }

    /**
     * add a name entry on the include files list
     *
     * @return PatternSetNameEntry
     */
    public function createExcludesFile(): PatternSetNameEntry
    {
        return $this->fileset->createExcludesFile();
    }

    /**
     * add a set of patterns
     *
     * @return PatternSet
     */
    public function createPatternSet(): PatternSet
    {
        return $this->fileset->createPatternSet();
    }

    /**
     * Sets the set of include patterns. Patterns may be separated by a comma
     * or a space.
     *
     * @param string $includes the string containing the include patterns
     *
     * @return void
     */
    public function setIncludes(string $includes): void
    {
        $this->fileset->setIncludes($includes);
    }

    /**
     * Sets the set of exclude patterns. Patterns may be separated by a comma
     * or a space.
     *
     * @param string $excludes the string containing the exclude patterns
     *
     * @return void
     */
    public function setExcludes(string $excludes): void
    {
        $this->fileset->setExcludes($excludes);
    }

    /**
     * Sets whether default exclusions should be used or not.
     *
     * @param bool $useDefaultExcludes "true"|"on"|"yes" when default exclusions
     *                                 should be used, "false"|"off"|"no" when they
     *                                 shouldn't be used.
     *
     * @return void
     */
    public function setDefaultexcludes(bool $useDefaultExcludes): void
    {
        $this->useDefaultExcludes = $useDefaultExcludes;
    }

    /**
     * Returns the directory scanner needed to access the files to process.
     *
     * @param PhingFile $baseDir
     *
     * @return DirectoryScanner
     *
     * @throws BuildException
     * @throws IOException
     * @throws NullPointerException
     * @throws ReflectionException
     */
    protected function getDirectoryScanner(PhingFile $baseDir): DirectoryScanner
    {
        $this->fileset->setDir($baseDir);
        $this->fileset->setDefaultexcludes($this->useDefaultExcludes);

        return $this->fileset->getDirectoryScanner($this->project);
    }

    /**
     * Sets the name of the file containing the includes patterns.
     *
     * @param PhingFile $includesfile A string containing the filename to fetch
     *                                 the include patterns from.
     *
     * @return void
     */
    public function setIncludesfile(PhingFile $includesfile): void
    {
        $this->fileset->setIncludesfile($includesfile);
    }

    /**
     * Sets the name of the file containing the includes patterns.
     *
     * @param PhingFile $excludesfile A string containing the filename to fetch
     *                                 the include patterns from.
     *
     * @return void
     */
    public function setExcludesfile(PhingFile $excludesfile): void
    {
        $this->fileset->setExcludesfile($excludesfile);
    }

    /**
     * Sets case sensitivity of the file system
     *
     * @param bool $isCaseSensitive "true"|"on"|"yes" if file system is case
     *                              sensitive, "false"|"off"|"no" when not.
     *
     * @return void
     */
    public function setCaseSensitive(bool $isCaseSensitive): void
    {
        $this->fileset->setCaseSensitive($isCaseSensitive);
    }

    /**
     * Sets whether or not symbolic links should be followed.
     *
     * @param bool $followSymlinks whether or not symbolic links should be followed
     *
     * @return void
     */
    public function setFollowSymlinks(bool $followSymlinks): void
    {
        $this->fileset->setExpandSymbolicLinks($followSymlinks);
    }

    /**
     * Indicates whether there are any selectors here.
     *
     * @return bool Whether any selectors are in this container
     *
     * @throws ReflectionException
     */
    public function hasSelectors(): bool
    {
        return $this->fileset->hasSelectors();
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
        return $this->fileset->count();
    }

    /**
     * Returns the set of selectors as an array.
     *
     * @param Project $p
     *
     * @return BaseSelectorContainer[] An array of selectors in this container
     *
     * @throws ReflectionException
     */
    public function getSelectors(Project $p): array
    {
        return $this->fileset->getSelectors($p);
    }

    /**
     * Returns an enumerator for accessing the set of selectors.
     *
     * @return BaseSelectorContainer[] an enumerator that goes through each of the selectors
     *
     * @throws ReflectionException
     */
    public function selectorElements(): array
    {
        return $this->fileset->selectorElements();
    }

    /**
     * Add a new selector into this container.
     *
     * @param FileSelector $selector the new selector to add
     *
     * @return void
     */
    public function appendSelector(FileSelector $selector): void
    {
        $this->fileset->appendSelector($selector);
    }

    /* Methods below all add specific selectors */

    /**
     * add a "Select" selector entry on the selector list
     *
     * @param SelectSelector $selector
     *
     * @return void
     */
    public function addSelector(SelectSelector $selector): void
    {
        $this->fileset->addSelector($selector);
    }

    /**
     * add an "And" selector entry on the selector list
     *
     * @param AndSelector $selector
     *
     * @return void
     */
    public function addAnd(AndSelector $selector): void
    {
        $this->fileset->addAnd($selector);
    }

    /**
     * add an "Or" selector entry on the selector list
     *
     * @param OrSelector $selector
     *
     * @return void
     */
    public function addOr(OrSelector $selector): void
    {
        $this->fileset->addOr($selector);
    }

    /**
     * add a "Not" selector entry on the selector list
     *
     * @param NotSelector $selector
     *
     * @return void
     */
    public function addNot(NotSelector $selector): void
    {
        $this->fileset->addNot($selector);
    }

    /**
     * add a "None" selector entry on the selector list
     *
     * @param NoneSelector $selector
     *
     * @return void
     */
    public function addNone(NoneSelector $selector): void
    {
        $this->fileset->addNone($selector);
    }

    /**
     * add a majority selector entry on the selector list
     *
     * @param MajoritySelector $selector
     *
     * @return void
     */
    public function addMajority(MajoritySelector $selector): void
    {
        $this->fileset->addMajority($selector);
    }

    /**
     * add a selector date entry on the selector list
     *
     * @param DateSelector $selector
     *
     * @return void
     */
    public function addDate(DateSelector $selector): void
    {
        $this->fileset->addDate($selector);
    }

    /**
     * add a selector size entry on the selector list
     *
     * @param SizeSelector $selector
     *
     * @return void
     */
    public function addSize(SizeSelector $selector): void
    {
        $this->fileset->addSize($selector);
    }

    /**
     * add a selector filename entry on the selector list
     *
     * @param FilenameSelector $selector
     *
     * @return void
     */
    public function addFilename(FilenameSelector $selector): void
    {
        $this->fileset->addFilename($selector);
    }

    /**
     * add an extended selector entry on the selector list
     *
     * @param ExtendSelector $selector
     *
     * @return void
     */
    public function addCustom(ExtendSelector $selector): void
    {
        $this->fileset->addCustom($selector);
    }

    /**
     * add a contains selector entry on the selector list
     *
     * @param ContainsSelector $selector
     *
     * @return void
     */
    public function addContains(ContainsSelector $selector): void
    {
        $this->fileset->addContains($selector);
    }

    /**
     * add a present selector entry on the selector list
     *
     * @param PresentSelector $selector
     *
     * @return void
     */
    public function addPresent(PresentSelector $selector): void
    {
        $this->fileset->addPresent($selector);
    }

    /**
     * add a depth selector entry on the selector list
     *
     * @param DepthSelector $selector
     *
     * @return void
     */
    public function addDepth(DepthSelector $selector): void
    {
        $this->fileset->addDepth($selector);
    }

    /**
     * add a depends selector entry on the selector list
     *
     * @param DependSelector $selector
     *
     * @return void
     */
    public function addDepend(DependSelector $selector): void
    {
        $this->fileset->addDepend($selector);
    }

    /**
     * add a executable selector entry on the selector list
     *
     * @param ExecutableSelector $selector
     *
     * @return void
     */
    public function addExecutable(ExecutableSelector $selector): void
    {
        $this->fileset->addExecutable($selector);
    }

    /**
     * add a readable selector entry on the selector list
     *
     * @param ReadableSelector $selector
     *
     * @return void
     */
    public function addReadable(ReadableSelector $selector): void
    {
        $this->fileset->addReadable($selector);
    }

    /**
     * add a writable selector entry on the selector list
     *
     * @param WritableSelector $selector
     *
     * @return void
     */
    public function addWritable(WritableSelector $selector): void
    {
        $this->fileset->addWritable($selector);
    }

    /**
     * add a different selector entry on the selector list
     *
     * @param DifferentSelector $selector
     *
     * @return void
     */
    public function addDifferent(DifferentSelector $selector): void
    {
        $this->fileset->addDifferent($selector);
    }

    /**
     * add a type selector entry on the selector list
     *
     * @param TypeSelector $selector
     *
     * @return void
     */
    public function addType(TypeSelector $selector): void
    {
        $this->fileset->addType($selector);
    }

    /**
     * add a contains selector entry on the selector list
     *
     * @param ContainsRegexpSelector $selector
     *
     * @return void
     */
    public function addContainsRegexp(ContainsRegexpSelector $selector): void
    {
        $this->fileset->addContainsRegexp($selector);
    }

    /**
     * @param SymlinkSelector $selector
     *
     * @return void
     */
    public function addSymlink(SymlinkSelector $selector): void
    {
        $this->fileset->addSymlink($selector);
    }

    /**
     * Accessor for the implict fileset.
     *
     * @return FileSet
     */
    protected function getImplicitFileSet(): Fileset
    {
        return $this->fileset;
    }
}
