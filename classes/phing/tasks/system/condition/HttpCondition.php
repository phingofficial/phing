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

/**
 * Condition to wait for a HTTP request to succeed.
 *
 * Attributes are:
 * - url - the URL of the request.
 * - errorsBeginAt - number at which errors begin at; default=400.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system.condition
 */
class HttpCondition extends ProjectComponent implements Condition
{
    const DEFAULT_REQUEST_METHOD = 'GET';

    private $errorsBeginAt = 400;
    private $url;
    private $quiet = false;
    private $requestMethod = self::DEFAULT_REQUEST_METHOD;
    private $followRedirects = true;

    /**
     * Set the url attribute.
     *
     * @param string $url the url of the request
     *
     * @return void
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Set the errorsBeginAt attribute.
     *
     * @param string $errorsBeginAt number at which errors begin at, default is 400
     *
     * @return void
     */
    public function setErrorsBeginAt($errorsBeginAt)
    {
        $this->errorsBeginAt = $errorsBeginAt;
    }

    /**
     * Sets the method to be used when issuing the HTTP request.
     *
     * @param string $method The HTTP request method to use. Valid values are
     *               "GET", "HEAD", "TRACE", etc. The default
     *               if not specified is "GET".
     */
    public function setRequestMethod($method)
    {
        $this->requestMethod = $method === null ? self::DEFAULT_REQUEST_METHOD : strtoupper($method);
    }

    /**
     * Whether redirects sent by the server should be followed,
     * defaults to true.
     *
     * @param boolean $f
     */
    public function setFollowRedirects($f)
    {
        $this->followRedirects = $f;
    }

    /**
     * Set quiet mode, which suppresses warnings if curl_exec() fails.
     *
     * @param $bool
     */
    public function setQuiet($bool)
    {
        $this->quiet = $bool;
    }

    /**
     * {@inheritdoc}
     *
     * @return true if the HTTP request succeeds
     *
     * @throws BuildException if an error occurs
     */
    public function evaluate()
    {
        if ($this->url === null) {
            throw new BuildException("No url specified in http condition");
        }

        if (!filter_var($this->url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)) {
            $this->log("Possible malformed URL: " . $this->url,
                $this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN);
        }

        $this->log("Checking for " . $this->url, Project::MSG_VERBOSE);

        $handle = curl_init($this->url);
        curl_setopt($handle, CURLOPT_NOBODY, true);
        curl_setopt($handle, CURLOPT_CUSTOMREQUEST, $this->requestMethod);
        curl_setopt($handle, CURLOPT_FOLLOWLOCATION, $this->followRedirects);

        if (!curl_exec($handle)) {
            $this->log("No response received from URL: " . $this->url,
                $this->quiet ? Project::MSG_VERBOSE : Project::MSG_ERR);

            return false;
        }

        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        $this->log("Result code for " . $this->url . " was " . $httpCode, Project::MSG_VERBOSE);

        $result = false;
        if ($httpCode > 0 && $httpCode < $this->errorsBeginAt) {
            $result = true;
        }

        return $result;
    }
}
