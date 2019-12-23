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

use PHPMD\AbstractRule;
use PHPMD\PHPMD;
use PHPMD\RuleSetFactory;

/**
 * Runs PHP Mess Detector. Checking PHP files for several potential problems
 * based on rulesets.
 *
 * @package phing.tasks.ext.phpmd
 * @author  Benjamin Schultz <bschultz@proqrent.de>
 * @since   2.4.1
 */
class PHPMDTask extends Task
{
    use FileSetAware;

    /**
     * A php source code filename or directory
     *
     * @var PhingFile
     */
    protected $file = null;

    /**
     * The rule-set filenames or identifier.
     *
     * @var string
     */
    protected $rulesets = 'codesize,unusedcode';

    /**
     * The minimum priority for rules to load.
     *
     * @var int
     */
    protected $minimumPriority = 0;

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
    protected $format = 'text';

    /**
     * Formatter elements.
     *
     * @var PHPMDFormatterElement[]
     */
    protected $formatters = [];

    /**
     * @var bool
     */
    protected $newVersion = true;

    /**
     * @var string
     */
    protected $pharLocation = '';

    /**
     * Cache data storage
     *
     * @var DataStore
     */
    protected $cache;

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
     * Sets the minimum rule priority.
     *
     * @param int $minimumPriority Minimum rule priority.
     *
     * @return void
     */
    public function setMinimumPriority(int $minimumPriority): void
    {
        $this->minimumPriority = $minimumPriority;
    }

    /**
     * Sets the rule-sets.
     *
     * @param string $ruleSetFileNames Comma-separated string of rule-set filenames or identifier.
     *
     * @return void
     */
    public function setRulesets(string $ruleSetFileNames): void
    {
        $this->rulesets = $ruleSetFileNames;
    }

    /**
     * Sets a list of filename extensions for valid php source code files.
     *
     * @param string $fileExtensions List of valid file extensions without leading dot.
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
     * Create object for nested formatter element.
     *
     * @return PHPMDFormatterElement
     */
    public function createFormatter(): PHPMDFormatterElement
    {
        $num = array_push($this->formatters, new PHPMDFormatterElement());

        return $this->formatters[$num - 1];
    }

    /**
     * @param string $format
     *
     * @return void
     */
    public function setFormat(string $format): void
    {
        $this->format = $format;
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
     * Whether to store last-modified times in cache
     *
     * @param PhingFile $file
     *
     * @return void
     *
     * @throws IOException
     */
    public function setCacheFile(PhingFile $file): void
    {
        $this->cache = new DataStore($file);
    }

    /**
     * Find PHPMD
     *
     * @return string
     *
     * @throws BuildException
     */
    protected function loadDependencies(): string
    {
        if (!empty($this->pharLocation)) {
            include_once 'phar://' . $this->pharLocation . '/vendor/autoload.php';
        }

        $className = PHPMD::class;

        if (!class_exists($className)) {
            @include_once 'PHP/PMD.php';
            $className        = 'PHP_PMD';
            $this->newVersion = false;
        }

        if (!class_exists($className)) {
            throw new BuildException(
                'PHPMDTask depends on PHPMD being installed and on include_path or listed in pharLocation.',
                $this->getLocation()
            );
        }

        if ($this->newVersion) {
            $minPriority = AbstractRule::LOWEST_PRIORITY;
            include_once 'phing/tasks/ext/phpmd/PHPMDRendererRemoveFromCache.php';
        } else {
            include_once 'PHP/PMD/AbstractRule.php';
            $minPriority = PHP_PMD_AbstractRule::LOWEST_PRIORITY;
        }

        if (!$this->minimumPriority) {
            $this->minimumPriority = $minPriority;
        }

        return $className;
    }

    /**
     * Return the list of files to parse
     *
     * @return string[] list of absolute files to parse
     *
     * @throws IOException
     * @throws NullPointerException
     * @throws ReflectionException
     */
    protected function getFilesToParse(): array
    {
        $filesToParse = [];

        if ($this->file instanceof PhingFile) {
            $filesToParse[] = $this->file->getPath();
        } else {
            // append any files in filesets
            foreach ($this->filesets as $fs) {
                $dir = $fs->getDir($this->project)->getAbsolutePath();
                foreach ($fs->getDirectoryScanner($this->project)->getIncludedFiles() as $filename) {
                    $fileAbsolutePath = $dir . DIRECTORY_SEPARATOR . $filename;
                    if ($this->cache) {
                        $lastMTime    = $this->cache->get($fileAbsolutePath);
                        $currentMTime = filemtime($fileAbsolutePath);
                        if ($lastMTime >= $currentMTime) {
                            continue;
                        }

                        $this->cache->put($fileAbsolutePath, $currentMTime);
                    }
                    $filesToParse[] = $fileAbsolutePath;
                }
            }
        }
        return $filesToParse;
    }

    /**
     * Executes PHPMD against PhingFile or a FileSet
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
        $className = $this->loadDependencies();

        if (!isset($this->file) && count($this->filesets) == 0) {
            throw new BuildException('Missing either a nested fileset or attribute "file" set');
        }

        if (count($this->formatters) == 0) {
            // turn legacy format attribute into formatter
            $fmt = new PHPMDFormatterElement();
            $fmt->setType($this->format);
            $fmt->setUseFile(false);

            $this->formatters[] = $fmt;
        }

        $reportRenderers = [];

        foreach ($this->formatters as $fe) {
            if ($fe->getType() == '') {
                throw new BuildException('Formatter missing required "type" attribute.');
            }

            if ($fe->getUsefile() && $fe->getOutfile() === null) {
                throw new BuildException('Formatter requires "outfile" attribute when "useFile" is true.');
            }

            $reportRenderers[] = $fe->getRenderer();
        }

        if ($this->newVersion && $this->cache) {
            $reportRenderers[] = new PHPMDRendererRemoveFromCache($this->cache);
        } else {
            $this->cache = null; // cache not compatible to old version
        }

        // Create a rule set factory
        if ($this->newVersion) {
            $ruleSetFactory = new RuleSetFactory();
        } else {
            if (!class_exists('PHP_PMD_RuleSetFactory')) {
                @include 'PHP/PMD/RuleSetFactory.php';
            }
            $ruleSetFactory = new PHP_PMD_RuleSetFactory();
        }
        $ruleSetFactory->setMinimumPriority($this->minimumPriority);

        /**
         * @var PHPMD\PHPMD $phpmd
         */
        $phpmd = new $className();
        $phpmd->setFileExtensions($this->allowedFileExtensions);
        $phpmd->setIgnorePattern($this->ignorePatterns);

        $filesToParse = $this->getFilesToParse();

        if (count($filesToParse) > 0) {
            $inputPath = implode(',', $filesToParse);

            $this->log('Processing files...');

            $phpmd->processFiles($inputPath, $this->rulesets, $reportRenderers, $ruleSetFactory);

            if ($this->cache) {
                $this->cache->commit();
            }

            $this->log('Finished processing files');
        } else {
            $this->log('No files to process');
        }
    }
}
