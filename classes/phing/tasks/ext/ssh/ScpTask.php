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
 * Copy files to and from a remote host using scp.
 *
 * @author  Michiel Rook <mrook@php.net>
 * @author  Johan Van den Brande <johan@vandenbrande.com>
 * @package phing.tasks.ext
 */
class ScpTask extends Task
{
    use FileSetAware;
    use LogLevelAware;

    /**
     * @var string
     */
    protected $file = '';

    /**
     * @var string
     */
    protected $todir = '';

    /**
     * @var int|null
     */
    protected $mode = null;

    /**
     * @var string
     */
    protected $host = '';

    /**
     * @var int
     */
    protected $port    = 22;
    protected $methods = null;

    /**
     * @var string
     */
    protected $username = '';

    /**
     * @var string
     */
    protected $password = '';

    /**
     * @var bool
     */
    protected $autocreate = true;

    /**
     * @var bool
     */
    protected $fetch = false;

    /**
     * @var string
     */
    protected $pubkeyfile = '';

    /**
     * @var string
     */
    protected $privkeyfile = '';

    /**
     * @var string
     */
    protected $privkeyfilepassphrase = '';

    protected $connection = null;
    protected $sftp       = null;

    /**
     * @var int
     */
    protected $counter = 0;

    /**
     * If number of success of "sftp" is grater than declared number
     * decide to skip "scp" operation.
     *
     * @var int
     */
    protected $heuristicDecision = 5;

    /**
     * Indicate number of failures in sending files via "scp" over "sftp"
     *
     * - If number is negative - scp & sftp failed
     * - If number is positive - scp failed & sftp succeed
     * - If number is 0 - scp succeed
     *
     * @var int
     */
    protected $heuristicScpSftp = 0;

    /**
     * Sets the remote host
     *
     * @param string $h
     *
     * @return void
     */
    public function setHost(string $h): void
    {
        $this->host = $h;
    }

    /**
     * Returns the remote host
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Sets the remote host port
     *
     * @param int $p
     *
     * @return void
     */
    public function setPort(int $p): void
    {
        $this->port = $p;
    }

    /**
     * Returns the remote host port
     *
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * Sets the mode value
     *
     * @param int $value
     *
     * @return void
     */
    public function setMode(int $value): void
    {
        $this->mode = $value;
    }

    /**
     * Returns the mode value
     *
     * @return int|null
     */
    public function getMode(): ?int
    {
        return $this->mode;
    }

    /**
     * Sets the username of the user to scp
     *
     * @param string $username
     *
     * @return void
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * Returns the username
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Sets the password of the user to scp
     *
     * @param string $password
     *
     * @return void
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * Returns the password
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Sets the public key file of the user to scp
     *
     * @param string $pubkeyfile
     *
     * @return void
     */
    public function setPubkeyfile(string $pubkeyfile): void
    {
        $this->pubkeyfile = $pubkeyfile;
    }

    /**
     * Returns the pubkeyfile
     *
     * @return string
     */
    public function getPubkeyfile(): string
    {
        return $this->pubkeyfile;
    }

    /**
     * Sets the private key file of the user to scp
     *
     * @param string $privkeyfile
     *
     * @return void
     */
    public function setPrivkeyfile(string $privkeyfile): void
    {
        $this->privkeyfile = $privkeyfile;
    }

    /**
     * Returns the private keyfile
     *
     * @return string
     */
    public function getPrivkeyfile(): string
    {
        return $this->privkeyfile;
    }

    /**
     * Sets the private key file passphrase of the user to scp
     *
     * @param string $privkeyfilepassphrase
     *
     * @return void
     */
    public function setPrivkeyfilepassphrase(string $privkeyfilepassphrase): void
    {
        $this->privkeyfilepassphrase = $privkeyfilepassphrase;
    }

    /**
     * Returns the private keyfile passphrase
     *
     * @return string
     */
    public function getPrivkeyfilepassphrase(): string
    {
        return $this->privkeyfilepassphrase;
    }

    /**
     * Sets whether to autocreate remote directories
     *
     * @param bool $autocreate
     *
     * @return void
     */
    public function setAutocreate(bool $autocreate): void
    {
        $this->autocreate = $autocreate;
    }

    /**
     * Returns whether to autocreate remote directories
     *
     * @return bool
     */
    public function getAutocreate(): bool
    {
        return $this->autocreate;
    }

    /**
     * Set destination directory
     *
     * @param string $todir
     *
     * @return void
     */
    public function setTodir(string $todir): void
    {
        $this->todir = $todir;
    }

    /**
     * Returns the destination directory
     *
     * @return string
     */
    public function getTodir(): string
    {
        return $this->todir;
    }

    /**
     * Sets local filename
     *
     * @param string $file
     *
     * @return void
     */
    public function setFile(string $file): void
    {
        $this->file = $file;
    }

    /**
     * Returns local filename
     *
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * Sets whether to send (default) or fetch files
     *
     * @param bool $fetch
     *
     * @return void
     */
    public function setFetch(bool $fetch): void
    {
        $this->fetch = $fetch;
    }

    /**
     * Returns whether to send (default) or fetch files
     *
     * @return bool
     */
    public function getFetch(): bool
    {
        return $this->fetch;
    }

    /**
     * Declare number of successful operations above which "sftp" will be chosen over "scp".
     *
     * @param int $heuristicDecision Number
     *
     * @return void
     */
    public function setHeuristicDecision(int $heuristicDecision): void
    {
        $this->heuristicDecision = (int) $heuristicDecision;
    }

    /**
     * Get declared number of successful operations above which "sftp" will be chosen over "scp".
     *
     * @return int
     */
    public function getHeuristicDecision(): int
    {
        return $this->heuristicDecision;
    }

    /**
     * Creates an Ssh2MethodParam object. Handles the <sshconfig /> nested tag
     *
     * @return Ssh2MethodParam
     */
    public function createSshconfig(): Ssh2MethodParam
    {
        $this->methods = new Ssh2MethodParam();

        return $this->methods;
    }

    /**
     * @return void
     */
    public function init(): void
    {
    }

    /**
     * @return void
     *
     * @throws NullPointerException
     * @throws ReflectionException
     * @throws Exception
     * @throws IOException
     */
    public function main(): void
    {
        $p = $this->getProject();

        if (!function_exists('ssh2_connect')) {
            throw new BuildException('To use ScpTask, you need to install the PHP SSH2 extension.');
        }

        if ($this->file == '' && empty($this->filesets)) {
            throw new BuildException("Missing either a nested fileset or attribute 'file'");
        }

        if ($this->host == '' || $this->username == '') {
            throw new BuildException("Attribute 'host' and 'username' must be set");
        }

        $methods          = !empty($this->methods) ? $this->methods->toArray($p) : [];
        $this->connection = ssh2_connect($this->host, $this->port, $methods);
        if (!$this->connection) {
            throw new BuildException('Could not establish connection to ' . $this->host . ':' . $this->port . '!');
        }

        $could_auth = null;
        if ($this->pubkeyfile) {
            $could_auth = ssh2_auth_pubkey_file(
                $this->connection,
                $this->username,
                $this->pubkeyfile,
                $this->privkeyfile,
                $this->privkeyfilepassphrase
            );
        } else {
            $could_auth = ssh2_auth_password($this->connection, $this->username, $this->password);
        }
        if (!$could_auth) {
            throw new BuildException('Could not authenticate connection!');
        }

        // prepare sftp resource
        if ($this->autocreate) {
            $this->sftp = ssh2_sftp($this->connection);
        }

        if ($this->file != '') {
            $this->copyFile($this->file, basename($this->file));
        } else {
            if ($this->fetch) {
                throw new BuildException('Unable to use filesets to retrieve files from remote server');
            }

            foreach ($this->filesets as $fs) {
                $ds    = $fs->getDirectoryScanner($this->project);
                $files = $ds->getIncludedFiles();
                $dir   = $fs->getDir($this->project)->getPath();
                foreach ($files as $file) {
                    $path = $dir . DIRECTORY_SEPARATOR . $file;

                    // Translate any Windows paths
                    $this->copyFile($path, strtr($file, '\\', '/'));
                }
            }
        }

        $this->log(
            'Copied ' . $this->counter . ' file(s) ' . ($this->fetch ? 'from' : 'to') . " '" . $this->host . "'"
        );

        // explicitly close ssh connection
        @ssh2_exec($this->connection, 'exit');
    }

    /**
     * @param string $local
     * @param string $remote
     *
     * @return void
     *
     * @throws Exception
     * @throws BuildException
     */
    protected function copyFile(string $local, string $remote): void
    {
        $path = rtrim($this->todir, '/') . '/';

        if ($this->fetch) {
            $localEndpoint  = $path . $remote;
            $remoteEndpoint = $local;

            $this->log('Will fetch ' . $remoteEndpoint . ' to ' . $localEndpoint, $this->logLevel);

            $ret = @ssh2_scp_recv($this->connection, $remoteEndpoint, $localEndpoint);

            if ($ret === false) {
                throw new BuildException("Could not fetch remote file '" . $remoteEndpoint . "'");
            }
        } else {
            $localEndpoint  = $local;
            $remoteEndpoint = $path . $remote;

            if ($this->autocreate) {
                ssh2_sftp_mkdir(
                    $this->sftp,
                    dirname($remoteEndpoint),
                    ($this->mode ?? 0777),
                    true
                );
            }

            $this->log('Will copy ' . $localEndpoint . ' to ' . $remoteEndpoint, $this->logLevel);

            $ret = false;
            // If more than "$this->heuristicDecision" successfully send files by "ssh2.sftp" over "ssh2_scp_send"
            // then ship this step (task finish ~40% faster)
            if ($this->heuristicScpSftp < $this->heuristicDecision) {
                if (null !== $this->mode) {
                    $ret = @ssh2_scp_send($this->connection, $localEndpoint, $remoteEndpoint, $this->mode);
                } else {
                    $ret = @ssh2_scp_send($this->connection, $localEndpoint, $remoteEndpoint);
                }
            }

            // sometimes remote server allow only create files via sftp (eg. phpcloud.com)
            if (false === $ret && $this->sftp) {
                // mark failure of "scp"
                --$this->heuristicScpSftp;

                // try create file via ssh2.sftp://file wrapper
                $fh = @fopen('ssh2.sftp://' . $this->sftp . '/' . $remoteEndpoint, 'wb');
                if (is_resource($fh)) {
                    $ret = fwrite($fh, file_get_contents($localEndpoint));
                    fclose($fh);

                    // mark success of "sftp"
                    $this->heuristicScpSftp += 2;
                }
            }

            if ($ret === false) {
                throw new BuildException("Could not create remote file '" . $remoteEndpoint . "'");
            }
        }

        $this->counter++;
    }
}
