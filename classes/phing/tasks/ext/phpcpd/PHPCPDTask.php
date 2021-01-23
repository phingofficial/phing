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

use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Task;
use Phing\Type\Element\FileSetAware;
use SebastianBergmann\PHPCPD\Detector\Strategy\DefaultStrategy;
use Composer\Autoload\ClassLoader;

/**
 * Runs PHP Copy & Paste Detector. Checking PHP files for duplicated code.
 * Refactored original PhpCpdTask provided by
 * Timo Haberkern <timo.haberkern@fantastic-bits.de>
 *
 * @package phing.tasks.ext.phpcpd
 * @author  Benjamin Schultz <bschultz@proqrent.de>
 */
class PHPCPDTask extends Task
{
    use FileSetAware;

    /**
     * A php source code filename or directory
     *
     * @var File
     */
    protected $file = null;

    /**
     * Minimum number of identical lines.
     *
     * @var integer
     */
    protected $minLines = 5;

    /**
     * Minimum number of identical tokens.
     *
     * @var integer
     */
    protected $minTokens = 70;

    /**
     * Allow for fuzzy matches.
     *
     * @var boolean
     */
    protected $fuzzy = false;

    /**
     * List of valid file extensions for analyzed files.
     *
     * @var array
     */
    protected $allowedFileExtensions = ['php'];

    /**
     * List of exclude directory patterns.
     *
     * @var array
     */
    protected $ignorePatterns = ['.git', '.svn', 'CVS', '.bzr', '.hg'];

    /**
     * The format for the report
     *
     * @var string
     */
    protected $format = 'default';

    /**
     * Formatter elements.
     *
     * @var PHPCPDFormatterElement[]
     */
    protected $formatters = [];

    /**
     * @var bool
     */
    protected $oldVersion = false;

    /**
     * @var string
     */
    private $pharLocation = "";

    /**
     * Set the input source file or directory.
     *
     * @param File $file The input source file or directory.
     */
    public function setFile(File $file)
    {
        $this->file = $file;
    }

    /**
     * Sets the minimum number of identical lines (default: 5).
     *
     * @param integer $minLines Minimum number of identical lines
     */
    public function setMinLines($minLines)
    {
        $this->minLines = $minLines;
    }

    /**
     * Sets the minimum number of identical tokens (default: 70).
     *
     * @param integer $minTokens Minimum number of identical tokens
     */
    public function setMinTokens($minTokens)
    {
        $this->minTokens = $minTokens;
    }

    /**
     * Sets the fuzzy match (default: false).
     *
     * @param boolean $fuzzy fuzzy match
     */
    public function setFuzzy($fuzzy)
    {
        $this->fuzzy = $fuzzy;
    }

    /**
     * Sets a list of filename extensions for valid php source code files.
     *
     * @param string $fileExtensions List of valid file extensions.
     */
    public function setAllowedFileExtensions($fileExtensions)
    {
        $this->allowedFileExtensions = [];

        $token = ' ,;';
        $ext = strtok($fileExtensions, $token);

        while ($ext !== false) {
            $this->allowedFileExtensions[] = $ext;
            $ext = strtok($token);
        }
    }

    /**
     * Sets a list of ignore patterns that is used to exclude directories from the source analysis.
     *
     * @param string $ignorePatterns List of ignore patterns.
     */
    public function setIgnorePatterns($ignorePatterns)
    {
        $this->ignorePatterns = [];

        $token = ' ,;';
        $pattern = strtok($ignorePatterns, $token);

        while ($pattern !== false) {
            $this->ignorePatterns[] = $pattern;
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
        $this->format = $format;
    }

    /**
     * Create object for nested formatter element.
     *
     * @return PHPCPDFormatterElement
     */
    public function createFormatter()
    {
        $num = array_push($this->formatters, new PHPCPDFormatterElement($this));

        return $this->formatters[$num - 1];
    }

    /**
     * @param string $pharLocation
     */
    public function setPharLocation($pharLocation)
    {
        $this->pharLocation = $pharLocation;
    }

    /**
     * @throws BuildException if the phpcpd classes can't be loaded
     */
    private function loadDependencies()
    {
        if (!empty($this->pharLocation)) {
            // hack to prevent PHPCPD from starting in CLI mode and halting Phing
            eval(
                "namespace SebastianBergmann\PHPCPD\CLI;
class Application
{
    public function run() {}
}"
            );

            ob_start();
            include $this->pharLocation;
            ob_end_clean();

            if (class_exists('\\SebastianBergmann\\PHPCPD\\Detector\\Strategy\\DefaultStrategy')) {
                return;
            }
        }

        if (
            class_exists(ClassLoader::class, false) &&
            class_exists(DefaultStrategy::class)
        ) {
            return;
        }

        if ($handler = @fopen('SebastianBergmann/PHPCPD/autoload.php', 'r', true)) {
            fclose($handler);
            @include_once 'SebastianBergmann/PHPCPD/autoload.php';

            return;
        }

        if ($handler = @fopen('PHPCPD/Autoload.php', 'r', true)) {
            fclose($handler);
            @include_once 'PHPCPD/Autoload.php';

            $this->oldVersion = true;

            return;
        }

        throw new BuildException(
            'PHPCPDTask depends on PHPCPD being installed and on include_path.',
            $this->getLocation()
        );
    }

    /**
     * Executes PHPCPD against PhingFile or a FileSet
     *
     * @throws BuildException
     */
    public function main()
    {
        $this->loadDependencies();

        if (!isset($this->file) && count($this->filesets) == 0) {
            throw new BuildException('Missing either a nested fileset or attribute "file" set');
        }

        if (count($this->formatters) == 0) {
            // turn legacy format attribute into formatter
            $fmt = new PHPCPDFormatterElement($this);
            $fmt->setType($this->format);
            $fmt->setUseFile(false);

            $this->formatters[] = $fmt;
        }

        $this->validateFormatters();

        $filesToParse = [];

        if ($this->file instanceof File) {
            $filesToParse[] = $this->file->getPath();
        } else {
            // append any files in filesets
            foreach ($this->filesets as $fs) {
                $files = $fs->getDirectoryScanner($this->project)->getIncludedFiles();

                foreach ($files as $filename) {
                    $f = new File($fs->getDir($this->project), $filename);
                    $filesToParse[] = $f->getAbsolutePath();
                }
            }
        }

        $this->log('Processing files...');

        if ($this->oldVersion) {
            $detectorClass = 'PHPCPD_Detector';
            $strategyClass = 'PHPCPD_Detector_Strategy_Default';
        } else {
            $detectorClass = '\\SebastianBergmann\\PHPCPD\\Detector\\Detector';
            $strategyClass = '\\SebastianBergmann\\PHPCPD\\Detector\\Strategy\\DefaultStrategy';
        }

        $detector = new $detectorClass(new $strategyClass());
        $clones = $detector->copyPasteDetection(
            $filesToParse,
            $this->minLines,
            $this->minTokens,
            $this->fuzzy
        );

        $this->log('Finished copy/paste detection');

        foreach ($this->formatters as $fe) {
            $formatter = $fe->getFormatter();
            $formatter->processClones(
                $clones,
                $this->project,
                $fe->getUseFile(),
                $fe->getOutfile()
            );
        }
    }

    /**
     * Validates the available formatters
     *
     * @throws BuildException
     */
    protected function validateFormatters()
    {
        foreach ($this->formatters as $fe) {
            if ($fe->getType() == '') {
                throw new BuildException('Formatter missing required "type" attribute.');
            }

            if ($fe->getUsefile() && $fe->getOutfile() === null) {
                throw new BuildException('Formatter requires "outfile" attribute when "useFile" is true.');
            }
        }
    }
}
