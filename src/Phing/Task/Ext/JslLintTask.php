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

namespace Phing\Task\Ext;

use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Io\FileWriter;
use Phing\Project;
use Phing\Task;
use Phing\Type\Element\FileSetAware;
use Phing\Util\DataStore;
use Phing\Util\StringHelper;

/**
 * A Javascript lint task. Checks syntax of Javascript files.
 * Javascript lint (http://www.javascriptlint.com) must be in the system path.
 * This class is based on Knut Urdalen's PhpLintTask.
 *
 * @author Stefan Priebsch <stefan.priebsch@e-novative.de>
 */
class JslLintTask extends Task
{
    use FileSetAware;

    /**
     * @var File
     */
    protected $file; // the source file (from xml attribute)

    /**
     * @var bool
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
     * @var File
     */
    protected $tofile;

    /**
     * @var array
     */
    private $badFiles = [];

    /**
     * @var DataStore
     */
    private $cache;

    /**
     * @var File
     */
    private $conf;

    /**
     * @var string
     */
    private $executable = 'jsl';

    /**
     * Sets the flag if warnings should be shown.
     *
     * @param bool $show
     */
    public function setShowWarnings($show)
    {
        $this->showWarnings = StringHelper::booleanValue($show);
    }

    /**
     * The haltonfailure property.
     *
     * @param bool $aValue
     */
    public function setHaltOnFailure($aValue)
    {
        $this->haltOnFailure = $aValue;
    }

    /**
     * The haltonwarning property.
     *
     * @param bool $aValue
     */
    public function setHaltOnWarning($aValue)
    {
        $this->haltOnWarning = $aValue;
    }

    /**
     * File to be performed syntax check on.
     */
    public function setFile(File $file)
    {
        $this->file = $file;
    }

    /**
     * Whether to store last-modified times in cache.
     */
    public function setCacheFile(File $file)
    {
        $this->cache = new DataStore($file);
    }

    /**
     * jsl config file.
     */
    public function setConfFile(File $file)
    {
        $this->conf = $file;
    }

    /**
     * @param string $path
     *
     * @throws BuildException
     */
    public function setExecutable($path)
    {
        $this->executable = $path;

        if (!@file_exists($path)) {
            throw new BuildException("JavaScript Lint executable '{$path}' not found");
        }
    }

    /**
     * @return string
     */
    public function getExecutable()
    {
        return $this->executable;
    }

    /**
     * File to save error messages to.
     */
    public function setToFile(File $tofile)
    {
        $this->tofile = $tofile;
    }

    /**
     * Execute lint check against PhingFile or a FileSet.
     */
    public function main()
    {
        if (!isset($this->file) and 0 == count($this->filesets)) {
            throw new BuildException("Missing either a nested fileset or attribute 'file' set");
        }

        if (empty($this->executable)) {
            throw new BuildException("Missing the 'executable' attribute");
        }

        if ($this->file instanceof File) {
            $this->lint($this->file->getPath());
        } else { // process filesets
            $project = $this->getProject();
            foreach ($this->filesets as $fs) {
                $ds = $fs->getDirectoryScanner($project);
                $files = $ds->getIncludedFiles();
                $dir = $fs->getDir($this->project)->getPath();
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
     * Performs the actual syntax check.
     *
     * @param string $file
     *
     * @throws BuildException
     *
     * @return bool|void
     */
    protected function lint($file)
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

                        return false;
                    }
                }

                $messages = [];
                exec($command . '"' . $file . '"', $messages, $return);

                if ($return > 100) {
                    throw new BuildException("Could not execute Javascript Lint executable '{$this->executable}'");
                }

                $summary = $messages[count($messages) - 1];

                preg_match('/(\d+)\serror/', $summary, $matches);
                $errorCount = (count($matches) > 1 ? $matches[1] : 0);

                preg_match('/(\d+)\swarning/', $summary, $matches);
                $warningCount = (count($matches) > 1 ? $matches[1] : 0);

                $errors = [];
                $warnings = [];
                if ($errorCount > 0 || $warningCount > 0) {
                    $last = false;
                    foreach ($messages as $message) {
                        $matches = [];
                        if (preg_match('/^(\.*)\^$/', $message)) {
                            $column = strlen($message);
                            if ('error' == $last) {
                                $errors[count($errors) - 1]['column'] = $column;
                            } else {
                                if ('warning' == $last) {
                                    $warnings[count($warnings) - 1]['column'] = $column;
                                }
                            }
                            $last = false;
                        }
                        if (!preg_match('/^file:(.+);line:(\d+);message:(.+)$/', $message, $matches)) {
                            continue;
                        }
                        $msg = $matches[3];
                        $data = ['filename' => $matches[1], 'line' => $matches[2], 'message' => $msg];
                        if (preg_match('/^.*error:.+$/i', $msg)) {
                            $errors[] = $data;
                            $last = 'error';
                        } else {
                            if (preg_match('/^.*warning:.+$/i', $msg)) {
                                $warnings[] = $data;
                                $last = 'warning';
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
                    if (!$this->showWarnings || 0 == $warningCount) {
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
