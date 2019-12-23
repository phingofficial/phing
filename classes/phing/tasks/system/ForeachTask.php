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
 * <foreach> task
 *
 * Task definition for the foreach task.  This task takes a list with
 * delimited values, and executes a target with set param.
 *
 * Usage:
 * <foreach list="values" target="targ" param="name" delimiter="|" />
 *
 * Attributes:
 * list      --> The list of values to process, with the delimiter character,
 *               indicated by the "delimiter" attribute, separating each value.
 * target    --> The target to call for each token, passing the token as the
 *               parameter with the name indicated by the "param" attribute.
 * param     --> The name of the parameter to pass the tokens in as to the
 *               target.
 * delimiter --> The delimiter string that separates the values in the "list"
 *               parameter.  The default is ",".
 *
 * @author  Jason Hines <jason@greenhell.com>
 * @author  Hans Lellelid <hans@xmpl.org>
 * @package phing.tasks.system
 */
class ForeachTask extends Task
{
    use ResourceAware;

    /**
     * Delimter-separated list of values to process.
     *
     * @var string
     */
    private $list;

    /**
     * Name of parameter to pass to callee
     *
     * @var string
     */
    private $param;

    /**
     * @var PropertyTask[] $params
     */
    private $params = [];

    /**
     * Name of absolute path parameter to pass to callee
     *
     * @var string
     */
    private $absparam;

    /**
     * Delimiter that separates items in $list
     *
     * @var string
     */
    private $delimiter = ',';

    /**
     * PhingCallTask that will be invoked w/ calleeTarget.
     *
     * @var PhingCallTask
     */
    private $callee;

    /**
     * Instance of mapper
     *
     * @var Mapper
     */
    private $mapperElement;

    /**
     * Target to execute.
     *
     * @var string
     */
    private $calleeTarget;

    /**
     * Total number of files processed
     *
     * @var int
     */
    private $total_files = 0;

    /**
     * Total number of directories processed
     *
     * @var int
     */
    private $total_dirs = 0;

    /**
     * @var bool $trim
     */
    private $trim = false;

    /**
     * @var bool $inheritAll
     */
    private $inheritAll = false;

    /**
     * @var bool $inheritRefs
     */
    private $inheritRefs = false;

    /**
     * @var Path $currPath
     */
    private $currPath;

    /**
     * @var PhingReference[] $references
     */
    private $references = [];

    /**
     * @var string $index
     */
    private $index = 'index';

    /**
     * This method does the work.
     *
     * @return void
     *
     * @throws ConfigurationException
     * @throws IOException
     * @throws NullPointerException
     * @throws ReflectionException
     * @throws BuildException
     */
    public function main(): void
    {
        if ($this->list === null && $this->currPath === null && count($this->dirsets) === 0 && count($this->filesets) == 0 && count($this->filelists) == 0) {
            throw new BuildException('Need either list, path, nested dirset, nested fileset or nested filelist to iterate through');
        }
        if ($this->param === null) {
            throw new BuildException('You must supply a property name to set on each iteration in param');
        }
        if ($this->calleeTarget === null) {
            throw new BuildException('You must supply a target to perform');
        }

        $callee = $this->createCallTarget();
        $mapper = null;

        if ($this->mapperElement !== null) {
            $mapper = $this->mapperElement->getImplementation();
        }

        if ($this->list !== null) {
            $arr           = explode($this->delimiter, $this->list);
            $total_entries = 0;

            foreach ($arr as $index => $value) {
                if ($this->trim) {
                    $value = trim((string) $value);
                }
                $premapped = '';
                if ($mapper !== null) {
                    $premapped = $value;
                    $value     = $mapper->main($value);
                    if ($value === null) {
                        continue;
                    }
                    $value = array_shift($value);
                }

                $message = sprintf("Setting param '%s' to value '%s'", $this->param, $value);

                if ($premapped) {
                    $message .= sprintf(" (mapped from '%s')", $premapped);
                }

                $this->log($message, Project::MSG_VERBOSE);
                $prop = $callee->createProperty();
                $prop->setOverride(true);
                $prop->setName($this->param);
                $prop->setValue($value);
                $prop = $callee->createProperty();
                $prop->setOverride(true);
                $prop->setName($this->index);
                $prop->setValue($index);
                $callee->main();
                $total_entries++;
            }
        }

        if ($this->currPath !== null) {
            $pathElements = $this->currPath->listPaths();
            foreach ($pathElements as $pathElement) {
                $ds = new DirectoryScanner();
                $ds->setBasedir($pathElement);
                $ds->scan();
                $this->process($callee, new PhingFile($pathElement), $ds->getIncludedFiles(), []);
            }
        }

        // filelists
        foreach ($this->filelists as $fl) {
            $srcFiles = $fl->getFiles($this->project);

            $this->process($callee, $fl->getDir($this->project), $srcFiles, []);
        }

        // filesets
        foreach ($this->filesets as $fs) {
            $ds       = $fs->getDirectoryScanner($this->project);
            $srcFiles = $ds->getIncludedFiles();
            $srcDirs  = $ds->getIncludedDirectories();

            $this->process($callee, $fs->getDir($this->project), $srcFiles, $srcDirs);
        }

        foreach ($this->dirsets as $dirset) {
            $ds      = $dirset->getDirectoryScanner($this->project);
            $srcDirs = $ds->getIncludedDirectories();

            $this->process($callee, $dirset->getDir($this->project), [], $srcDirs);
        }

        if ($this->list === null) {
            $this->log(
                sprintf('Processed %d directories and %d files', $this->total_dirs, $this->total_files),
                Project::MSG_VERBOSE
            );
        } else {
            $this->log(
                sprintf('Processed %d entr%s in list', $total_entries, ($total_entries > 1 ? 'ies' : 'y')),
                Project::MSG_VERBOSE
            );
        }
    }

    /**
     * Processes a list of files & directories
     *
     * @param Task      $callee
     * @param PhingFile $fromDir
     * @param array     $srcFiles
     * @param array     $srcDirs
     *
     * @return void
     *
     * @throws ConfigurationException
     * @throws IOException
     */
    protected function process(Task $callee, PhingFile $fromDir, array $srcFiles, array $srcDirs): void
    {
        $mapper = null;

        if ($this->mapperElement !== null) {
            $mapper = $this->mapperElement->getImplementation();
        }

        $filecount          = count($srcFiles);
        $this->total_files += $filecount;

        $this->processResources($filecount, $srcFiles, $callee, (string) $fromDir, $mapper);

        $dircount          = count($srcDirs);
        $this->total_dirs += $dircount;

        $this->processResources($dircount, $srcDirs, $callee, (string) $fromDir, $mapper);
    }

    /**
     * @param int                                 $rescount
     * @param array                               $srcRes
     * @param Task                                $callee
     * @param string                              $fromDir
     * @param ContainerMapper|FileNameMapper|null $mapper
     *
     * @return void
     *
     * @throws IOException
     * @throws Exception
     */
    private function processResources(int $rescount, array $srcRes, Task $callee, string $fromDir, $mapper): void
    {
        for ($j = 0; $j < $rescount; $j++) {
            $value     = $srcRes[$j];
            $premapped = '';

            if ($this->absparam) {
                $prop = $callee->createProperty();
                $prop->setOverride(true);
                $prop->setName($this->absparam);
                $prop->setValue($fromDir . FileSystem::getFileSystem()->getSeparator() . $value);
            }

            if ($mapper !== null) {
                $premapped = $value;
                $value     = $mapper->main($value);
                if ($value === null) {
                    continue;
                }
                $value = array_shift($value);
            }

            if ($this->param) {
                $message = sprintf("Setting param '%s' to value '%s'", $this->param, $value);

                if ($premapped) {
                    $message .= sprintf(" (mapped from '%s')", $premapped);
                }

                $this->log($message, Project::MSG_VERBOSE);
                $prop = $callee->createProperty();
                $prop->setOverride(true);
                $prop->setName($this->param);
                $prop->setValue($value);
            }

            $callee->main();
        }
    }

    /**
     * @param bool $trim
     *
     * @return void
     */
    public function setTrim(bool $trim): void
    {
        $this->trim = $trim;
    }

    /**
     * @param string $list
     *
     * @return void
     */
    public function setList(string $list): void
    {
        $this->list = (string) $list;
    }

    /**
     * @param string $target
     *
     * @return void
     */
    public function setTarget(string $target): void
    {
        $this->calleeTarget = (string) $target;
    }

    /**
     * @param PropertyTask $param
     *
     * @return void
     */
    public function addParam(PropertyTask $param): void
    {
        $this->params[] = $param;
    }

    /**
     * Corresponds to <code>&lt;phingcall&gt;</code>'s nested
     * <code>&lt;reference&gt;</code> element.
     *
     * @param PhingReference $r
     *
     * @return void
     */
    public function addReference(PhingReference $r): void
    {
        $this->references[] = $r;
    }

    /**
     * @param string $absparam
     *
     * @return void
     */
    public function setAbsparam(string $absparam): void
    {
        $this->absparam = (string) $absparam;
    }

    /**
     * @param string $delimiter
     *
     * @return void
     */
    public function setDelimiter(string $delimiter): void
    {
        $this->delimiter = (string) $delimiter;
    }

    /**
     * @param string $index
     *
     * @return void
     */
    public function setIndex(string $index): void
    {
        $this->index = $index;
    }

    /**
     * @return Path
     *
     * @throws Exception
     */
    public function createPath(): Path
    {
        if ($this->currPath === null) {
            $this->currPath = new Path($this->getProject());
        }

        return $this->currPath;
    }

    /**
     * Nested creator, creates one Mapper for this task
     *
     * @return Mapper The created Mapper type object
     *
     * @throws BuildException
     */
    public function createMapper(): Mapper
    {
        if ($this->mapperElement !== null) {
            throw new BuildException('Cannot define more than one mapper', $this->getLocation());
        }
        $this->mapperElement = new Mapper($this->project);

        return $this->mapperElement;
    }

    /**
     * @return PropertyTask
     *
     * @throws ConfigurationException
     * @throws IOException
     * @throws NullPointerException
     */
    public function createProperty(): PropertyTask
    {
        return $this->callee->createProperty();
    }

    /**
     * @return PropertyTask
     *
     * @throws ConfigurationException
     * @throws IOException
     * @throws NullPointerException
     */
    public function createParam(): PropertyTask
    {
        return $this->callee->createProperty();
    }

    /**
     * @param string $param
     *
     * @return void
     */
    public function setParam(string $param): void
    {
        $this->param = $param;
    }

    /**
     * Corresponds to <code>&lt;antcall&gt;</code>'s <code>inheritall</code>
     * attribute.
     *
     * @param bool $b
     *
     * @return void
     */
    public function setInheritall(bool $b): void
    {
        $this->inheritAll = $b;
    }

    /**
     * Corresponds to <code>&lt;antcall&gt;</code>'s <code>inheritrefs</code>
     * attribute.
     *
     * @param bool $b
     *
     * @return void
     */
    public function setInheritrefs(bool $b): void
    {
        $this->inheritRefs = $b;
    }

    /**
     * @return PhingCallTask
     *
     * @throws ConfigurationException
     * @throws IOException
     * @throws NullPointerException
     */
    private function createCallTarget(): PhingCallTask
    {
        /**
         * @var PhingCallTask $ct
         */
        $ct = $this->getProject()->createTask('phingcall');
        $ct->setOwningTarget($this->getOwningTarget());
        $ct->setTaskName($this->getTaskName());
        $ct->setLocation($this->getLocation());
        $ct->init();
        $ct->setTarget($this->calleeTarget);
        $ct->setInheritAll($this->inheritAll);
        $ct->setInheritRefs($this->inheritRefs);
        foreach ($this->params as $param) {
            $toSet = $ct->createParam();
            $toSet->setName($param->getName());
            if ($param->getValue() !== null) {
                $toSet->setValue($param->getValue());
            }

            if ($param->getFile() != null) {
                $toSet->setFile($param->getFile());
            }
            if ($param->getPrefix() != null) {
                $toSet->setPrefix($param->getPrefix());
            }
            if ($param->getRefid() != null) {
                $toSet->setRefid($param->getRefid());
            }
            if ($param->getEnvironment() != null) {
                $toSet->setEnvironment($param->getEnvironment());
            }
        }

        foreach ($this->references as $ref) {
            $ct->addReference($ref);
        }

        return $ct;
    }
}
