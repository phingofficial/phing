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
 * Copy files to and from a remote host using scp.
 *
 * @author    Johan Van den Brande <johan@vandenbrande.com>
 * @version   $Revision$
 * @package   phing.tasks.ext
 */

class ScpTask extends Task
{
    private $file = "";
    private $todir = "";
    private $mode = null;

    private $host = "";
	private $port = 22;
	private $username = "";
	private $password = "";
    private $localEndpoint = "";
    private $remoteEndpoint = "";

	function setMode($mode)
	{
		$this->mode = $mode;
	}

	function getMode()
	{
		return $this->mode;
	}

	function setTodir($todir)
	{
		$this->todir = $todir;
	}

	function getTodir()
	{
		return $this->todir;
	}

	function setFile($file)
	{
		$this->file = $file;
	}

	function getFile()
	{
		return $this->file;
	}

	public function init()
    {
        if (!function_exists('ssh2_connect')) { 
            throw new BuildException("To use ScpTask, you need to install the SSH extension.");
        }
        return TRUE;
	}

	public function main()
    {
        $this->determineEndpoints();

        $connection = ssh2_connect($this->host, $this->port);
        if (is_null($connection)) {
            throw new BuildException("Could not establish connection to " . $this->host . ":" . $this->port . "!");
        }

        $could_auth = ssh2_auth_password($connection, $this->username, $this->password);
        if (!$could_auth) {
            throw new BuildException("Could not authenticate connection!");
        }


        if (!is_null($this->mode)) {
            ssh2_scp_send($connection, $this->localEndpoint, $this->remoteEndpoint, $this->mode);
        } else {
            ssh2_scp_send($connection, $this->localEndpoint, $this->remoteEndpoint);
        }
    }

    private function isRemote($url)
    {
        return strstr($url, "@");
    }

    private function assignEndpoints($protocol, $localEndpoint)
    {
        $this->host = $protocol['host'];
        if ($protocol['port']) $this->port = $protocol['port'];
        $this->username = $protocol['user'];
        $this->password = $protocol['pass'];

        $path = $protocol['path'];
        if ($path) {
            $path= rtrim($path, "/") . "/";
        }
        $this->remoteEndpoint = $path . basename($localEndpoint);
        $this->localEndpoint = $localEndpoint;
   }

    private function determineEndpoints()
    {
        // determine fetch or send (which part is remote?)
        if ($this->isRemote($this->file)) {
            // Fetch from remote
            $protocol = parse_url("scp://" . $this->file, PHP_URL_PATH);
            $this->assignEndpoints($protocol, $this->todir);
        } 
        elseif ($this->isRemote($this->todir)) {
            // Send to remote
            $protocol = parse_url("scp://" . $this->todir);
            $this->assignEndpoints($protocol, $this->file);
        }
    }
}
?>
