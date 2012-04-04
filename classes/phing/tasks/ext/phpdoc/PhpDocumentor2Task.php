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

require_once 'phing/Task.php';
require_once 'phing/system/io/FileOutputStream.php';

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
     * @var PhingFile
     */
    private $destDir = null;

    /**
     * name of the template to use
     * @var string
     */
    private $template = "responsive";
    
    /**
     * Title of the project
     * @var string
     */
    private $title = "";
    
    /**
     * Force phpDocumentor to be quiet
     * @var boolean
     */
    private $quiet = true;
    
    /**
     * Nested creator, adds a set of files (nested fileset attribute).
     * 
     * @return FileSet
     */
    public function createFileSet()
    {
        $num = array_push($this->filesets, new FileSet());
        return $this->filesets[$num-1];
    }
    
    /**
     * Sets destination/target directory
     * @param PhingFile $destDir
     */
    public function setDestDir(PhingFile $destDir)
    {
        $this->destDir = $destDir;
    }

    /**
     * Convenience setter (@see setDestDir)
     * @param PhingFile $output
     */
    public function setOutput(PhingFile $output)
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
     * Forces phpDocumentor to be quiet
     * @param boolean $quiet
     */
    public function setQuiet($quiet)
    {
        $this->quiet = (boolean) $quiet;
    }
    
    /**
     * Finds and initializes the phpDocumentor installation
     */
    private function initializePhpDocumentor()
    {
        $phpDocumentorPath = null;
        
        foreach (explode(PATH_SEPARATOR, get_include_path()) as $path) {
            $testPhpDocumentorPath = $path . DIRECTORY_SEPARATOR . 'phpDocumentor' . DIRECTORY_SEPARATOR . 'src';

            if (file_exists($testPhpDocumentorPath)) {
                $phpDocumentorPath = $testPhpDocumentorPath;
            }
        }

        if (empty($phpDocumentorPath)) {
            throw new BuildException("Please make sure PhpDocumentor 2 is installed and on the include_path.", $this->getLocation());
        }
        
        set_include_path($phpDocumentorPath . PATH_SEPARATOR . get_include_path());
        
        require_once $phpDocumentorPath . '/phpDocumentor/Bootstrap.php';
            
        $bootstrap = phpDocumentor_Bootstrap::createInstance();
            
        $autoloader = $bootstrap->registerAutoloader();
            
        if ($this->quiet) {
            phpDocumentor_Core_Abstract::config()->logging->level = 'quiet';
        } else {
            phpDocumentor_Core_Abstract::config()->logging->level = 'debug';
        }
            
        $bootstrap->registerPlugins($autoloader);
    }
    
    /**
     * Build a list of files (from the fileset elements)
     * and call the phpDocumentor parser
     *
     * @return string
     */
    private function parseFiles()
    {
        $parser = new phpDocumentor_Parser();
        
        //Only initialize the dispatcher when not already done
        if (is_null(phpDocumentor_Parser_Abstract::$event_dispatcher)) {
            phpDocumentor_Parser_Abstract::$event_dispatcher = new sfEventDispatcher();
        }
        $parser->setTitle($this->title);
        
        $paths = array();
        
        // filesets
        foreach ($this->filesets as $fs) {
            $ds    = $fs->getDirectoryScanner($this->project);
            $dir   = $fs->getDir($this->project);
            $srcFiles = $ds->getIncludedFiles();
            
            foreach ($srcFiles as $file) {
                $paths[] = $dir . FileSystem::getFileSystem()->getSeparator() . $file;
            }
        }
        
        $this->log("Will parse " . count($paths) . " file(s)", Project::MSG_VERBOSE);
        
        $files = new phpDocumentor_Parser_Files();
        $files->addFiles($paths);
        
        $parser->setPath($files->getProjectRoot());
        
        return $parser->parseFiles($files);
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
        
        $xml = $this->parseFiles();
        
        $this->log("Transforming...", Project::MSG_VERBOSE);
        
        $transformer = new phpDocumentor_Transformer();
        $transformer->setTemplatesPath(phpDocumentor_Core_Abstract::config()->paths->templates);
        $transformer->setTemplates($this->template);
        $transformer->setSource($xml);
        $transformer->setTarget($this->destDir->getAbsolutePath());
        $transformer->execute();
    }
}