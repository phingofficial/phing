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

include_once 'phing/system/io/FileReader.php';
include_once 'phing/types/DataType.php';

/**
 * The patternset storage component. Carries all necessary data and methods
 * for the patternset stuff.
 *
 * @author   Andreas Aderhold, andi@binarycloud.com
 * @version  $Revision$
 * @package  phing.types
 */
class PatternSet extends DataType {
	protected $creators = array();
	
    private $includeList = array();
    private $excludeList = array();
    private $includesFileList = array();
    private $excludesFileList = array();

    /**
     * Makes this instance in effect a reference to another PatternSet
     * instance.
     * You must not set another attribute or nest elements inside
     * this element if you make it a reference.
     */
    function setRefid(Reference $r) {
        if (!empty($this->includeList) || !empty($this->excludeList)) {
            throw $this->tooManyAttributes();
        }
        parent::setRefid($r);
    }


    /**
    * Add a name entry on the include list
    *
    * @return PatternSetNameEntry Reference to object
    * @throws BuildException
    */
    function createInclude() {
        if ($this->isReference()) 
            throw $this->noChildrenAllowed();

		return $this->creator($this->includeList);
    }


    /**
    * Add a name entry on the include files list
    *
    * @return PatternSetNameEntry Reference to object
    * @throws BuildException
    */
    function createIncludesFile() {
        if ($this->isReference())
            throw $this->noChildrenAllowed();

		return $this->creator($this->includesFileList);
    }

    /**
    * Add a name entry on the exclude list
    *
    * @return PatternSetNameEntry Reference to object
    * @throws BuildException
    */
    function createExclude() {
        if ($this->isReference())
            throw $this->noChildrenAllowed();
        
        return $this->creator($this->excludeList);
    }

    /**
     * add a name entry on the exclude files list
     *
     * @return PatternSetNameEntry Reference to object
     * @throws BuildException
     */
    function createExcludesFile() {
        if ($this->isReference()) 
            throw $this->noChildrenAllowed();

		return $this->creator($this->excludesFileList);
    }


    /**
     * Sets the set of include patterns. Patterns may be separated by a comma
     * or a space.
     *
     * @param  string the string containing the include patterns
     * @return void
     * @throws BuildException
     */
    function setIncludes($includes) {
		if ($this->isReference()) 
            throw $this->tooManyAttributes();
    	
    	$this->createInclude()->setName($includes);
    }

    /**
     * Sets the set of exclude patterns. Patterns may be separated by a comma
     * or a space.
     *
     * @param  string the string containing the exclude patterns
     * @return void
     * @throws BuildException
     */
    function setExcludes($excludes) {
		if ($this->isReference()) 
            throw $this->tooManyAttributes();
    	
    	$this->createExclude()->setName($excludes);
    }

    /**
     * add a name entry to the given list
     *
     * @param  array List onto which the nameentry should be added
     * @return PatternSetNameEntry  Reference to the created PsetNameEntry instance
     */
    private function creator(&$list) {
    	$c = new PatternSetNameEntryCreator($list);
    	$this->creators[] = $c;
    	return $c;
    }

    /**
     * Sets the name of the file containing the includes patterns.
     *
     * @param includesFile The file to fetch the include patterns from.
     */
    function setIncludesFile($file) {
        if ($this->isReference()) 
            throw $this->tooManyAttributes();

		if ($file instanceof File) 
            $file = $file->getPath();

		$this->createIncludesFiles()->setName($file);
    }

    /**
     * Sets the name of the file containing the excludes patterns.
     *
     * @param excludesFile The file to fetch the exclude patterns from.
     */
    function setExcludesFile($file) {
		if ($this->isReference()) 
            throw $this->tooManyAttributes();

		if ($file instanceof File) 
            $file = $file->getPath();

		$this->createExcludesFile()->setName($file);
    }

    /**
     *  Reads path matching patterns from a file and adds them to the
     *  includes or excludes list
     */
    private function readPatterns(PhingFile $patternfile, &$patternlist, Project $p) {
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

        } catch (IOException $ioe)  {
            $msg = "An error occured while reading from pattern file: " . $patternfile->__toString();
            if($patternReader) $patternReader->close();
            throw new BuildException($msg, $ioe);
        }

        $patternReader->close();
    }


    /** Adds the patterns of the other instance to this set. */
    function append($other, $p) {
        if ($this->isReference()) {
            throw new BuildException("Cannot append to a reference");
        }

        $incl = $other->getIncludePatterns($p);
        if ($incl !== null) {
            foreach($incl as $incl_name) {
                $o = $this->createInclude();
                $o->setName($incl_name);
            }
        }

        $excl = $other->getExcludePatterns($p);
        if ($excl !== null) {
            foreach($excl as $excl_name) {
                $o = $this->createExclude();
                $o->setName($excl_name);
            }
        }
    }

    /** Returns the filtered include patterns. */
    function getIncludePatterns(Project $p) {
        if ($this->isReference()) {
            $o = $this->getRef($p);
            return $o->getIncludePatterns($p);
        } else {
        	$this->applyCreators();
            $this->readFiles($p);
            return $this->makeArray($this->includeList, $p);
        }
    }

    /** Returns the filtered exclude patterns. */
    function getExcludePatterns(Project $p) {
        if ($this->isReference()) {
            $o = $this->getRef($p);
            return $o->getExcludePatterns($p);
        } else {
        	$this->applyCreators();
            $this->readFiles($p);
            return $this->makeArray($this->excludeList, $p);
        }
    }

    /** helper for FileSet. */
    function hasPatterns() {
    	$this->applyCreators();
        return (boolean) count($this->includesFileList) > 0 || count($this->excludesFileList) > 0
        || count($this->includeList) > 0 || count($this->excludeList) > 0;
    }

    /**
     * Performs the check for circular references and returns the
     * referenced PatternSet.
     */
    function getRef(Project $p) {
        if (!$this->checked) {
            $stk = array();
            array_push($stk, $this);
            $this->dieOnCircularReference($stk, $p);
        }
        $o = $this->ref->getReferencedObject($p);
        if (!($o instanceof PatternSet)) {
            $msg = $this->ref->getRefId()." doesn't denote a patternset";
            throw new BuildException($msg);
        } else {
            return $o;
        }
    }

    /** Convert a array of PatternSetNameEntry elements into an array of Strings. */
    private function makeArray(&$list, Project $p) {

        if (count($list) === 0) {
            return null;
        }

        $tmpNames = array();
        foreach($list as $ne) {
            $pattern = (string) $ne->evalName($p);
            if ($pattern !== null && strlen($pattern) > 0) {
                array_push($tmpNames, $pattern);
            }
        }
        return $tmpNames;
    }

    protected function applyCreators() {
    	/* We could use DataType::parsingComplete() for this, however I'm not 
    	 * sure whether there are any clients directly using PatternSet (withour
    	 * going through a SAX parser) and so they might not make this call.
    	 */ 
    	while ($c = array_shift($this->creators))
    		$c->apply();
    }
    
    /** Read includesfile or excludesfile if not already done so. */
    private function readFiles(Project $p) {
        if (!empty($this->includesFileList)) {
            foreach($this->includesFileList as $ne) {
                $fileName = (string) $ne->evalName($p);
                if ($fileName !== null) {
                    $inclFile = $p->resolveFile($fileName);
                    if (!$inclFile->exists()) {
                        throw new BuildException("Includesfile ".$inclFile->getAbsolutePath()." not found.");
                    }
                    $this->readPatterns($inclFile, $this->includeList, $p);
                }
            }
            $this->includesFileList = array();
        }

        if (!empty($this->excludesFileList)) {
            foreach($this->excludesFileList as $ne) {
                $fileName = (string) $ne->evalName($p);
                if ($fileName !== null) {
                    $exclFile = $p->resolveFile($fileName);
                    if (!$exclFile->exists()) {
                        throw new BuildException("Excludesfile ".$exclFile->getAbsolutePath()." not found.");
                        return;
                    }
                    $this->readPatterns($exclFile, $this->excludeList, $p);
                }
            }
            $this->excludesFileList = array();
        }
    }


    function toString() {
		$this->applyCreators();
		
        // We can't compile includeList into array because, toString() does
        // not know about project:
        //
        // $includes = $this->makeArray($this->includeList, $this->project);
        // $excludes = $this->makeArray($this->excludeList, $this->project);

        if (empty($this->includeList)) {
            $includes = "empty";
        } else {
            $includes = "";
            foreach($this->includeList as $ne) {
                $includes .= $ne->toString() . ",";
            }
            $includes = rtrim($includes, ",");
        }

        if (empty($this->excludeList)) {
            $excludes = "empty";
        } else {
            $excludes = "";
            foreach($this->excludeList as $ne) {
                $excludes .= $ne->toString() . ",";
            }
            $excludes = rtrim($excludes, ",");
        }

        return "patternSet{ includes: $includes  excludes: $excludes }";
    }
}

class PatternSetNameEntryCreator {
	protected $target;
	protected $name, $ifCond, $unlessCond;
	
	public function __construct(&$target) { $this->target =& $target; }
	public function setName($name) { $this->name = $name; }
	public function setIf($cond) { $this->ifCond = $cond; }
	public function setUnless($cond) { $this->unlessCond = $cond; }

	public function apply() {
		foreach (explode(",", $this->name) as $n) {
			$n = trim($n); 
			if (!$n) continue;
			$this->target[] = $this->create($n);
		}
	}

	protected function create($name) {
		$c = new PatternSetNameEntry();
		if ($name !== null) $c->setName($name);
		if ($this->ifCond !== null) $c->setIf($this->ifCond);
		if ($this->unlessCond !== null) $c->setUnless($this->unlessCond);
		return $c;
	}
}

/**
 * "Internal" class for holding an include/exclude pattern.
 *
 * @package  phing.types
 */
class PatternSetNameEntry {

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
    public function setPattern($pattern) {
        $this->setName($pattern);
    }

    /**
     * Set the pattern text.
     * @param string $name The pattern
     */
    public function setName($name) {
        $this->name = (string) $name;
    }

    /**
     * Sets an if-condition property for this pattern to match.
     * @param string $cond
     */
    public function setIf($cond) {
        $this->ifCond = (string) $cond;
    }


    /**
     * Sets an unless-condition property for this pattern to match.
     * @param string $cond
     */
    public function setUnless($cond) {
        $this->unlessCond = (string) $cond;
    }

    /**
     * Get the pattern text.
     * @return string The pattern.
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Evaluates the pattern.
     * @return string The pattern or null if it is ruled out by a condition. 
     */
    public function evalName(Project $project) {
        return $this->valid($project) ? $this->name : null;
    }


    /**
     * Checks whether pattern should be applied based on whether the if and unless
     * properties are set in project.
     * @param Project $project
     * @return boolean
     */
    protected function valid(Project $project) {
        if ($this->ifCond !== null && $project->getProperty($this->ifCond) === null) {
            return false;
        } else if ($this->unlessCond !== null && $project->getProperty($this->unlessCond) !== null) {
            return false;
        }
        return true;
    }

    /**
     * Gets a string representation of this pattern.
     * @return string
     */
    public function toString() {
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
}
