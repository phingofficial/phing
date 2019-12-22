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
 * The datatype handler class.
 *
 * This class handles the occurrence of registered datatype tags like
 * FileSet
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @copyright 2001,2002 THYRELL. All rights reserved
 * @package   phing.parser
 */
class ProjectConfigurator
{
    public const PARSING_CONTEXT_REFERENCE = 'phing.parsing.context';

    /**
     * @var Project $project
     */
    public $project;
    public $locator;

    public $buildFile;
    public $buildFileParent;

    /**
     * Synthetic target that will be called at the end to the parse phase
     */
    private $parseEndTarget;

    /**
     * Name of the current project
     */
    private $currentProjectName;

    private $isParsing = true;

    /**
     * Indicates whether the project tag attributes are to be ignored
     * when processing a particular build file.
     */
    private $ignoreProjectTag = false;

    /**
     * Static call to ProjectConfigurator. Use this to configure a
     * project. Do not use the new operator.
     *
     * @param Project   $project   the Project instance this configurator should use
     * @param PhingFile $buildFile the buildfile object the parser should use
     *
     * @throws \IOException
     * @throws \BuildException
     * @throws NullPointerException
     */
    public static function configureProject(Project $project, PhingFile $buildFile): void
    {
        (new self($project, $buildFile))->parse();
    }

    /**
     * Constructs a new ProjectConfigurator object
     * This constructor is private. Use a static call to
     * <code>configureProject</code> to configure a project.
     *
     * @param Project   $project   the Project instance this configurator should use
     * @param PhingFile $buildFile the buildfile object the parser should use
     *
     * @throws IOException
     * @throws NullPointerException
     */
    private function __construct(Project $project, PhingFile $buildFile)
    {
        $this->project         = $project;
        $this->buildFile       = new PhingFile($buildFile->getAbsolutePath());
        $this->buildFileParent = new PhingFile($this->buildFile->getParent());
        $this->parseEndTarget  = new Target();
    }

    /**
     * find out the build file
     *
     * @return PhingFile the build file to which the xml context belongs
     */
    public function getBuildFile()
    {
        return $this->buildFile;
    }

    /**
     * find out the parent build file of this build file
     *
     * @return PhingFile the parent build file of this build file
     */
    public function getBuildFileParent()
    {
        return $this->buildFileParent;
    }

    /**
     * find out the current project name
     *
     * @return string current project name
     */
    public function getCurrentProjectName()
    {
        return $this->currentProjectName;
    }

    /**
     * set the name of the current project
     *
     * @param string $name name of the current project
     */
    public function setCurrentProjectName($name)
    {
        $this->currentProjectName = $name;
    }

    /**
     * tells whether the project tag is being ignored
     *
     * @return bool whether the project tag is being ignored
     */
    public function isIgnoringProjectTag()
    {
        return $this->ignoreProjectTag;
    }

    /**
     * sets the flag to ignore the project tag
     *
     * @param bool $flag flag to ignore the project tag
     */
    public function setIgnoreProjectTag($flag)
    {
        $this->ignoreProjectTag = $flag;
    }

    /**
     * @return bool
     */
    public function isParsing()
    {
        return $this->isParsing;
    }

    /**
     * Creates the ExpatParser, sets root handler and kick off parsing
     * process.
     *
     * @throws BuildException if there is any kind of exception during
     *                        the parsing process
     */
    protected function parse()
    {
        try {
            // get parse context
            $ctx = $this->project->getReference(self::PARSING_CONTEXT_REFERENCE);
            if (null == $ctx) {
                // make a new context and register it with project
                $ctx = new PhingXMLContext($this->project);
                $this->project->addReference(self::PARSING_CONTEXT_REFERENCE, $ctx);
            }

            //record this parse with context
            $ctx->addImport($this->buildFile);

            if (count($ctx->getImportStack()) > 1) {
                $currentImplicit = $ctx->getImplicitTarget();
                $currentTargets  = $ctx->getCurrentTargets();

                $newCurrent = new Target();
                $newCurrent->setProject($this->project);
                $newCurrent->setName('');
                $ctx->setCurrentTargets([]);
                $ctx->setImplicitTarget($newCurrent);

                // this is an imported file
                // modify project tag parse behavior
                $this->setIgnoreProjectTag(true);
                $this->_parse($ctx);
                $newCurrent->main();

                $ctx->setImplicitTarget($currentImplicit);
                $ctx->setCurrentTargets($currentTargets);
            } else {
                $ctx->setCurrentTargets([]);
                $this->_parse($ctx);
                $ctx->getImplicitTarget()->main();
            }
        } catch (Exception $exc) {
            //throw new BuildException("Error reading project file", $exc);
            throw $exc;
        }
    }

    /**
     * @param PhingXMLContext $ctx
     *
     * @throws ExpatParseException
     */
    protected function _parse(PhingXMLContext $ctx)
    {
        // push action onto global stack
        $ctx->startConfigure($this);

        $reader = new BufferedReader(new FileReader($this->buildFile));
        $parser = new ExpatParser($reader);
        $parser->parserSetOption(XML_OPTION_CASE_FOLDING, 0);
        $parser->setHandler(new RootHandler($parser, $this, $ctx));
        $this->project->log('parsing buildfile ' . $this->buildFile->getName(), Project::MSG_VERBOSE);
        $parser->parse();
        $reader->close();

        // mark parse phase as completed
        $this->isParsing = false;
        // execute delayed tasks
        $this->parseEndTarget->main();
        // pop this action from the global stack
        $ctx->endConfigure();
    }

    /**
     * Delay execution of a task until after the current parse phase has
     * completed.
     *
     * @param Task $task Task to execute after parse
     */
    public function delayTaskUntilParseEnd($task)
    {
        $this->parseEndTarget->addTask($task);
    }

    /**
     * Configures an element and resolves eventually given properties.
     *
     * @param mixed   $target  element to configure
     * @param array   $attrs   element's attributes
     * @param Project $project project this element belongs to
     *
     * @throws BuildException
     * @throws Exception
     */
    public static function configure($target, $attrs, Project $project)
    {
        if ($target instanceof TaskAdapter) {
            $target = $target->getProxy();
        }

        // if the target is an UnknownElement, this means that the tag had not been registered
        // when the enclosing element (task, target, etc.) was configured.  It is possible, however,
        // that the tag was registered (e.g. using <taskdef>) after the original configuration.
        // ... so, try to load it again:
        if ($target instanceof UnknownElement) {
            $tryTarget = $project->createTask($target->getTaskType());
            if ($tryTarget) {
                $target = $tryTarget;
            }
        }

        $bean = get_class($target);
        $ih   = IntrospectionHelper::getHelper($bean);

        foreach ($attrs as $key => $value) {
            if ($key == 'id') {
                continue;
                // throw new BuildException("Id must be set Extermnally");
            }
            $value = $project->replaceProperties($value);
            try { // try to set the attribute
                $ih->setAttribute($project, $target, strtolower($key), $value);
            } catch (BuildException $be) {
                // id attribute must be set externally
                if ($key !== 'id') {
                    throw $be;
                }
            }
        }
    }

    /**
     * Configures the #CDATA of an element.
     *
     * @param Project $project the project this element belongs to
     * @param object  $target  the element to configure
     * @param string  $text    the element's #CDATA
     */
    public static function addText($project, $target, $text = null)
    {
        if ($text === null || strlen(trim($text)) === 0) {
            return;
        }
        $ih   = IntrospectionHelper::getHelper(get_class($target));
        $text = $project->replaceProperties($text);
        $ih->addText($project, $target, $text);
    }

    /**
     * Stores a configured child element into its parent object
     *
     * @param object $project the project this element belongs to
     * @param object $parent  the parent element
     * @param object $child   the child element
     * @param string $tag     the XML tagname
     */
    public static function storeChild($project, $parent, $child, $tag)
    {
        $ih = IntrospectionHelper::getHelper(get_class($parent));
        $ih->storeElement($project, $parent, $child, $tag);
    }

    /**
     * Scan Attributes for the id attribute and maybe add a reference to
     * project.
     *
     * @param object $target the element's object
     * @param array  $attr   the element's attributes
     */
    public function configureId($target, $attr)
    {
        if (isset($attr['id']) && $attr['id'] !== null) {
            $this->project->addReference($attr['id'], $target);
        }
    }

    /**
     * Add location to build exception.
     *
     * @param BuildException $ex          The build exception, if the build exception does not include
     * @param Location       $newLocation The location of the calling task (may be null)
     *
     * @return BuildException A new build exception based in the build exception with
     *                        location set to newLocation. If the original exception
     *                        did not have a location, just return the build exception
     */
    public static function addLocationToBuildException(BuildException $ex, Location $newLocation)
    {
        if ($ex->getLocation() === null || $ex->getMessage() === null) {
            return $ex;
        }
        $errorMessage = sprintf(
            'The following error occurred while executing this line:%s%s %s%s',
            PHP_EOL,
            $ex->getLocation(),
            $ex->getMessage(),
            PHP_EOL
        );
        if ($ex instanceof ExitStatusException) {
            $exitStatus = $ex->getCode();
            if ($newLocation === null) {
                return new ExitStatusException($errorMessage, $exitStatus);
            }
            return new ExitStatusException($errorMessage, $exitStatus, $newLocation);
        }

        return new BuildException($errorMessage, $ex, $newLocation);
    }
}
