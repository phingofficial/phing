<?php
/*
 *  $Id$
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
use Phing\Exception\BuildException;
use Phing\Io\BufferedReader;
use Phing\Io\File;
use Phing\Io\FileReader;
use Phing\Io\IOException;
use Phing\Project;


/**
 * The patternset storage component. Carries all necessary data and methods
 * for the patternset stuff.
 *
 * @author   Andreas Aderhold, andi@binarycloud.com
 * @version  $Id$
 * @package  phing.types
 */
class PatternSet extends DataType
{

    private $includeList = array();
    private $excludeList = array();
    private $creators = array();

    /**
     * Makes this instance in effect a reference to another PatternSet
     * instance.
     * You must not set another attribute or nest elements inside
     * this element if you make it a reference.
     * @param Reference $r
     * @throws \Phing\Exception\BuildException
     */
    public function setRefid(Reference $r)
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
     * @throws \Phing\Exception\BuildException
     */
    public function createInclude()
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }

        return $this->creatorValue($this->includeList);
    }

    /**
     * Add a name entry on the include files list
     *
     * @return PatternSetNameEntry Reference to object
     * @throws BuildException
     */
    public function createIncludesFile()
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }

        return $this->creatorFile($this->includeList);
    }

    /**
     * Add a name entry on the exclude list
     *
     * @return PatternSetNameEntry Reference to object
     * @throws \Phing\Exception\BuildException
     */
    public function createExclude()
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }

        return $this->creatorValue($this->excludeList);
    }

    /**
     * add a name entry on the exclude files list
     *
     * @return PatternSetNameEntry Reference to object
     * @throws \Phing\Exception\BuildException
     */
    public function createExcludesFile()
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }

        return $this->creatorFile($this->excludeList);
    }

    /**
     * Sets the set of include patterns. Patterns may be separated by a comma
     * or a space.
     *
     * @param  string $includes the string containing the include patterns
     * @return void
     * @throws \Phing\Exception\BuildException
     */
    public function setIncludes($includes)
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        /*return*/ $this->createInclude()->setName($includes);
    }

    /**
     * Sets the set of exclude patterns. Patterns may be separated by a comma
     * or a space.
     *
     * @param  string the string containing the exclude patterns
     * @return void
     * @throws \Phing\Exception\BuildException
     */
    public function setExcludes($excludes)
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        /*return*/ $this->createExclude()->setName($excludes);
    }

    /**
     * add a name entry to the given list
     *
     * @param  array List onto which the nameentry should be added
     * @return PatternSetNameEntry Reference to the created PatternSetNameEntry instance
     */
    private function creatorValue(&$list)
    {
        $c = new PatternSetNameEntryValueCreator($list);
        $this->creators[] = $c;
        return $c;
    }

    private function creatorFile(&$list)
    {
        $c = new PatternSetNameEntryFileCreator($list, $this->project);
        $this->creators[] = $c;
        return $c;
    }

    /**
     * Sets the name of the file containing the includes patterns.
     *
     * @param File|File $includesFile file to fetch the include patterns from.
     *
     * @throws \Phing\Exception\BuildException
     */
    public function setIncludesFile($file)
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }

        $this->createIncludesFile()->setName($file);
    }

    /**
     * Sets the name of the file containing the excludes patterns.
     *
     * @param File $excludesFile file to fetch the exclude patterns from.
     * @throws BuildException
     */
    public function setExcludesFile($file)
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }

        $this->createExcludesFile()->setName($file);
    }

    /**
     * Adds the patterns of the other instance to this set.
     *
     * @param $other
     * @param Project $p
     *
     * @throws \Phing\Exception\BuildException
     */
    public function append($other, $p)
    {
        if ($this->isReference()) {
            throw new BuildException("Cannot append to a reference");
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
     * @throws \Phing\Exception\BuildException
     *
     * @return array
     */
    public function getIncludePatterns(Project $p)
    {
        if ($this->isReference()) {
            $o = $this->getRef($p);

            return $o->getIncludePatterns($p);
        } else {
            $this->applyCreators();

            return $this->makeArray($this->includeList, $p);
        }
    }

    /**
     * Returns the filtered exclude patterns.
     *
     * @param Project $p
     *
     * @throws \Phing\Exception\BuildException
     *
     * @return array
     */
    public function getExcludePatterns(Project $p)
    {
        if ($this->isReference()) {
            $o = $this->getRef($p);

            return $o->getExcludePatterns($p);
        } else {
            $this->applyCreators();

            return $this->makeArray($this->excludeList, $p);
        }
    }

    /**
     * helper for FileSet.
     *
     * @return bool
     */
    public function hasPatterns()
    {
        $this->applyCreators();
        return count($this->includeList) > 0 || count($this->excludeList) > 0;
    }

    /**
     * Performs the check for circular references and returns the
     * referenced PatternSet.
     *
     * @param Project $p
     *
     * @throws \Phing\Exception\BuildException
     *
     * @return Reference
     */
    public function getRef(Project $p)
    {
        if (!$this->checked) {
            $stk = array();
            array_push($stk, $this);
            $this->dieOnCircularReference($stk, $p);
        }
        $o = $this->ref->getReferencedObject($p);
        if (!($o instanceof PatternSet)) {
            $msg = $this->ref->getRefId() . " doesn't denote a patternset";
            throw new BuildException($msg);
        } else {
            return $o;
        }
    }

    /**
     * Convert a array of PatternSetNameEntry elements into an array of Strings.
     *
     * @param array $list
     * @param Project $p
     *
     * @return array
     */
    private function makeArray(&$list, Project $p)
    {

        if (count($list) === 0) {
            return null;
        }

        $tmpNames = array();
        foreach ($list as $ne) {
            $pattern = (string) $ne->evalName($p);
            if ($pattern !== null && strlen($pattern) > 0) {
                array_push($tmpNames, $pattern);
            }
        }

        return $tmpNames;
    }

    private function applyCreators()
    {
        /* We could use DataType::parsingComplete() for this, however I'm not
         * sure whether there are any clients directly using PatternSet (withour
         * going through a SAX parser) and so they might not make this call.
         */
        while ($c = array_shift($this->creators)) {
            $c->apply();
        }
    }

    public function __toString() { return $this->toString(); }

    /**
     * @return string
     */
    public function toString()
    {
        $this->applyCreators();

        $includes = implode(", ", $this->includeList);
        $excludes = implode(", ", $this->excludeList);

        return "patternSet{
                includes: { $includes }
                excludes: { $excludes }
         }";
    }
}

abstract class PatternSetNameEntryCreatorBase
{
    protected $target;
    protected $name, $ifCond, $unlessCond;

    public function __construct(&$target)
    {
        $this->target =& $target;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setIf($cond)
    {
        $this->ifCond = $cond;
        return $this;
    }

    public function setUnless($cond)
    {
        $this->unlessCond = $cond;
        return $this;
    }

    abstract public function apply();

    protected function create($name)
    {
        $c = new PatternSetNameEntry();
        if ($name !== null) $c->setName($name);
        if ($this->ifCond !== null) $c->setIf($this->ifCond);
        if ($this->unlessCond !== null) $c->setUnless($this->unlessCond);
        return $c;
    }
}

class PatternSetNameEntryValueCreator extends PatternSetNameEntryCreatorBase
{

    public function apply()
    {
        foreach (explode(",", $this->name) as $n) {
            $n = trim($n);
            if (!$n) {
                continue;
            }
            $this->target[] = $this->create($n);
        }
    }
}

class PatternSetNameEntryFileCreator extends PatternSetNameEntryCreatorBase
{
    protected $project;

    public function __construct(&$target, Project $p)
    {
        parent::__construct($target);
        $this->project = $p;
    }

    public function setName($n)
    {
        if ($n instanceof File) {
            $n = $n->getPath();
        }
        parent::setName($n);
    }

    public function apply()
    {
        $f = $this->project->resolveFile($this->name);

        if (!$f->exists()) {
            $this->project->log('Pattern file ' . $f->getAbsolutePath() . ' not found.', Project::MSG_WARN);
            return;
        }

        $patternReader = null;
        try {
            // Get a FileReader
            $patternReader = new BufferedReader(new FileReader($f));

            // Create one NameEntry in the appropriate pattern list for each
            // line in the file.
            for (
                $line = $patternReader->readLine();
                $line !== null;
                $line = $patternReader->readLine()
            ) {
                if (!($line = trim($line))) {
                    continue;
                }
                $line = $this->project->replaceProperties($line);
                $this->target[] = $this->create($line);
            }

        } catch (IOException $ioe) {
            $msg = "An error occured while reading from pattern file: " . $f->__toString();
            if ($patternReader) {
                $patternReader->close();
            }
            throw new BuildException($msg, $ioe);
        }

        $patternReader->close();
    }


}

/**
 * "Internal" class for holding an include/exclude pattern.
 *
 * @package  phing.types
 */
class PatternSetNameEntry
{

    /**
     * The pattern.
     * @var string
     */
    private $name;

    /**
     * The if-condition property for this pattern to be applied.
     * @var string
     */
    private $ifCond;

    /**
     * The unless-condition property for this pattern to be applied.
     * @var string
     */
    private $unlessCond;

    /**
     * An alias for the setName() method.
     * @see setName()
     * @param string $pattern
     */
    public function setPattern($pattern)
    {
        return $this->setName($pattern);
    }

    /**
     * Set the pattern text.
     * @param string $name The pattern
     */
    public function setName($name)
    {
        $this->name = (string) $name;
        return $this;
    }

    /**
     * Sets an if-condition property for this pattern to match.
     * @param string $cond
     */
    public function setIf($cond)
    {
        $this->ifCond = (string) $cond;
        return $this;
    }

    /**
     * Sets an unless-condition property for this pattern to match.
     * @param string $cond
     */
    public function setUnless($cond)
    {
        $this->unlessCond = (string) $cond;
        return $this;
    }

    /**
     * Get the pattern text.
     * @return string The pattern.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Evaluates the pattern.
     * @param Project $project
     * @return string The pattern or null if it is ruled out by a condition.
     */
    public function evalName(Project $project)
    {
        return $this->valid($project) ? $this->name : null;
    }

    /**
     * Checks whether pattern should be applied based on whether the if and unless
     * properties are set in project.
     * @param  Project $project
     * @return boolean
     */
    protected function valid(Project $project)
    {
        if ($this->ifCond !== null && $project->getProperty($this->ifCond) === null) {
            return false;
        } else {
            if ($this->unlessCond !== null && $project->getProperty($this->unlessCond) !== null) {
                return false;
            }
        }

        return true;
    }

    /**
     * Gets a string representation of this pattern.
     * @return string
     */
    public function toString()
    {
        $buf = $this->name;
        if (($this->ifCond !== null) || ($this->unlessCond !== null)) {
            $buf .= ":";
            $connector = "";

            if ($this->ifCond !== null) {
                $buf .= "if->{$this->ifCond}";
                $connector = ";";
            }
            if ($this->unlessCond !== null) {
                $buf .= "$connector unless->{$this->unlessCond}";
            }
        }

        return $buf;
    }

    public function __toString()
    {
        return $this->toString();
    }
}
