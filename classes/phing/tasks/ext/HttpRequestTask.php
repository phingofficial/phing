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
 * A HTTP request task.
 * Making an HTTP request and try to match the response against an provided
 * regular expression.
 *
 * @package phing.tasks.ext
 * @author  Benjamin Schultz <bschultz@proqrent.de>
 * @since   2.4.1
 */
class HttpRequestTask extends HttpTask
{
    /**
     * Holds the regular expression that should match the response
     *
     * @var string
     */
    protected $responseRegex = '';

    /**
     * Holds the regular expression that should match the response code
     *
     * @var string
     */
    protected $responseCodeRegex = '';

    /**
     * Whether to enable detailed logging
     *
     * @var boolean
     */
    protected $verbose = false;

    /**
     * Holds the events that will be logged
     *
     * @var array<string>
     */
    protected $observerEvents = [
        'connect',
        'sentHeaders',
        'sentBodyPart',
        'receivedHeaders',
        'receivedBody',
        'disconnect',
    ];

    /**
     * Holds the request method
     *
     * @var string
     */
    protected $method = null;

    /**
     * Holds additional post parameters for the request
     *
     * @var Parameter[]
     */
    protected $postParameters = [];

    /**
     * @var Regexp
     */
    private $regexp;

    /**
     * Sets the response regex
     *
     * @param string $regex
     */
    public function setResponseRegex($regex)
    {
        $this->responseRegex = $regex;
    }

    /**
     * Sets the response code regex
     *
     * @param string $regex
     */
    public function setResponseCodeRegex($regex)
    {
        $this->responseCodeRegex = $regex;
    }

    /**
     * Sets whether to enable detailed logging
     *
     * @param boolean $verbose
     */
    public function setVerbose($verbose)
    {
        $this->verbose = StringHelper::booleanValue($verbose);
    }

    /**
     * Sets a list of observer events that will be logged if verbose output is enabled.
     *
     * @param string $observerEvents List of observer events
     */
    public function setObserverEvents($observerEvents)
    {
        $this->observerEvents = [];

        $token = ' ,;';
        $ext = strtok($observerEvents, $token);

        while ($ext !== false) {
            $this->observerEvents[] = $ext;
            $ext = strtok($token);
        }
    }

    /**
     * The setter for the method
     * @param $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * Creates post body parameters for this request
     *
     * @return Parameter The created post parameter
     */
    public function createPostParameter()
    {
        $num = array_push($this->postParameters, new Parameter());

        return $this->postParameters[$num - 1];
    }

    /**
     * Load the necessary environment for running this task.
     *
     * @throws BuildException
     */
    public function init()
    {
        parent::init();

        $this->regexp = new Regexp();

        $this->authScheme = HTTP_Request2::AUTH_BASIC;

        // Other dependencies that should only be loaded when class is actually used
        require_once 'HTTP/Request2/Observer/Log.php';
    }

    /**
     * Creates and configures an instance of HTTP_Request2
     *
     * @return HTTP_Request2
     * @throws HTTP_Request2_Exception
     * @throws HTTP_Request2_LogicException
     */
    protected function createRequest()
    {
        $request = parent::createRequest();

        if ($this->method == HTTP_Request2::METHOD_POST) {
            $request->setMethod(HTTP_Request2::METHOD_POST);

            if ($this->isHeaderSet('content-type', 'application/json')) {
                $request->setBody(json_encode(array_map(function (Parameter $postParameter) {
                    return [$postParameter->getName() => $postParameter->getValue()];
                }, $this->postParameters)));
            } else {
                foreach ($this->postParameters as $postParameter) {
                    $request->addPostParameter($postParameter->getName(), $postParameter->getValue());
                }
            }
        }

        if ($this->verbose) {
            $observer = new HTTP_Request2_Observer_Log();

            // set the events we want to log
            $observer->events = $this->observerEvents;

            $request->attach($observer);
        }

        return $request;
    }

    private function isHeaderSet($headerName, $headerValue)
    {
        $isSet = false;

        foreach ($this->headers as $header) {
            if ($header->getName() === $headerName && $header->getValue() === $headerValue) {
                $isSet = true;
            }
        }

        return $isSet;
    }

    /**
     * Checks whether response body or status-code matches the given regexp
     *
     * @param  HTTP_Request2_Response $response
     * @return void
     * @throws BuildException
     * @throws HTTP_Request2_Exception
     * @throws RegexpException
     */
    protected function processResponse(HTTP_Request2_Response $response)
    {
        if ($this->responseRegex !== '') {
            $this->regexp->setPattern($this->responseRegex);

            if (!$this->regexp->matches($response->getBody())) {
                throw new BuildException('The received response body did not match the given regular expression');
            } else {
                $this->log('The response body matched the provided regex.');
            }
        }

        if ($this->responseCodeRegex !== '') {
            $this->regexp->setPattern($this->responseCodeRegex);

            if (!$this->regexp->matches($response->getStatus())) {
                throw new BuildException('The received response status-code did not match the given regular expression');
            } else {
                $this->log('The response status-code matched the provided regex.');
            }
        }
    }
}
