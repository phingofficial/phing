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
 * A Javascript lint task. Checks syntax of Javascript files.
 * Javascript lint (http://www.javascriptlint.com) must be in the system path.
 * This class is based on Knut Urdalen's PhpLintTask.
 *
 * @author Stefan Priebsch <stefan.priebsch@e-novative.de>
 * @package phing.tasks.ext
 */
class JslLintTask extends Task
{
    use FileSetAware;

    /**
     * @var PhingFile
     */
    protected $file; // the source file (from xml attribute)

    /**
     * @var bool $showWarnings
     */
    protected $showWarnings = true;

    /**
     * @var bool
     */
    protected $haltOnFailure = false;

    /**
     * @var bool
     */
    protected $haltOnWarning = false;

    /**
     * @var bool
     */
    protected $hasErrors = false;

    /**
     * @var bool
     */
    protected $hasWarnings = false;

    /**
     * @var array $badFiles
     */
    private $badFiles = [];

    /**
     * @var DataStore
     */
    private $cache = null;

    /**
     * @var PhingFile
     */
    private $conf = null;

    /**
     * @var string
     */
    private $executable = 'jsl';

    /**
     * @var PhingFile
     */
    protected $tofile = null;

    /**
     * Sets the flag if warnings should be shown
     *
     * @param bool $show
     *
     * @return void
     */
    public function setShowWarnings(bool $show): void
    {
        $this->showWarnings = StringHelper::booleanValue($show);
    }

    /**
     * The haltonfailure property
     *
     * @param bool $aValue
     *
     * @return void
     */
    public function setHaltOnFailure(bool $aValue): void
    {
        $this->haltOnFailure = $aValue;
    }

    /**
     * The haltonwarning property
     *
     * @param bool $aValue
     *
     * @return void
     */
    public function setHaltOnWarning(bool $aValue): void
    {
        $this->haltOnWarning = $aValue;
    }

    /**
     * File to be performed syntax check on
     *
     * @param PhingFile $file
     *
     * @return void
     */
    public function setFile(PhingFile $file): void
    {
        $this->file = $file;
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
     * jsl config file
     *
     * @param PhingFile $file
     *
     * @return void
     */
    public function setConfFile(PhingFile $file): void
    {
        $this->conf = $file;
    }

    /**
     * @param string $path
     *
     * @return void
     *
     * @throws BuildException
     */
    public function setExecutable(string $path): void
    {
        $this->executable = $path;

        if (!@file_exists($path)) {
            throw new BuildException(sprintf("JavaScript Lint executable '%s' not found", $path));
        }
    }

    /**
     * @return string
     */
    public function getExecutable(): string
    {
        return $this->executable;
    }

    /**
     * File to save error messages to
     *
     * @param PhingFile $tofile
     *
     * @return void
     */
    public function setToFile(PhingFile $tofile): void
    {
        $this->tofile = $tofile;
    }

    /**
     * Execute lint check against PhingFile or a FileSet
     *
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     * @throws ReflectionException
     */
    public function main(): void
    {
        if (!isset($this->file) && count($this->filesets) == 0) {
            throw new BuildException("Missing either a nested fileset or attribute 'file' set");
        }

        if (empty($this->executable)) {
            throw new BuildException("Missing the 'executable' attribute");
        }

        if ($this->file instanceof PhingFile) {
            $this->lint($this->file->getPath());
        } else { // process filesets
            $project = $this->getProject();
            foreach ($this->filesets as $fs) {
                $ds    = $fs->getDirectoryScanner($project);
                $files = $ds->getIncludedFiles();
                $dir   = $fs->getDir($this->project)->getPath();
                foreach ($files as $file) {
                    $this->lint($dir . DIRECTORY_SEPARATOR . $file);
                }
            }
        }

        // write list of 'bad files' to file (if specified)
        if ($this->tofile) {
            $writer = new FileWriter($this->tofile);

            foreach ($this->badFiles as $file => $messages) {
                foreach ($messages as $msg) {
                    $writer->write($file . '=' . $msg . PHP_EOL);
                }
            }

            $writer->close();
        }

        if ($this->haltOnFailure && $this->hasErrors) {
            throw new BuildException(
                'Syntax error(s) in JS files:' . implode(
                    ', ',
                    array_keys($this->badFiles)
                )
            );
        }
        if ($this->haltOnWarning && $this->hasWarnings) {
            throw new BuildException(
                'Syntax warning(s) in JS files:' . implode(
                    ', ',
                    array_keys($this->badFiles)
                )
            );
        }
    }

    /**
     * Performs the actual syntax check
     *
     * @param string $file
     *
     * @return void
     *
     * @throws IOException
     * @throws BuildException
     * @throws Exception
     */
    protected function lint(string $file): void
    {
        $command = $this->executable . ' -output-format ' . escapeshellarg(
            'file:__FILE__;line:__LINE__;message:__ERROR__'
        ) . ' ';

        if (isset($this->conf)) {
            $command .= '-conf ' . escapeshellarg($this->conf->getPath()) . ' ';
        }

        $command .= '-process ';

        if (file_exists($file)) {
            if (is_readable($file)) {
                if ($this->cache) {
                    $lastmtime = $this->cache->get($file);

                    if ($lastmtime >= filemtime($file)) {
                        $this->log("Not linting '" . $file . "' due to cache", Project::MSG_DEBUG);

                        return;
                    }
                }

                $messages = [];
                exec($command . '"' . $file . '"', $messages, $return);

                if ($return > 100) {
                    throw new BuildException(sprintf("Could not execute Javascript Lint executable '%s'", $this->executable));
                }

                $summary = $messages[count($messages) - 1];

                preg_match('/(\d+)\serror/', (string) $summary, $matches);
                $errorCount = (count($matches) > 1 ? $matches[1] : 0);

                preg_match('/(\d+)\swarning/', (string) $summary, $matches);
                $warningCount = (count($matches) > 1 ? $matches[1] : 0);

                $errors   = [];
                $warnings = [];
                if ($errorCount > 0 || $warningCount > 0) {
                    $last = false;
                    foreach ($messages as $message) {
                        $matches = [];
                        if (preg_match('/^(\.*)\^$/', (string) $message)) {
                            $column = strlen($message);
                            if ($last == 'error') {
                                $errors[count($errors) - 1]['column'] = $column;
                            } else {
                                if ($last == 'warning') {
                                    $warnings[count($warnings) - 1]['column'] = $column;
                                }
                            }
                            $last = false;
                        }
                        if (!preg_match('/^file:(.+);line:(\d+);message:(.+)$/', (string) $message, $matches)) {
                            continue;
                        }
                        $msg  = $matches[3];
                        $data = ['filename' => $matches[1], 'line' => $matches[2], 'message' => $msg];
                        if (preg_match('/^.*error:.+$/i', (string) $msg)) {
                            $errors[] = $data;
                            $last     = 'error';
                        } else {
                            if (preg_match('/^.*warning:.+$/i', (string) $msg)) {
                                $warnings[] = $data;
                                $last       = 'warning';
                            }
                        }
                    }
                }

                if ($this->showWarnings && $warningCount > 0) {
                    $this->log($file . ': ' . $warningCount . ' warnings detected', Project::MSG_WARN);
                    foreach ($warnings as $warning) {
                        $this->log(
                            '- line ' . $warning['line'] . (isset($warning['column']) ? ' column ' . $warning['column'] : '') . ': ' . $warning['message'],
                            Project::MSG_WARN
                        );
                    }
                    $this->hasWarnings = true;
                }

                if ($errorCount > 0) {
                    $this->log($file . ': ' . $errorCount . ' errors detected', Project::MSG_ERR);
                    if (!isset($this->badFiles[$file])) {
                        $this->badFiles[$file] = [];
                    }

                    foreach ($errors as $error) {
                        $message = 'line ' . $error['line'] . (isset($error['column']) ? ' column ' . $error['column'] : '') . ': ' . $error['message'];
                        $this->log('- ' . $message, Project::MSG_ERR);
                        $this->badFiles[$file][] = $message;
                    }
                    $this->hasErrors = true;
                } else {
                    if (!$this->showWarnings || $warningCount == 0) {
                        $this->log($file . ': No syntax errors detected', Project::MSG_VERBOSE);

                        if ($this->cache) {
                            $this->cache->put($file, filemtime($file));
                        }
                    }
                }
            } else {
                throw new BuildException('Permission denied: ' . $file);
            }
        } else {
            throw new BuildException('File not found: ' . $file);
        }
    }
}
