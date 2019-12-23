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
 * Execute commands on a remote host using ssh.
 *
 * @author  Johan Van den Brande <johan@vandenbrande.com>
 * @package phing.tasks.ext
 */
class SshTask extends Task
{
    /**
     * @var string
     */
    private $host = '';

    /**
     * @var int
     */
    private $port = 22;

    /**
     * @var Ssh2MethodParam
     */
    private $methods = null;

    /**
     * @var string
     */
    private $username = '';

    /**
     * @var string
     */
    private $password = '';

    /**
     * @var string
     */
    private $command = '';

    /**
     * @var string
     */
    private $pubkeyfile = '';

    /**
     * @var string
     */
    private $privkeyfile = '';

    /**
     * @var string
     */
    private $privkeyfilepassphrase = '';

    /**
     * @var string
     */
    private $pty = '';

    /**
     * @var bool
     */
    private $failonerror = false;

    /**
     * The name of the property to capture (any) output of the command
     *
     * @var string
     */
    private $property = '';

    /**
     * Whether to display the output of the command
     *
     * @var bool
     */
    private $display = true;

    /**
     * @var resource
     */
    private $connection;

    /**
     * @param string $host
     *
     * @return void
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param int $port
     *
     * @return void
     */
    public function setPort(int $port): void
    {
        $this->port = $port;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @param string $username
     *
     * @return void
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $password
     *
     * @return void
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
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
     * @param string $command
     *
     * @return void
     */
    public function setCommand(string $command): void
    {
        $this->command = $command;
    }

    /**
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * @param string $pty
     *
     * @return void
     */
    public function setPty(string $pty): void
    {
        $this->pty = $pty;
    }

    /**
     * @return string
     */
    public function getPty(): string
    {
        return $this->pty;
    }

    /**
     * Sets the name of the property to capture (any) output of the command
     *
     * @param string $property
     *
     * @return void
     */
    public function setProperty(string $property): void
    {
        $this->property = $property;
    }

    /**
     * Sets whether to display the output of the command
     *
     * @param bool $display
     *
     * @return void
     */
    public function setDisplay(bool $display): void
    {
        $this->display = (bool) $display;
    }

    /**
     * Sets whether to fail the task on any error
     *
     * @param bool $failonerror
     *
     * @return void
     */
    public function setFailonerror(bool $failonerror): void
    {
        $this->failonerror = (bool) $failonerror;
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
     * Initiates a ssh connection and stores
     * it in $this->connection
     *
     * @return void
     */
    protected function setupConnection(): void
    {
        $p = $this->getProject();

        if (!function_exists('ssh2_connect')) {
            throw new BuildException('To use SshTask, you need to install the PHP SSH2 extension.');
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
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function main(): void
    {
        $this->setupConnection();

        if ($this->pty != '') {
            $stream = ssh2_exec($this->connection, $this->command, $this->pty);
        } else {
            $stream = ssh2_exec($this->connection, $this->command);
        }

        $this->handleStream($stream);
    }

    /**
     * This function reads the streams from the ssh2_exec
     * command, stores output data, checks for errors and
     * closes the streams properly.
     *
     * @param resource $stream
     *
     * @return void
     *
     * @throws Exception
     * @throws BuildException
     */
    protected function handleStream($stream): void
    {
        if (!$stream) {
            throw new BuildException('Could not execute command!');
        }

        $this->log('Executing command ' . $this->command, Project::MSG_VERBOSE);

        stream_set_blocking($stream, true);
        $result = stream_get_contents($stream);

        // always load contents of error stream, to make sure not one command failed
        $stderr_stream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
        stream_set_blocking($stderr_stream, true);
        $result_error = stream_get_contents($stderr_stream);

        if ($this->display) {
            print $result;
        }

        if (!empty($this->property)) {
            $this->project->setProperty($this->property, $result);
        }

        fclose($stream);
        if (isset($stderr_stream)) {
            fclose($stderr_stream);
        }

        if ($this->failonerror && !empty($result_error)) {
            throw new BuildException('SSH Task failed: ' . $result_error);
        }
    }
}
