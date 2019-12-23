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

use Composer\Autoload\ClassLoader;
use SebastianBergmann\PHPCPD\Detector\Detector;
use SebastianBergmann\PHPCPD\Detector\Strategy\DefaultStrategy;

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
     * @var PhingFile
     */
    protected $file = null;

    /**
     * Minimum number of identical lines.
     *
     * @var int
     */
    protected $minLines = 5;

    /**
     * Minimum number of identical tokens.
     *
     * @var int
     */
    protected $minTokens = 70;

    /**
     * Allow for fuzzy matches.
     *
     * @var bool
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
    private $pharLocation = '';

    /**
     * Set the input source file or directory.
     *
     * @param PhingFile $file The input source file or directory.
     *
     * @return void
     */
    public function setFile(PhingFile $file): void
    {
        $this->file = $file;
    }

    /**
     * Sets the minimum number of identical lines (default: 5).
     *
     * @param int $minLines Minimum number of identical lines
     *
     * @return void
     */
    public function setMinLines(int $minLines): void
    {
        $this->minLines = $minLines;
    }

    /**
     * Sets the minimum number of identical tokens (default: 70).
     *
     * @param int $minTokens Minimum number of identical tokens
     *
     * @return void
     */
    public function setMinTokens(int $minTokens): void
    {
        $this->minTokens = $minTokens;
    }

    /**
     * Sets the fuzzy match (default: false).
     *
     * @param bool $fuzzy fuzzy match
     *
     * @return void
     */
    public function setFuzzy(bool $fuzzy): void
    {
        $this->fuzzy = $fuzzy;
    }

    /**
     * Sets a list of filename extensions for valid php source code files.
     *
     * @param string $fileExtensions List of valid file extensions.
     *
     * @return void
     */
    public function setAllowedFileExtensions(string $fileExtensions): void
    {
        $this->allowedFileExtensions = [];

        $token = ' ,;';
        $ext   = strtok($fileExtensions, $token);

        while ($ext !== false) {
            $this->allowedFileExtensions[] = $ext;
            $ext                           = strtok($token);
        }
    }

    /**
     * Sets a list of ignore patterns that is used to exclude directories from the source analysis.
     *
     * @param string $ignorePatterns List of ignore patterns.
     *
     * @return void
     */
    public function setIgnorePatterns(string $ignorePatterns): void
    {
        $this->ignorePatterns = [];

        $token   = ' ,;';
        $pattern = strtok($ignorePatterns, $token);

        while ($pattern !== false) {
            $this->ignorePatterns[] = $pattern;
            $pattern                = strtok($token);
        }
    }

    /**
     * Sets the output format
     *
     * @param string $format Format of the report
     *
     * @return void
     */
    public function setFormat(string $format): void
    {
        $this->format = $format;
    }

    /**
     * Create object for nested formatter element.
     *
     * @return PHPCPDFormatterElement
     */
    public function createFormatter(): PHPCPDFormatterElement
    {
        $num = array_push($this->formatters, new PHPCPDFormatterElement($this));

        return $this->formatters[$num - 1];
    }

    /**
     * @param string $pharLocation
     *
     * @return void
     */
    public function setPharLocation(string $pharLocation): void
    {
        $this->pharLocation = $pharLocation;
    }

    /**
     * @return void
     *
     * @throws BuildException if the phpcpd classes can't be loaded
     */
    private function loadDependencies(): void
    {
        if (!empty($this->pharLocation)) {
            // hack to prevent PHPCPD from starting in CLI mode and halting Phing
            eval(
                'namespace SebastianBergmann\PHPCPD\CLI;
class Application
{
    public function run() {}
}'
            );

            ob_start();
            include $this->pharLocation;
            ob_end_clean();

            if (class_exists(DefaultStrategy::class)) {
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
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     * @throws ReflectionException
     * @throws Exception
     */
    public function main(): void
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

        if ($this->file instanceof PhingFile) {
            $filesToParse[] = $this->file->getPath();
        } else {
            // append any files in filesets
            foreach ($this->filesets as $fs) {
                $files = $fs->getDirectoryScanner($this->project)->getIncludedFiles();

                foreach ($files as $filename) {
                    $f              = new PhingFile($fs->getDir($this->project), $filename);
                    $filesToParse[] = $f->getAbsolutePath();
                }
            }
        }

        $this->log('Processing files...');

        if ($this->oldVersion) {
            $detectorClass = 'PHPCPD_Detector';
            $strategyClass = 'PHPCPD_Detector_Strategy_Default';
        } else {
            $detectorClass = Detector::class;
            $strategyClass = DefaultStrategy::class;
        }

        $detector = new $detectorClass(new $strategyClass());
        $clones   = $detector->copyPasteDetection(
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
     * @return void
     *
     * @throws BuildException
     */
    protected function validateFormatters(): void
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
