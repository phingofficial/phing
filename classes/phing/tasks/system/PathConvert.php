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

require_once 'phing/Task.php';
include_once 'phing/types/Path.php';
include_once 'phing/BuildException.php';

/**
 * Converts path and classpath information to a specific target OS
 * format. The resulting formatted path is placed into the specified property.
 *
 * @author   Siad Ardroumli <siad.ardroumli@gmail.com>
 *
 * @package  phing.tasks.system
 */
class PathConvert extends Task
{
    // Members
    /**
     * Path to be converted
     */
    private $path = null;
    /**
     * Reference to path/fileset to convert
     * @var Reference $refid
     */
    private $refid = null;
    /**
     * The target OS type
     */
    private $targetOS = null;
    /**
     * Set when targetOS is set to windows
     */
    private $targetWindows = false;
    /**
     * Set if we're running on windows
     */
    public $onWindows = false;
    /**
     * Set if we should create a new property even if the result is empty
     */
    private $setonempty = true;
    /**
     * The property to receive the conversion
     */
    private $property = null;
    /**
     * Path prefix map
     * @var MapEntry[]
     */
    private $prefixMap = array();
    /**
     * User override on path sep char
     */
    private $pathSep = null;
    /**
     * User override on directory sep char
     */
    private $dirSep = null;

    public $from = null;
    public $to = null;

    /**
     * constructor
     */
    public function __construct()
    {
        $this->onWindows = strncasecmp(PHP_OS, 'WIN', 3) === 0;
    }

    /** Create a nested PATH element */
    public function createPath()
    {

        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }

        if ($this->path === null) {
            $this->path = new Path($this->getProject());
        }
        return $this->path->createPath();
    }


    /**
     * Create a nested MAP element
     * @return MapEntry a Map to configure
     */
    public function createMap()
    {
        $entry = new MapEntry($this);

        $this->prefixMap[] = $entry;

        return $entry;
    }


    /**
     * Set targetos to a platform to one of
     * "windows", "unix", "netware", or "os/2"; required unless
     * unless pathsep and/or dirsep are specified.
     */
    public function setTargetos($target)
    {
        $this->targetOS = $target;
        $this->targetWindows = $this->targetOS !== 'unix';
    }

    /**
     * Set setonempty
     *
     * If false, don't set the new property if the result is the empty string.
     * @param bool $setonempty true or false
     */
    public function setSetonempty($setonempty)
    {
        $this->setonempty = $setonempty;
    }

    /**
     * The property into which the converted path will be placed.
     */
    public function setProperty($p)
    {
        $this->property = $p;
    }

    /**
     * Adds a reference to a Path, FileSet, DirSet, or FileList defined
     * elsewhere.
     *
     * @param Reference $r
     *
     * @throws BuildException
     */
    public function setRefid(Reference $r)
    {
        if ($this->path !== null) {
            throw $this->noChildrenAllowed();
        }

        $this->refid = $r;
    }


    /**
     * Set the default path separator string;
     * defaults to current JVM
     *
     * @param string $sep path separator string
     */
    public function setPathSep($sep)
    {
        $this->pathSep = $sep;
    }


    /**
     * Set the default directory separator string
     *
     * @param string $sep directory separator string
     */
    public function setDirSep($sep)
    {
        $this->dirSep = $sep;
    }


    /**
     * Has the refid attribute of this element been set?
     * @return true if refid is valid
     */
    public function isReference()
    {
        return $this->refid !== null;
    }


    /** Do the execution.
     * @throws BuildException if something is invalid
     */
    public function main()
    {
        $savedPath = $this->path;
        $savedPathSep = $this->pathSep;// may be altered in validateSetup
        $savedDirSep = $this->dirSep;// may be altered in validateSetup

        // If we are a reference, create a Path from the reference
        if ($this->isReference()) {
            $this->path = new Path($this->getProject());
            $this->path = $this->path->createPath();

            $obj = $this->refid->getReferencedObject($this->getProject());

            if ($obj instanceof Path) {
                $this->path->setRefid($this->refid);
            } elseif ($obj instanceof FileSet) {
                $fs = $obj;

                $this->path->addFileset($fs);
            } elseif ($obj instanceof DirSet) {
                $ds = $obj;

                $this->path->addDirset($ds);
            } else {
                throw new BuildException("'refid' does not refer to a "
                    . "path, fileset, dirset, or "
                    . "filelist.");
            }
        }

        $this->validateSetup();// validate our setup

        // Currently, we deal with only two path formats: Unix and Windows
        // And Unix is everything that is not Windows
        // (with the exception for NetWare and OS/2 below)

        // for NetWare and OS/2, piggy-back on Windows, since here and
        // in the apply code, the same assumptions can be made as with
        // windows - that \\ is an OK separator, and do comparisons
        // case-insensitive.
        $fromDirSep = $this->onWindows ? "\\" : "/";

        $rslt = '';

        // Get the list of path components in canonical form
        $elems = $this->path->listPaths();

        foreach ($elems as $key => $elem) {
            if (is_string($elem)) {
                $elem = new Path($this->project, $elem);
            }
            $elem = $this->mapElement($elem);// Apply the path prefix map

            // Now convert the path and file separator characters from the
            // current os to the target os.

            if ($key !== 0) {
                $rslt .= $this->pathSep;
            }

            $rslt .= str_replace($fromDirSep, $this->dirSep, $elem);
        }

        // Place the result into the specified property,
        // unless setonempty == false
        $value = $rslt;
        if ($this->setonempty) {
            $this->log("Set property " . $this->property . " = " . $value,
                Project::MSG_VERBOSE);
            $this->getProject()->setNewProperty($this->property, $value);
        } else {
            if ($rslt !== '') {
                $this->log("Set property " . $this->property . " = " . $value,
                    Project::MSG_VERBOSE);
                $this->getProject()->setNewProperty($this->property, $value);
            }
        }

        $this->path = $savedPath;
        $this->dirSep = $savedDirSep;
        $this->pathSep = $savedPathSep;
    }

    /**
     * Apply the configured map to a path element. The map is used to convert
     * between Windows drive letters and Unix paths. If no map is configured,
     * then the input string is returned unchanged.
     *
     * @param string $elem The path element to apply the map to
     * @return String Updated element
     */
    private function mapElement(Path $elem)
    {
        $size = count($this->prefixMap);

        if ($size !== 0) {

            // Iterate over the map entries and apply each one.
            // Stop when one of the entries actually changes the element.

            foreach ($this->prefixMap as $entry) {
                $newElem = $entry->apply((string) $elem);

                // Note I'm using "!=" to see if we got a new object back from
                // the apply method.

                if ($newElem !== (string) $elem) {
                    $elem = $newElem;
                    break;// We applied one, so we're done
                }
            }
        }

        return $elem;
    }

    /**
     * Validate that all our parameters have been properly initialized.
     *
     * @throws BuildException if something is not setup properly
     */
    private function validateSetup()
    {

        if ($this->path === null) {
            throw new BuildException("You must specify a path to convert");
        }

        if ($this->property === null) {
            throw new BuildException("You must specify a property");
        }

        // Must either have a target OS or both a dirSep and pathSep

        if ($this->targetOS == null && $this->pathSep == null && $this->dirSep == null) {
            throw new BuildException("You must specify at least one of "
                . "targetOS, dirSep, or pathSep");
        }

        // Determine the separator strings.  The dirsep and pathsep attributes
        // override the targetOS settings.
        $dsep = PhingFile::$separator;
        $psep = PhingFile::$pathSeparator;

        if ($this->targetOS !== null) {
            $psep = $this->targetWindows ? ";" : ":";
            $dsep = $this->targetWindows ? "\\" : "/";
        }

        if ($this->pathSep !== null) {// override with pathsep=
            $psep = $this->pathSep;
        }

        if ($this->dirSep !== null) {// override with dirsep=
            $dsep = $this->dirSep;
        }

        $this->pathSep = $psep;
        $this->dirSep = $dsep;
    }


    /**
     * Creates an exception that indicates that this XML element must not have
     * child elements if the refid attribute is set.
     */
    private function noChildrenAllowed()
    {
        return new BuildException("You must not specify nested <path> "
            . "elements when using the refid attribute.");
    }

}

/**
 * Helper class, holds the nested &lt;map&gt; values. Elements will look like
 * this: &lt;map from=&quot;d:&quot; to=&quot;/foo&quot;/&gt;
 *
 * When running on windows, the prefix comparison will be case
 * insensitive.
 */
class MapEntry
{
    /** @var PathConvert $outer */
    private $outer;

    public function __construct(PathConvert $outer)
    {
        $this->outer = $outer;
    }

    /**
     * the prefix string to search for; required.
     * Note that this value is case-insensitive when the build is
     * running on a Windows platform and case-sensitive when running on
     * a Unix platform.
     */
    public function setFrom($from)
    {

        $this->outer->from = $from;
    }

    public function setTo($to)
    {
        $this->outer->to = $to;
    }

    /**
     * Apply this map entry to a given path element
     *
     * @param string $elem Path element to process
     * @return string Updated path element after mapping
     *
     * @throws BuildException
     */
    public function apply($elem)
    {
        if ($this->outer->from === null || $this->outer->to === null) {
            throw new BuildException("Both 'from' and 'to' must be set "
                . "in a map entry");
        }

        // If we're on windows, then do the comparison ignoring case
        $cmpElem = $this->outer->onWindows ? strtolower($elem) : $elem;
        $cmpFrom = $this->outer->onWindows ? strtolower(str_replace('/', '\\', $this->outer->from)) : $this->outer->from;

        // If the element starts with the configured prefix, then
        // convert the prefix to the configured 'to' value.

        if (StringHelper::startsWith($cmpFrom, $cmpElem)) {
            $len = strlen($this->outer->from);

            if ($len >= strlen($elem)) {
                $elem = $this->outer->to;
            } else {
                $elem = $this->outer->to . StringHelper::substring($elem, $len);
            }
        }

        return $elem;
    }
}
