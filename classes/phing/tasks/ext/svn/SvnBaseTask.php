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
 * Base class for Subversion tasks
 *
 * @see   VersionControl_SVN
 *
 * @author Michiel Rook <mrook@php.net>
 * @author Andrew Eddie <andrew.eddie@jamboworks.com>
 * @package phing.tasks.ext.svn
 * @since 2.2.0
 */
abstract class SvnBaseTask extends Task
{
    /**
     * @var string
     */
    private $workingCopy = '';

    /**
     * @var string
     */
    private $repositoryUrl = '';

    /**
     * @var string
     */
    private $svnPath = '/usr/bin/svn';

    protected $svn = null;

    /**
     * @var string
     */
    private $mode = '';

    /**
     * @var array
     */
    private $svnArgs = [];

    /**
     * @var array
     */
    private $svnSwitches = [];

    private $toDir = '';

    protected $fetchMode;

    protected $oldVersion = false;

    /**
     * Initialize Task.
     * This method includes any necessary SVN libraries and triggers
     * appropriate error if they cannot be found.  This is not done in header
     * because we may want this class to be loaded w/o triggering an error.
     *
     * @return void
     *
     * @throws Exception
     */
    public function init(): void
    {
        include_once 'VersionControl/SVN.php';
        $this->fetchMode = VERSIONCONTROL_SVN_FETCHMODE_ASSOC;
        if (!class_exists('VersionControl_SVN')) {
            throw new Exception('The SVN tasks depend on PEAR VersionControl_SVN package being installed.');
        }
    }

    /**
     * Sets the path to the workingcopy
     *
     * @param string $workingCopy
     *
     * @return void
     */
    public function setWorkingCopy(string $workingCopy): void
    {
        $this->workingCopy = $workingCopy;
    }

    /**
     * Returns the path to the workingcopy
     *
     * @return string
     */
    public function getWorkingCopy(): string
    {
        return $this->workingCopy;
    }

    /**
     * Sets the path/URI to the repository
     *
     * @param string $repositoryUrl
     *
     * @return void
     */
    public function setRepositoryUrl(string $repositoryUrl): void
    {
        $this->repositoryUrl = $repositoryUrl;
    }

    /**
     * Returns the path/URI to the repository
     *
     * @return string
     */
    public function getRepositoryUrl(): string
    {
        return $this->repositoryUrl;
    }

    /**
     * Sets the path to the SVN executable
     *
     * @param string $svnPath
     *
     * @return void
     */
    public function setSvnPath(string $svnPath): void
    {
        $this->svnPath = $svnPath;
    }

    /**
     * Returns the path to the SVN executable
     *
     * @return string
     */
    public function getSvnPath(): string
    {
        return $this->svnPath;
    }

    // Args

    /**
     * Sets the path to export/checkout to
     *
     * @param string $toDir
     *
     * @return void
     */
    public function setToDir(string $toDir): void
    {
        $this->toDir = $toDir;
    }

    /**
     * Returns the path to export/checkout to
     *
     * @return string
     */
    public function getToDir(): string
    {
        return $this->toDir;
    }

    // Switches

    /**
     * Sets the force switch
     *
     * @param string $value
     *
     * @return void
     */
    public function setForce(string $value): void
    {
        $this->svnSwitches['force'] = $value;
    }

    /**
     * Returns the force switch
     *
     * @return string
     */
    public function getForce(): string
    {
        return isset($this->svnSwitches['force']) ? $this->svnSwitches['force'] : '';
    }

    /**
     * Sets the username of the user to export
     *
     * @param string $value
     *
     * @return void
     */
    public function setUsername(string $value): void
    {
        $this->svnSwitches['username'] = $value;
    }

    /**
     * Returns the username
     *
     * @return string
     */
    public function getUsername(): string
    {
        return isset($this->svnSwitches['username']) ? $this->svnSwitches['username'] : '';
    }

    /**
     * Sets the password of the user to export
     *
     * @param string $value
     *
     * @return void
     */
    public function setPassword(string $value): void
    {
        $this->svnSwitches['password'] = $value;
    }

    /**
     * Returns the password
     *
     * @return string
     */
    public function getPassword(): string
    {
        return isset($this->svnSwitches['password']) ? $this->svnSwitches['password'] : '';
    }

    /**
     * Sets the no-auth-cache switch
     *
     * @param string $value
     *
     * @return void
     */
    public function setNoCache(string $value): void
    {
        $this->svnSwitches['no-auth-cache'] = $value;
    }

    /**
     * Returns the no-auth-cache switch
     *
     * @return string
     */
    public function getNoCache(): string
    {
        return isset($this->svnSwitches['no-auth-cache']) ? $this->svnSwitches['no-auth-cache'] : '';
    }

    /**
     * Sets the depth switch
     *
     * @param string $value
     *
     * @return void
     */
    public function setDepth(string $value): void
    {
        $this->svnSwitches['depth'] = $value;
    }

    /**
     * Returns the depth switch
     *
     * @return string
     */
    public function getDepth(): string
    {
        return isset($this->svnSwitches['depth']) ? $this->svnSwitches['depth'] : '';
    }

    /**
     * Sets the ignore-externals switch
     *
     * @param string $value
     *
     * @return void
     */
    public function setIgnoreExternals(string $value): void
    {
        $this->svnSwitches['ignore-externals'] = $value;
    }

    /**
     * Returns the ignore-externals switch
     *
     * @return string
     */
    public function getIgnoreExternals(): string
    {
        return isset($this->svnSwitches['ignore-externals']) ? $this->svnSwitches['ignore-externals'] : '';
    }

    /**
     * Sets the trust-server-cert switch
     *
     * @param string $value
     *
     * @return void
     */
    public function setTrustServerCert(string $value): void
    {
        $this->svnSwitches['trust-server-cert'] = $value;
    }

    /**
     * Returns the trust-server-cert switch
     *
     * @return string
     */
    public function getTrustServerCert(): string
    {
        return isset($this->svnSwitches['trust-server-cert']) ? $this->svnSwitches['trust-server-cert'] : '';
    }

    /**
     * Creates a VersionControl_SVN class based on $mode
     *
     * @param string $mode The SVN mode to use (info, export, checkout, ...)
     *
     * @return void
     *
     * @throws VersionControl_SVN_Exception
     * @throws BuildException
     */
    protected function setup(string $mode): void
    {
        $this->mode = $mode;

        // Set up runtime options. Will be passed to all
        // subclasses.
        $options = ['fetchmode' => $this->fetchMode];

        if ($this->oldVersion) {
            $options['svn_path'] = $this->getSvnPath();
        } else {
            $options['binaryPath'] = $this->getSvnPath();
        }

        // Pass array of subcommands we need to factory
        $this->svn = VersionControl_SVN::factory($mode, $options);

        if (get_parent_class($this->svn) !== 'VersionControl_SVN_Command') {
            $this->oldVersion              = true;
            $this->svn->use_escapeshellcmd = false;
        }

        if (!empty($this->repositoryUrl)) {
            $this->svnArgs = [$this->repositoryUrl];
        } else {
            if (!empty($this->workingCopy)) {
                if (is_dir($this->workingCopy)) {
                    $this->svnArgs = [$this->workingCopy];
                } else {
                    if ($mode == 'info') {
                        if (is_file($this->workingCopy)) {
                            $this->svnArgs = [$this->workingCopy];
                        } else {
                            throw new BuildException("'" . $this->workingCopy . "' is not a directory nor a file");
                        }
                    } else {
                        throw new BuildException("'" . $this->workingCopy . "' is not a directory");
                    }
                }
            }
        }
    }

    /**
     * Executes the constructed VersionControl_SVN instance
     *
     * @param array $args     Additional arguments to pass to SVN.
     * @param array $switches Switches to pass to SVN.
     *
     * @return string|array Output generated by SVN.
     *
     * @throws BuildException
     */
    protected function run(array $args = [], array $switches = [])
    {
        $tempArgs     = array_merge($this->svnArgs, $args);
        $tempSwitches = array_merge($this->svnSwitches, $switches);

        if ($this->oldVersion) {
            $svnstack = PEAR_ErrorStack::singleton('VersionControl_SVN');

            if ($output = $this->svn->run($tempArgs, $tempSwitches)) {
                return $output;
            }

            if (count($errs = $svnstack->getErrors())) {
                $err          = current($errs);
                $errorMessage = $err['message'];

                if (isset($err['params']['errstr'])) {
                    $errorMessage = $err['params']['errstr'];
                }

                throw new BuildException("Failed to run the 'svn " . $this->mode . "' command: " . $errorMessage);
            }
        } else {
            try {
                return $this->svn->run($tempArgs, $tempSwitches);
            } catch (Throwable $e) {
                throw new BuildException("Failed to run the 'svn " . $this->mode . "' command: " . $e->getMessage());
            }
        }

        return '';
    }
}
