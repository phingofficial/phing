<?php
/**
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

/**
 * Runs PHP Copy & Paste Detector. Checking PHP files for duplicated code.
 * Refactored original PhpCpdTask provided by
 * Timo Haberkern <timo.haberkern@fantastic-bits.de>
 *
 * @package phing.tasks.ext.phpmd
 * @author  Benjamin Schultz <bschultz@proqrent.de>
 * @version $Id$
 */
class PHPCPDTask extends Task
{
    /**
     * A php source code filename or directory
     *
     * @var PhingFile
     */
    protected $_file = null;

    /**
     * All fileset objects assigned to this task
     *
     * @var array<FileSet>
     */
    protected $_filesets = array();

    /**
     * Minimum number of identical lines.
     *
     * @var integer
     */
    protected $_minLines = 5;

    /**
     * Minimum number of identical tokens.
     *
     * @var integer
     */
    protected $_minTokens = 70;

    /**
     * List of valid file extensions for analyzed files.
     *
     * @var array
     */
    protected $_allowedFileExtensions = array('php');

    /**
     * List of exclude directory patterns.
     *
     * @var array
     */
    protected $_ignorePatterns = array('.git', '.svn', 'CVS', '.bzr', '.hg');

    /**
     * The format for the report
     *
     * @var string
     */
    protected $_format = 'default';

    /**
     * Formatter elements.
     *
     * @var array<PHPCPDFormatterElement>
     */
    protected $_formatters = array();

    /**
     * Load the necessary environment for running PHPCPD.
     *
     * @throws BuildException - if the phpcpd classes can't be loaded.
     */
    public function init()
    {
        /**
         * Determine PHPCPD installation
         */
        @include_once 'PHPCPD/TextUI/Command.php';

        if (! class_exists('PHPCPD_TextUI_Command')) {
            throw new BuildException(
                'PHPCPDTask depends on PHPCPD being installed '
                . 'and on include_path.',
                $this->getLocation()
            );
        }

        // Other dependencies that should only be loaded
        // when class is actually used
        require_once 'phing/tasks/ext/phpcpd/PHPCPDFormatterElement.php';
        require_once 'PHPCPD/Detector.php';
    }

    /**
     * Set the input source file or directory.
     *
     * @param PhingFile $file The input source file or directory.
     *
     * @return void
     */
    public function setFile(PhingFile $file)
    {
        $this->_file = $file;
    }

    /**
     * Nested creator, adds a set of files (nested fileset attribute).
     *
     * @return FileSet The created fileset object
     */
    public function createFileSet()
    {
        $num = array_push($this->_filesets, new FileSet());
        return $this->_filesets[$num-1];
    }

    /**
     * Sets the minimum rule priority.
     *
     * @param integer $minimumPriority Minimum rule priority.
     *
     * @return void
     */
    public function setMinimumPriority($minimumPriority)
    {
        $this->_minimumPriority = $minimumPriority;
    }

    /**
     * Sets the minimum number of identical lines (default: 5).
     *
     * @param integer $minLines Minimum number of identical lines
     *
     * @return void
     */
    public function setMinLines($minLines)
    {
        $this->_minLines = $minLines;
    }

    /**
     * Sets the minimum number of identical tokens (default: 70).
     *
     * @param integer $minTokens Minimum number of identical tokens
     */
    public function setMinTokens($minTokens)
    {
        $this->_minTokens = $minTokens;
    }

    /**
     * Sets a list of filename extensions for valid php source code files.
     *
     * @param string $fileExtensions List of valid file extensions.
     *
     * @return void
     */
    public function setAllowedFileExtensions($fileExtensions)
    {
        $this->_allowedFileExtensions = array();

        $token = ' ,;';
        $ext   = strtok($fileExtensions, $token);

        while ($ext !== false) {
            $this->_allowedFileExtensions[] = $ext;
            $ext = strtok($token);
        }
    }

    /**
     * Sets a list of ignore patterns that is used to exclude directories from
     * the source analysis.
     *
     * @param string $ignorePatterns List of ignore patterns.
     *
     * @return void
     */
    public function setIgnorePatterns($ignorePatterns)
    {
        $this->_ignorePatterns = array();

        $token   = ' ,;';
        $pattern = strtok($ignorePatterns, $token);

        while ($pattern !== false) {
            $this->_ignorePatterns[] = $pattern;
            $pattern = strtok($token);
        }
    }

    /**
     * Sets the output format
     *
     * @param string $format Format of the report
     */
    public function setFormat($format)
    {
        $this->_format = $format;
    }

    /**
     * Create object for nested formatter element.
     *
     * @return PHPCPDFormatterElement
     */
    public function createFormatter()
    {
        $num = array_push(
            $this->_formatters,
            new PHPCPDFormatterElement($this)
        );
        return $this->_formatters[$num-1];
    }

    /**
     * Executes PHPCPD against PhingFile or a FileSet
     *
     * @return void
     */
    public function main()
    {
        if (!isset($this->_file) and count($this->_filesets) == 0) {
            throw new BuildException(
                "Missing either a nested fileset or attribute 'file' set"
            );
        }

        if (count($this->_formatters) == 0) {
            // turn legacy format attribute into formatter
            $fmt = new PHPCPDFormatterElement($this);
            $fmt->setType($this->format);
            $fmt->setUseFile(false);
            $this->_formatters[] = $fmt;
        }

        $this->validateFormatters();

        $filesToParse = array();

        if ($this->_file instanceof PhingFile) {
            $filesToParse[] = $this->_file->getPath();
        } else {
            // append any files in filesets
            foreach ($this->_filesets as $fs) {
                $files = $fs->getDirectoryScanner($this->project)
                            ->getIncludedFiles();

                foreach ($files as $filename) {
                     $f = new PhingFile($fs->getDir($this->project), $filename);
                     $filesToParse[] = $f->getAbsolutePath();
                }
            }
        }

        $this->log('Processing files...');

        $clones = PHPCPD_Detector::copyPasteDetection(
            $filesToParse,
            $this->_minLines,
            $this->_minTokens
        );

        $this->log('Finished copy/paste detection');

        foreach ($this->_formatters as $fe) {
            $formatter = $fe->getFormatter();
            $formatter->processClones(
                $clones,
                $fe->getOutfile(),
                $this->project
            );
        }
    }

    /**
     * Validates the available formatters
     *
     * @throws BuildException
     * @return void
     */
    protected function validateFormatters()
    {
        foreach ($this->_formatters as $fe) {
            if ($fe->getType() == '') {
                throw new BuildException(
                    "Formatter missing required 'type' attribute."
                );
            }

            if ($fe->getUsefile() && $fe->getOutfile() === null) {
                throw new BuildException(
                    "Formatter requires 'outfile' attribute "
                    . "when 'useFile' is true."
                );
            }
        }
    }
}