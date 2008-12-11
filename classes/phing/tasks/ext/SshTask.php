<?php
/*
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
 * Execute commands on a remote host using ssh.
 *
 * @author    Johan Van den Brande <johan@vandenbrande.com>
 * @version   $Revision$
 * @package   phing.tasks.ext
 */

class SshTask extends Task {

    private $host = "";
    private $port = 22;
    private $username = "";
    private $password = "";
    private $command = "";

    public function setHost($host) 
    {
        $this->host = $host;
    }

    public function getHost() 
    {
        return $this->host;
    }

    public function setPort($port) 
    {
        $this->port = $port;
    }

    public function getPort() 
    {
        return $this->port;
    }

    public function setUsername($username) 
    {
        $this->username = $username;
    }

    public function getUsername() 
    {
        return $this->username;
    }

    public function setPassword($password) 
    {
        $this->password = $password;
    }

    public function getPassword() 
    {
        return $this->password;
    }

    public function setCommand($command) 
    {
        $this->command = $command;
    }

    public function getCommand() 
    {
        return $this->command;
    }

    public function init() 
    {
        if (!function_exists('ssh2_connect')) { 
            throw new BuildException("To use SshTask, you need to install the SSH extension.");
        }
        return TRUE;
    }

    public function main() 
    {
        $connection = ssh2_connect($this->host, $this->port);
        if (is_null($connection)) {
            throw new BuildException("Could not establish connection to " . $this->host . ":" . $this->port . "!");
        }

        $could_auth = ssh2_auth_password($connection, $this->username, $this->password);
        if (!$could_auth) {
            throw new BuildException("Could not authenticate connection!");
        }

        $stream = ssh2_exec($connection, $this->command);
        if (!$stream) {
            throw new BuildException("Could not execute command!");
        }

        stream_set_blocking( $stream, true );
        while( $buf = fread($stream,4096) ){
            print($buf);
        }
        fclose($stream);
    }
}
?>
