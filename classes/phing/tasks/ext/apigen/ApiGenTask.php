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
 * ApiGen task (http://apigen.org).
 *
 * @package phing.tasks.ext.apigen
 * @author  Martin Srank <martin@smasty.net>
 * @author  Jaroslav Hanslík <kukulich@kukulich.cz>
 * @author  Lukáš Homza <lukashomza@gmail.com>
 * @since   2.4.10
 */
class ApiGenTask extends Task
{
    /**
     * Default ApiGen executable name.
     *
     * @var string
     */
    private $executable = 'apigen';

    /**
     * Default ApiGen action.
     *
     * @var string
     */
    private $action = 'generate';

    /**
     * Default ApiGen options.
     *
     * @var array
     */
    private $options = [];

    /**
     * Sets the ApiGen executable name.
     *
     * @param string $executable
     *
     * @return void
     */
    public function setExecutable(string $executable): void
    {
        $this->executable = (string) $executable;
    }

    /**
     * Sets the ApiGen action to be executed.
     *
     * @param string $action
     *
     * @return void
     */
    public function setAction(string $action): void
    {
        $this->action = (string) $action;
    }

    /**
     * Sets the config file name.
     *
     * @param string $config
     *
     * @return void
     */
    public function setConfig(string $config): void
    {
        $this->options['config'] = (string) $config;
    }

    /**
     * Sets source files or directories.
     *
     * @param string $source
     *
     * @return void
     */
    public function setSource(string $source): void
    {
        $this->options['source'] = explode(',', $source);
    }

    /**
     * Sets the destination directory.
     *
     * @param string $destination
     *
     * @return void
     */
    public function setDestination(string $destination): void
    {
        $this->options['destination'] = (string) $destination;
    }

    /**
     * Sets list of allowed file extensions.
     *
     * @param string $extensions
     *
     * @return void
     */
    public function setExtensions(string $extensions): void
    {
        $this->options['extensions'] = explode(',', $extensions);
    }

    /**
     * Sets masks (case sensitive) to exclude files or directories from processing.
     *
     * @param string $exclude
     *
     * @return void
     */
    public function setExclude(string $exclude): void
    {
        $this->options['exclude'] = explode(',', $exclude);
    }

    /**
     * Sets masks to exclude elements from documentation generating.
     *
     * @param string $skipDocPath
     *
     * @return void
     */
    public function setSkipDocPath(string $skipDocPath): void
    {
        $this->options['skip-doc-path'] = explode(',', $skipDocPath);
    }

    /**
     * Sets the character set of source files.
     *
     * @param string $charset
     *
     * @return void
     */
    public function setCharset(string $charset): void
    {
        $this->options['charset'] = explode(',', $charset);
    }

    /**
     * Sets the main project name prefix.
     *
     * @param string $main
     *
     * @return void
     */
    public function setMain(string $main): void
    {
        $this->options['main'] = (string) $main;
    }

    /**
     * Sets the title of generated documentation.
     *
     * @param string $title
     *
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->options['title'] = (string) $title;
    }

    /**
     * Sets the documentation base URL.
     *
     * @param string $baseUrl
     *
     * @return void
     */
    public function setBaseUrl(string $baseUrl): void
    {
        $this->options['base-url'] = (string) $baseUrl;
    }

    /**
     * Sets the Google Custom Search ID.
     *
     * @param string $googleCseId
     *
     * @return void
     */
    public function setGoogleCseId(string $googleCseId): void
    {
        $this->options['google-cse-id'] = (string) $googleCseId;
    }

    /**
     * Sets the Google Custom Search label.
     *
     * @param string $googleCseLabel
     *
     * @return void
     */
    public function setGoogleCseLabel(string $googleCseLabel): void
    {
        $this->options['google-cse-label'] = (string) $googleCseLabel;
    }

    /**
     * Sets the Google Analytics tracking code.
     *
     * @param string $googleAnalytics
     *
     * @return void
     */
    public function setGoogleAnalytics(string $googleAnalytics): void
    {
        $this->options['google-analytics'] = (string) $googleAnalytics;
    }

    /**
     * Sets the template config file name.
     *
     * @param string $templateConfig
     *
     * @return void
     */
    public function setTemplateConfig(string $templateConfig): void
    {
        $this->options['template-config'] = (string) $templateConfig;
    }

    /**
     * Sets the template config file name.
     *
     * @param string $templateTheme
     *
     * @return void
     */
    public function setTemplateTheme(string $templateTheme): void
    {
        $this->options['template-theme'] = (string) $templateTheme;
    }

    /**
     * Sets how elements should be grouped in the menu.
     *
     * @param string $groups
     *
     * @return void
     */
    public function setGroups(string $groups): void
    {
        $this->options['groups'] = (string) $groups;
    }

    /**
     * Sets the element access levels.
     *
     * Documentation only for methods and properties with the given access level will be generated.
     *
     * @param string $accessLevels
     *
     * @return void
     */
    public function setAccessLevels(string $accessLevels): void
    {
        $this->options['access-levels'] = (string) $accessLevels;
    }

    /**
     * Sets the element access levels.
     *
     * Documentation only for methods and properties with the given access level will be generated.
     *
     * @param string $annotationGroups
     *
     * @return void
     */
    public function setAnnotationGroups(string $annotationGroups): void
    {
        $this->options['annotation-groups'] = (string) $annotationGroups;
    }

    /**
     * Sets if documentation for elements marked as internal and internal documentation parts should be generated.
     *
     * @param bool $internal
     *
     * @return void
     */
    public function setInternal(bool $internal): void
    {
        if ($internal) {
            $this->options['internal'] = null;
        }
    }

    /**
     * Sets if documentation for PHP internal classes should be generated.
     *
     * @param bool $php
     *
     * @return void
     */
    public function setPhp(bool $php): void
    {
        if ($php) {
            $this->options['php'] = null;
        }
    }

    /**
     * Sets if tree view of classes, interfaces, traits and exceptions should be generated.
     *
     * @param bool $tree
     *
     * @return void
     */
    public function setTree(bool $tree): void
    {
        if ($tree) {
            $this->options['tree'] = null;
        }
    }

    /**
     * Sets if documentation for deprecated elements should be generated.
     *
     * @param bool $deprecated
     *
     * @return void
     */
    public function setDeprecated(bool $deprecated): void
    {
        if ($deprecated) {
            $this->options['deprecated'] = null;
        }
    }

    /**
     * Sets if documentation of tasks should be generated.
     *
     * @param bool $todo
     *
     * @return void
     */
    public function setTodo(bool $todo): void
    {
        if ($todo) {
            $this->options['todo'] = null;
        }
    }

    /**
     * Sets if highlighted source code files should be generated.
     *
     * @param bool $noSourceCode
     *
     * @return void
     */
    public function setSourceCode(bool $noSourceCode): void
    {
        if (!$noSourceCode) {
            $this->options['no-source-code'] = null;
        }
    }

    /**
     * Sets if highlighted source code files should not be generated.
     *
     * @deprecated use {@link setSourceCode} instead
     *
     * @param bool $noSourceCode
     *
     * @return void
     */
    public function setNoSourceCode(bool $noSourceCode): void
    {
        $this->setSourceCode(!$noSourceCode);
    }

    /**
     * Sets if a link to download documentation as a ZIP archive should be generated.
     *
     * @param bool $download
     *
     * @return void
     */
    public function setDownload(bool $download): void
    {
        if ($download) {
            $this->options['download'] = null;
        }
    }

    /**
     * Enables/disables the debug mode.
     *
     * @param bool $debug
     *
     * @return void
     */
    public function setDebug(bool $debug): void
    {
        if ($debug) {
            $this->options['debug'] = null;
        }
    }

    /**
     * Runs ApiGen.
     *
     * @see    Task::main()
     *
     * @return void
     *
     * @throws BuildException If something is wrong.
     */
    public function main(): void
    {
        if ('apigen' !== $this->executable && !is_file($this->executable)) {
            throw new BuildException(sprintf('Executable %s not found', $this->executable), $this->getLocation());
        }

        if (!empty($this->options['config'])) {
            // Config check
            if (!is_file($this->options['config'])) {
                throw new BuildException(
                    sprintf(
                        'Config file %s doesn\'t exist',
                        $this->options['config']
                    ),
                    $this->getLocation()
                );
            }
        } else {
            // Source check
            if (empty($this->options['source'])) {
                throw new BuildException('Source is not set', $this->getLocation());
            }
            // Destination check
            if (empty($this->options['destination'])) {
                throw new BuildException('Destination is not set', $this->getLocation());
            }
        }

        // Source check
        if (!empty($this->options['source'])) {
            foreach ($this->options['source'] as $source) {
                if (!file_exists($source)) {
                    throw new BuildException(sprintf('Source %s doesn\'t exist', $source), $this->getLocation());
                }
            }
        }

        // Execute ApiGen
        exec(
            escapeshellcmd($this->executable) . ' ' . escapeshellcmd($this->action) . ' ' . $this->constructArguments(),
            $output,
            $return
        );

        $logType = 0 === $return ? Project::MSG_INFO : Project::MSG_ERR;
        foreach ($output as $line) {
            $this->log($line, $logType);
        }
    }

    /**
     * Generates command line arguments for the ApiGen executable.
     *
     * @return string
     */
    protected function constructArguments(): string
    {
        $args = [];
        foreach ($this->options as $option => $value) {
            if (is_bool($value)) {
                $args[] = '--' . $option . '=' . ($value ? 'yes' : 'no');
            } elseif (is_array($value)) {
                foreach ($value as $v) {
                    $args[] = '--' . $option . '=' . escapeshellarg($v);
                }
            } else {
                $args[] = '--' . $option . '=' . escapeshellarg($value);
            }
        }

        return implode(' ', $args);
    }
}
