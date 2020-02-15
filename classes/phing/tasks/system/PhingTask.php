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

/**
 * Task that invokes phing on another build file.
 *
 * Use this task, for example, if you have nested buildfiles in your project. Unlike
 * AntTask, PhingTask can even support filesets:
 *
 * <pre>
 *   <phing>
 *    <fileset dir="${srcdir}">
 *      <include name="** /build.xml" /> <!-- space added after ** is there because of PHP comment syntax -->
 *      <exclude name="build.xml" />
 *    </fileset>
 *   </phing>
 * </pre>
 *
 * @author  Hans Lellelid <hans@xmpl.org>
 * @package phing.tasks.system
 */
class PhingTask extends Task
{
    use FileSetAware;

    /**
     * the basedir where is executed the build file
     * @var PhingFile
     */
    private $dir;

    /**
     * build.xml (can be absolute) in this case dir will be ignored
     */
    private $phingFile;

    /**
     * the target to call if any
     * @var Target
     */
    protected $newTarget;

    /**
     * should we inherit properties from the parent ?
     */
    private $inheritAll = true;

    /**
     * should we inherit references from the parent ?
     */
    private $inheritRefs = false;

    /**
     * the properties to pass to the new project
     */
    private $properties = [];

    /**
     * the references to pass to the new project
     */
    private $references = [];

    /**
     * The temporary project created to run the build file
     *
     * @var Project
     */
    private $newProject;

    /**
     * Fail the build process when the called build fails?
     */
    private $haltOnFailure = false;

    /**
     * Whether the basedir of the new project should be the same one
     * as it would be when running the build file directly -
     * independent of dir and/or inheritAll settings.
     */
    private $useNativeBasedir = false;

    /**
     * @var OutputStream
     */
    private $out;

    /** @var string */
    private $output;

    /**
     * @var array
     */
    private $locals;

    public function __construct(Task $owner = null)
    {
        if ($owner !== null) {
            $this->bindToOwner($owner);
        }
        parent::__construct();
    }

    /**
     *  If true, abort the build process if there is a problem with or in the target build file.
     *  Defaults to false.
     *
     * @param bool $hof new value
     */
    public function setHaltOnFailure($hof)
    {
        $this->haltOnFailure = (bool) $hof;
    }

    /**
     * Whether the basedir of the new project should be the same one
     * as it would be when running the build file directly -
     * independent of dir and/or inheritAll settings.
     *
     * @param bool $b
     */
    public function setUseNativeBasedir(bool $b)
    {
        $this->useNativeBasedir = $b;
    }

    /**
     * Creates a Project instance for the project to call.
     *
     * @return void
     */
    public function init()
    {
        $this->newProject = $this->getProject()->createSubProject();
        $tdf = $this->project->getTaskDefinitions();
        $this->newProject->addTaskDefinition("property", $tdf["property"]);
    }

    /**
     * Called in execute or createProperty if newProject is null.
     *
     * <p>This can happen if the same instance of this task is run
     * twice as newProject is set to null at the end of execute (to
     * save memory and help the GC).</p>
     *
     * <p>Sets all properties that have been defined as nested
     * property elements.</p>
     */
    private function reinit()
    {
        $this->init();

        $count = count($this->properties);
        for ($i = 0; $i < $count; $i++) {
            /**
             * @var PropertyTask $p
             */
            $p = $this->properties[$i];
            /**
             * @var PropertyTask $newP
             */
            $newP = $this->newProject->createTask("property");
            $newP->setName($p->getName());
            if ($p->getValue() !== null) {
                $newP->setValue($p->getValue());
            }
            if ($p->getFile() !== null) {
                $newP->setFile($p->getFile());
            }
            if ($p->getPrefix() !== null) {
                $newP->setPrefix($p->getPrefix());
            }
            if ($p->getRefid() !== null) {
                $newP->setRefid($p->getRefid());
            }
            if ($p->getEnvironment() !== null) {
                $newP->setEnvironment($p->getEnvironment());
            }
            if ($p->getUserProperty() !== null) {
                $newP->setUserProperty($p->getUserProperty());
            }
            $newP->setOverride($p->getOverride());
            $newP->setLogoutput($p->getLogoutput());
            $newP->setQuiet($p->getQuiet());

            $this->properties[$i] = $newP;
        }
    }

    /**
     * Main entry point for the task.
     *
     * @return void
     */
    public function main()
    {
        // Call Phing on the file set with the attribute "phingfile"
        if ($this->phingFile !== null || $this->dir !== null) {
            $this->processFile();
        }

        // if no filesets are given stop here; else process filesets
        if (!empty($this->filesets)) {
            // preserve old settings
            $savedDir = $this->dir;
            $savedPhingFile = $this->phingFile;
            $savedTarget = $this->newTarget;

            // set no specific target for files in filesets
            // [HL] I'm commenting this out; I don't know why this should not be supported!
            // $this->newTarget = null;

            foreach ($this->filesets as $fs) {
                $ds = $fs->getDirectoryScanner($this->project);

                $fromDir = $fs->getDir($this->project);
                $srcFiles = $ds->getIncludedFiles();

                foreach ($srcFiles as $fname) {
                    $f = new PhingFile($ds->getbasedir(), $fname);
                    $f = $f->getAbsoluteFile();
                    $this->phingFile = $f->getAbsolutePath();
                    $this->dir = $f->getParentFile();
                    $this->processFile(); // run Phing!
                }
            }

            // side effect free programming ;-)
            $this->dir = $savedDir;
            $this->phingFile = $savedPhingFile;
            $this->newTarget = $savedTarget;

            // [HL] change back to correct dir
            if ($this->dir !== null) {
                chdir($this->dir->getAbsolutePath());
            }
        }

        // Remove any dangling references to help the GC
        foreach ($this->properties as $property) {
            $property->setFallback(null);
        }
    }

    /**
     * Execute phing file.
     *
     * @throws BuildException
     */
    private function processFile(): void
    {
        $buildFailed = false;
        $savedDir = $this->dir;
        $savedPhingFile = $this->phingFile;
        $savedTarget = $this->newTarget;

        $savedBasedirAbsPath = null; // this is used to save the basedir *if* we change it

        try {
            $this->getNewProject();

            $this->initializeProject();

            if ($this->dir !== null) {
                if (!$this->useNativeBasedir) {
                    $this->newProject->setBasedir($this->dir);
                    if ($savedDir !== null) { // has been set explicitly
                        $this->newProject->setInheritedProperty('project.basedir', $this->dir->getAbsolutePath());
                    }
                }
            } else {
                // Since we're not changing the basedir here (for file resolution),
                // we don't need to worry about any side-effects in this scanrio.
                $this->dir = $this->getProject()->getBasedir();
            }

            $this->overrideProperties();
            $this->phingFile = $this->phingFile ?? 'build.xml';

            $fu = new FileUtils();
            $file = $fu->resolveFile($this->dir, $this->phingFile);
            $this->phingFile = $file->getAbsolutePath();
            $this->log('calling target(s) '
                . (empty($this->locals) ? '[default]' : implode(', ', $this->locals))
                . ' in build file ' . $this->phingFile, Project::MSG_VERBOSE);

            $this->newProject->setUserProperty("phing.file", $this->phingFile);

            if (empty($this->locals)) {
                $defaultTarget = $this->newProject->getDefaultTarget();
                if ($defaultTarget !== null) {
                    $this->locals[] = $defaultTarget;
                }
            }

            $thisPhingFile = $this->getProject()->getProperty('phing.file');
            // Are we trying to call the target in which we are defined (or
            // the build file if this is a top level task)?
            if (
                $thisPhingFile !== null
                && $this->getOwningTarget() !== null
                && $thisPhingFile === $file->getPath()
                && $this->getOwningTarget()->getName() === ''
            ) {
                if ('phingcall' === $this->getTaskName()) {
                    throw new BuildException('phingcall must not be used at the top level.');
                }
                throw new BuildException(
                    '%s task at the top level must not invoke its own build file.',
                    $this->getTaskName()
                );
            }

            ProjectConfigurator::configureProject($this->newProject, new PhingFile($this->phingFile));

            if ($this->newTarget === null) {
                $this->newTarget = $this->newProject->getDefaultTarget();
            }

            // Are we trying to call the target in which we are defined?
            if (
                $this->newProject->getBasedir()->equals($this->project->getBasedir())
                && $this->newProject->getProperty('phing.file') === $this->project->getProperty('phing.file')
                && $this->getOwningTarget() !== null
            ) {
                $owningTargetName = $this->getOwningTarget()->getName();
                if ($this->newTarget === $owningTargetName) {
                    throw new BuildException(
                        sprintf(
                            "%s task calling its own parent target",
                            $this->getTaskName()
                        )
                    );
                }

                $targets = $this->getProject()->getTargets();
                $taskName = $this->getTaskName();
                array_walk(
                    $targets,
                    static function (Target $target) use ($owningTargetName, $taskName) {
                        if (in_array($owningTargetName, $target->getDependencies())) {
                            throw new BuildException(
                                sprintf(
                                    "%s task calling its own parent target '%s'",
                                    $taskName,
                                    $owningTargetName
                                )
                            );
                        }
                    }
                );
            }

            $this->addReferences();
            $this->newProject->executeTarget($this->newTarget);
        } catch (Exception $e) {
            $buildFailed = true;
            $this->log($e->getMessage(), Project::MSG_ERR);
            if (Phing::getMsgOutputLevel() <= Project::MSG_DEBUG) {
                $lines = explode("\n", $e->getTraceAsString());
                foreach ($lines as $line) {
                    $this->log($line, Project::MSG_DEBUG);
                }
            }
        } finally {
            // reset environment values to prevent side-effects.

            $this->newProject = null;
            $pkeys = array_keys($this->properties);
            foreach ($pkeys as $k) {
                $this->properties[$k]->setProject(null);
            }

            if ($this->output !== null && $this->out !== null) {
                $this->out->close();
            }

            $this->dir = $savedDir;
            $this->phingFile = $savedPhingFile;
            $this->newTarget = $savedTarget;

            // If the basedir for any project was changed, we need to set that back here.
            if ($savedBasedirAbsPath !== null) {
                chdir($savedBasedirAbsPath);
            }

            if ($this->haltOnFailure && $buildFailed) {
                throw new BuildException('Execution of the target buildfile failed. Aborting.');
            }
        }
    }

    /**
     * Get the (sub)-Project instance currently in use.
     *
     * @return Project
     */
    protected function getNewProject(): \Project
    {
        if ($this->newProject === null) {
            $this->reinit();
        }
        return $this->newProject;
    }

    /**
     * Configure the Project, i.e. make intance, attach build listeners
     * (copy from father project), add Task and Datatype definitions,
     * copy properties and references from old project if these options
     * are set via the attributes of the XML tag.
     *
     * Developer note:
     * This function replaces the old methods "init", "_reinit" and
     * "_initializeProject".
     */
    private function initializeProject()
    {
        $this->newProject->setInputHandler($this->project->getInputHandler());

        foreach ($this->project->getBuildListeners() as $listener) {
            $this->newProject->addBuildListener($listener);
        }

        /* Copy things from old project. Datatypes and Tasks are always
         * copied, properties and references only if specified so/not
         * specified otherwise in the XML definition.
         */
        // Add Datatype definitions
        foreach ($this->project->getDataTypeDefinitions() as $typeName => $typeClass) {
            $this->newProject->addDataTypeDefinition($typeName, $typeClass);
        }

        // Add Task definitions
        foreach ($this->project->getTaskDefinitions() as $taskName => $taskClass) {
            if ($taskClass === 'phing.tasks.system.PropertyTask') {
                // we have already added this taskdef in init()
                continue;
            }
            $this->newProject->addTaskDefinition($taskName, $taskClass);
        }

        if ($this->output !== null) {
            try {
                if ($this->dir !== null) {
                    $outfile = (new FileUtils())->resolveFile($this->dir, $this->output);
                } else {
                    $outfile = $this->getProject()->resolveFile($this->output);
                }
                $this->out = new FileOutputStream($outfile);
                $logger = new DefaultLogger();
                $logger->setMessageOutputLevel(Project::MSG_INFO);
                $logger->setOutputStream($this->out);
                $logger->setErrorStream($this->out);
                $this->newProject->addBuildListener($logger);
            } catch (Exception $ex) {
                $this->log("Phing: Can't set output to " . $this->output);
            }
        }

        if ($this->useNativeBasedir) {
            $this->addAlmostAll($this->getProject()->getUserProperties(), 'user');
        } else {
            $this->project->copyUserProperties($this->newProject);
        }

        if (!$this->inheritAll) {
            // set System built-in properties separately,
            // b/c we won't inherit them.
            $this->newProject->setSystemProperties();
        } else {
            $this->addAlmostAll($this->getProject()->getProperties(), 'plain');
        }
    }

    /**
     * Copies all properties from the given table to the new project -
     * omitting those that have already been set in the new project as
     * well as properties named basedir or ant.file.
     * @param array $props properties <code>Hashtable</code> to copy to the
     * new project.
     * @param string $type the type of property to set (a plain Ant property, a
     * user property or an inherited property).
     */
    private function addAlmostAll(array $props, string $type): void
    {
        foreach ($props as $name => $value) {
            if ($name === 'basedir' || $name === 'phing.file' || $name === 'phing.version') {
                // basedir and phing.file get special treatment in main()
                continue;
            }
            if ($type === 'plain') {
                // don't re-set user properties, avoid the warning message
                if ($this->newProject->getProperty($name) === null) {
                    // no user property
                    $this->newProject->setNewProperty($name, $value);
                }
            } elseif ($type === 'user') {
                $this->newProject->setUserProperty($name, $value);
            } elseif ($type === 'inherited') {
                $this->newProject->setInheritedProperty($name, $value);
            }
        }
    }

    /**
     * Override the properties in the new project with the one
     * explicitly defined as nested elements here.
     *
     * @return void
     * @throws BuildException
     */
    private function overrideProperties()
    {
        $set = [];
        foreach (array_reverse(array_keys($this->properties)) as $i) {
            $p = $this->properties[$i];
            if ($p->getName() !== null && $p->getName() !== '' && $p->getName() !== null) {
                if (in_array($p->getName(), $set)) {
                    unset($this->properties[$i]);
                } else {
                    $set[] = $p->getName();
                }
            }
            $p->setProject($this->newProject);
            $p->main();
        }
        if ($this->useNativeBasedir) {
            $this->addAlmostAll($this->getProject()->getInheritedProperties(), 'inherited');
        } else {
            $this->project->copyInheritedProperties($this->newProject);
        }
    }

    /**
     * Add the references explicitly defined as nested elements to the
     * new project.  Also copy over all references that don't override
     * existing references in the new project if inheritrefs has been
     * requested.
     *
     * @return void
     * @throws BuildException
     */
    private function addReferences()
    {

        // parent project references
        $projReferences = $this->project->getReferences();

        $newReferences = $this->newProject->getReferences();

        $subprojRefKeys = [];

        if (count($this->references) > 0) {
            for ($i = 0, $count = count($this->references); $i < $count; $i++) {
                /** @var Reference $ref */
                $ref = $this->references[$i];
                $refid = $ref->getRefId();

                if ($refid === null) {
                    throw new BuildException('the refid attribute is required for reference elements');
                }
                if (!isset($projReferences[$refid])) {
                    $this->log("Parent project doesn't contain any reference '" . $refid . "'", Project::MSG_WARN);
                    continue;
                }

                $subprojRefKeys[] = $refid;
                unset($this->references[$i]);//thisReferences.remove(refid);
                $toRefid = $ref->getToRefid();
                if ($toRefid === null) {
                    $toRefid = $refid;
                }
                $this->copyReference($refid, $toRefid);
            }
        }

        // Now add all references that are not defined in the
        // subproject, if inheritRefs is true
        if ($this->inheritRefs) {
            // get the keys that are were not used by the subproject
            $unusedRefKeys = array_diff(array_keys($projReferences), $subprojRefKeys);

            foreach ($unusedRefKeys as $key) {
                if (isset($newReferences[$key])) {
                    continue;
                }
                $this->copyReference($key, $key);
            }
        }
    }

    /**
     * Try to clone and reconfigure the object referenced by oldkey in
     * the parent project and add it to the new project with the key
     * newkey.
     *
     * <p>If we cannot clone it, copy the referenced object itself and
     * keep our fingers crossed.</p>
     *
     * @param  string $oldKey
     * @param  string $newKey
     * @throws BuildException
     * @return void
     */
    private function copyReference($oldKey, $newKey)
    {
        $orig = $this->project->getReference($oldKey);
        if ($orig === null) {
            $this->log(
                "No object referenced by " . $oldKey . ". Can't copy to "
                . $newKey,
                Project::MSG_WARN
            );

            return;
        }

        $copy = clone $orig;

        if ($copy instanceof ProjectComponent) {
            $copy->setProject($this->newProject);
        } elseif (in_array('setProject', get_class_methods(get_class($copy)))) {
            $copy->setProject($this->newProject);
        } elseif (!($copy instanceof Project)) {
            // don't copy the old "Project" itself
            $msg = "Error setting new project instance for "
                . "reference with id " . $oldKey;
            throw new BuildException($msg);
        }

        $this->newProject->addReference($newKey, $copy);
    }

    /**
     * If true, pass all properties to the new phing project.
     * Defaults to true.
     *
     * @param $value
     */
    public function setInheritAll($value)
    {
        $this->inheritAll = (bool) $value;
    }

    /**
     * If true, pass all references to the new phing project.
     * Defaults to false.
     *
     * @param $value
     */
    public function setInheritRefs($value)
    {
        $this->inheritRefs = (bool) $value;
    }

    /**
     * The directory to use as a base directory for the new phing project.
     * Defaults to the current project's basedir, unless inheritall
     * has been set to false, in which case it doesn't have a default
     * value. This will override the basedir setting of the called project.
     *
     * @param PhingFile $d
     */
    public function setDir(PhingFile $d): void
    {
        $this->dir = $d;
    }

    /**
     * The build file to use.
     * Defaults to "build.xml". This file is expected to be a filename relative
     * to the dir attribute given.
     *
     * @param $s
     */
    public function setPhingFile($s)
    {
        // it is a string and not a file to handle relative/absolute
        // otherwise a relative file will be resolved based on the current
        // basedir.
        $this->phingFile = $s;
    }

    /**
     * Alias function for setPhingfile
     *
     * @param $s
     */
    public function setBuildfile($s)
    {
        $this->setPhingFile($s);
    }

    /**
     * The target of the new Phing project to execute.
     * Defaults to the new project's default target.
     *
     * @param string $s
     */
    public function setTarget(string $s)
    {
        if ('' === $s) {
            throw new BuildException("target attribute must not be empty");
        }

        $this->newTarget = $s;
    }

    /**
     * Set the filename to write the output to. This is relative to the value
     * of the dir attribute if it has been set or to the base directory of the
     * current project otherwise.
     * @param string $outputFile the name of the file to which the output should go.
     */
    public function setOutput(string $outputFile)
    {
        $this->output = $outputFile;
    }

    /**
     * Property to pass to the new project.
     * The property is passed as a 'user property'
     */
    public function createProperty()
    {
        $p = new PropertyTask();
        $p->setFallback($this->getNewProject());
        $p->setUserProperty(true);
        $p->setTaskName('property');
        $this->properties[] = $p;

        return $p;
    }

    /**
     * Reference element identifying a data type to carry
     * over to the new project.
     *
     * @param PhingReference $ref
     */
    public function addReference(PhingReference $ref)
    {
        $this->references[] = $ref;
    }
}
