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
use Phing\Io\File;
use Phing\Io\FileSystem\FileSystemFactory;
use Phing\Project;
use Phing\Task;


/**
 * PhpDocumentor2 Task (http://www.phpdoc.org)
 * Based on the DocBlox Task
 *
 * @author    Michiel Rook <mrook@php.net>
 * @version   $Id$
 * @since     2.4.10
 * @package   phing.tasks.ext.phpdoc
 */
class PhpDocumentor2Task extends Task
{
    /**
     * List of filesets
     * @var FileSet[]
     */
    private $filesets = array();

    /**
     * Destination/target directory
     * @var File
     */
    private $destDir = null;

    /**
     * name of the template to use
     * @var string
     */
    private $template = "responsive-twig";

    /**
     * Title of the project
     * @var string
     */
    private $title = "API Documentation";

    /**
     * Name of default package
     * @var string
     */
    private $defaultPackageName = "Default";

    /**
     * Path to the phpDocumentor 2 source
     * @var string
     */
    private $phpDocumentorPath = "";

    /**
     * @var \phpDocumentor\Application
     */
    private $app = null;

    /**
     * Nested adder, adds a set of files (nested fileset attribute).
     *
     * @param FileSet $fs
     * @return void
     */
    public function addFileSet(FileSet $fs)
    {
        $this->filesets[] = $fs;
    }

    /**
     * Sets destination/target directory
     * @param File $destDir
     */
    public function setDestDir(File $destDir)
    {
        $this->destDir = $destDir;
    }

    /**
     * Convenience setter (@see setDestDir)
     * @param File $output
     */
    public function setOutput(File $output)
    {
        $this->destDir = $output;
    }

    /**
     * Sets the template to use
     * @param strings $template
     */
    public function setTemplate($template)
    {
        $this->template = (string) $template;
    }

    /**
     * Sets the title of the project
     * @param strings $title
     */
    public function setTitle($title)
    {
        $this->title = (string) $title;
    }

    /**
     * Sets the default package name
     * @param string $defaultPackageName
     */
    public function setDefaultPackageName($defaultPackageName)
    {
        $this->defaultPackageName = (string) $defaultPackageName;
    }

    /**
     * Forces phpDocumentor to be quiet
     * @deprecated
     * @param boolean $quiet
     */
    public function setQuiet($quiet)
    {
        $this->project->log(__CLASS__ . ": the 'quiet' option has been deprecated", Project::MSG_WARN);
    }

    /**
     * Task entry point
     * @see Task::main()
     */
    public function main()
    {
        if (empty($this->destDir)) {
            throw new BuildException("You must supply the 'destdir' attribute", $this->getLocation());
        }

        if (empty($this->filesets)) {
            throw new BuildException("You have not specified any files to include (<fileset>)", $this->getLocation());
        }

        $this->initializePhpDocumentor();

        $cache = $this->app['descriptor.cache'];
        $cache->getOptions()->setCacheDir($this->destDir->getAbsolutePath());

        $this->parseFiles();

        $this->project->log("Transforming...", Project::MSG_VERBOSE);

        $this->transformFiles();
    }

    /**
     * Finds and initializes the phpDocumentor installation
     */
    private function initializePhpDocumentor()
    {
        if (class_exists('Composer\\Autoload\\ClassLoader', false)) {
            if (!class_exists('phpDocumentor\\Bootstrap')) {
                throw new BuildException('You need to install PhpDocumentor 2 or add your include path to your composer installation.');
            }
            $phpDocumentorPath = '';
        } else {
            $phpDocumentorPath = $this->findPhpDocumentorPath();

            if (empty($phpDocumentorPath)) {
                throw new BuildException("Please make sure PhpDocumentor 2 is installed and on the include_path.");
            }

            set_include_path($phpDocumentorPath . PATH_SEPARATOR . get_include_path());

            require_once $phpDocumentorPath . '/phpDocumentor/Bootstrap.php';
        }

        $this->app = \phpDocumentor\Bootstrap::createInstance()->initialize();

        $this->phpDocumentorPath = $phpDocumentorPath;
    }

    /**
     * Find the correct php documentor path
     *
     * @return null|string
     */
    private function findPhpDocumentorPath()
    {
        $phpDocumentorPath = null;
        $directories = array('phpDocumentor', 'phpdocumentor');
        foreach ($directories as $directory) {
            foreach (Phing::explodeIncludePath() as $path) {
                $testPhpDocumentorPath = $path . DIRECTORY_SEPARATOR . $directory . DIRECTORY_SEPARATOR . 'src';
                if (file_exists($testPhpDocumentorPath)) {
                    $phpDocumentorPath = $testPhpDocumentorPath;
                }
            }
        }

        return $phpDocumentorPath;
    }

    /**
     * Build a list of files (from the fileset elements)
     * and call the phpDocumentor parser
     *
     * @return string
     */
    private function parseFiles()
    {
        $parser = $this->app['parser'];
        $builder = $this->app['descriptor.builder'];

        $builder->createProjectDescriptor();
        $projectDescriptor = $builder->getProjectDescriptor();
        $projectDescriptor->setName($this->title);

        $paths = array();

        // filesets
        foreach ($this->filesets as $fs) {
            $ds = $fs->getDirectoryScanner($this->project);
            $dir = $fs->getDir($this->project);
            $srcFiles = $ds->getIncludedFiles();

            foreach ($srcFiles as $file) {
                $paths[] = $dir . FileSystemFactory::getFileSystem()->getSeparator() . $file;
            }
        }

        $this->project->log("Will parse " . count($paths) . " file(s)", Project::MSG_VERBOSE);

        $files = new \phpDocumentor\Fileset\Collection();
        $files->addFiles($paths);

        $mapper = new \phpDocumentor\Descriptor\Cache\ProjectDescriptorMapper($this->app['descriptor.cache']);
        $mapper->garbageCollect($files);
        $mapper->populate($projectDescriptor);

        $parser->setPath($files->getProjectRoot());
        $parser->setDefaultPackageName($this->defaultPackageName);

        $parser->parse($builder, $files);

        $mapper->save($projectDescriptor);

        return $mapper;
    }

    /**
     * Transforms the parsed files
     */
    private function transformFiles()
    {
        $transformer = $this->app['transformer'];
        $compiler = $this->app['compiler'];
        $builder = $this->app['descriptor.builder'];
        $projectDescriptor = $builder->getProjectDescriptor();

        $transformer->getTemplates()->load($this->template, $transformer);
        $transformer->setTarget($this->destDir->getAbsolutePath());

        foreach ($compiler as $pass) {
            $pass->execute($projectDescriptor);
        }
    }
}
