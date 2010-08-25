<?php
/*
 * $Id$
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
 * A HTTP request task.
 * Making an HTTP request and try to match the response against an provided
 * regular expression.
 *
 * @package phing.tasks.ext
 * @author  Ole Markus With <o.with@sportradar.com>
 * @version $Id$
 */
class HttpGetTask extends Task
{
    /**
     * Holds the request URL
     *
     * @var string
     */
    protected $url = null;

    /**
     * Holds the save location
     *
     * @var string
     */
    protected $dir = null;


    /**
     * Load the necessary environment for running this task.
     *
     * @throws BuildException
     */
    public function init()
    {
        require_once 'HTTP/Request2.php';
    }


    /**
     * Make the GET request
     *
     * @throws BuildException
     */
    public function main()
    {
        if (!isset($this->url)) {
            throw new BuildException("Missing attribute 'url'");
        }

        if (!isset($this->dir)) {
            throw new BuildException("Missing attribute 'dir'");
        }

	$this->log("Fetching " . $this->url);

        $request = new HTTP_Request2($this->url);
	$response =  $request->send();
	if ($response->getStatus() != 200) {
		throw new BuildException("Request unsuccessfull. Response from server: " . $response->getStatus() . " " . $response->getReasonPhrase());
	}
	$content = $response->getBody();
	if ($this->filename) {
		$filename = $this->filename;
	} elseif ($disposition = $response->getHeader('content-disposition')
	        && 0 == strpos($disposition, 'attachment')
		&& preg_match('/filename="([^"]+)"/', $disposition, $m)) {
		$filename = basename($m[1]);
	} else {
		$filename = basename(parse_url($this->url, PHP_URL_PATH));
	}

	if (!is_writable($this->dir)) {
		throw new BuildException("Cannot write to directory: " . $this->dir);
	}
	$filename = $this->dir . "/" . $filename;
	file_put_contents($filename, $content);
	$this->log("Contents from " . $this->url . " saved to $filename");
    }

    function setUrl($url) {
        $this->url = $url;
    }

    function setFilename($filename) {
        $this->filename = $filename;
    }

    function setDir($dir) {
        $this->dir = $dir;
    }

}
