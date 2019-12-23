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
 * Base class for HTTP_Request2-backed tasks
 *
 * Handles nested <config /> and <header /> tags, contains a method for
 * HTTP_Request2 instance creation
 *
 * @package phing.tasks.ext
 * @author  Alexey Borzov <avb@php.net>
 */
abstract class HttpTask extends Task
{
    /**
     * Holds the request URL
     *
     * @var string
     */
    protected $url = null;

    /**
     * Prototype HTTP_Request2 object, cloned in createRequest()
     *
     * @var HTTP_Request2
     */
    protected $requestPrototype = null;

    /**
     * Holds additional header data
     *
     * @var Parameter[]
     */
    protected $headers = [];

    /**
     * Holds additional config data for HTTP_Request2
     *
     * @var Parameter[]
     */
    protected $configData = [];

    /**
     * Holds the authentication user name
     *
     * @var string
     */
    protected $authUser = null;

    /**
     * Holds the authentication password
     *
     * @var string
     */
    protected $authPassword = '';

    /**
     * Holds the authentication scheme
     *
     * @var string
     */
    protected $authScheme;

    /**
     * Load the necessary environment for running this task.
     *
     * @return void
     *
     * @throws BuildException
     */
    public function init(): void
    {
        @include_once 'HTTP/Request2.php';

        if (!class_exists('HTTP_Request2')) {
            throw new BuildException(
                static::class . ' depends on HTTP_Request2 being installed '
                . 'and on include_path.',
                $this->getLocation()
            );
        }
    }

    /**
     * Sets the request URL
     *
     * @param string $url
     *
     * @return void
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * Sets the prototype object that will be cloned in createRequest()
     *
     * Used in tests to inject an instance of HTTP_Request2 containing a custom adapter
     *
     * @param HTTP_Request2 $request
     *
     * @return void
     */
    public function setRequestPrototype(HTTP_Request2 $request): void
    {
        $this->requestPrototype = $request;
    }

    /**
     * Creates and configures an instance of HTTP_Request2
     *
     * @return HTTP_Request2
     *
     * @throws HTTP_Request2_LogicException
     */
    protected function createRequest(): HTTP_Request2
    {
        if (!$this->requestPrototype) {
            $request = new HTTP_Request2($this->url);
        } else {
            $request = clone $this->requestPrototype;
            $request->setUrl($this->url);
        }

        foreach (array_keys($this->getProject()->getProperties()) as $propName) {
            if (0 === strpos($propName, 'phing.http.')) {
                $request->setConfig(substr($propName, 11), $this->getProject()->getProperty($propName));
            }
        }

        // set the authentication data
        if (!empty($this->authUser)) {
            $request->setAuth(
                $this->authUser,
                $this->authPassword,
                $this->authScheme
            );
        }

        foreach ($this->configData as $config) {
            $request->setConfig($config->getName(), $config->getValue());
        }

        foreach ($this->headers as $header) {
            $request->setHeader($header->getName(), $header->getValue());
        }

        return $request;
    }

    /**
     * Processes the server's response
     *
     * @param HTTP_Request2_Response $response
     *
     * @return void
     *
     * @throws BuildException
     */
    abstract protected function processResponse(HTTP_Request2_Response $response);

    /**
     * Makes a HTTP request and processes its response
     *
     * @return void
     *
     * @throws BuildException
     * @throws HTTP_Request2_LogicException
     * @throws HTTP_Request2_Exception
     */
    public function main(): void
    {
        if (!isset($this->url)) {
            throw new BuildException("Required attribute 'url' is missing");
        }

        try {
            $this->processResponse($this->createRequest()->send());
        } catch (HTTP_Request2_MessageException $e) {
            throw new BuildException($e);
        }
    }

    /**
     * Creates an additional header for this task
     *
     * @return Parameter The created header
     */
    public function createHeader(): Parameter
    {
        $num = array_push($this->headers, new Parameter());

        return $this->headers[$num - 1];
    }

    /**
     * Creates a config parameter for this task
     *
     * @return Parameter The created config parameter
     */
    public function createConfig(): Parameter
    {
        $num = array_push($this->configData, new Parameter());

        return $this->configData[$num - 1];
    }

    /**
     * Sets the authentication user name
     *
     * @param string $user
     *
     * @return void
     */
    public function setAuthUser(string $user): void
    {
        $this->authUser = $user;
    }

    /**
     * Sets the authentication password
     *
     * @param string $password
     *
     * @return void
     */
    public function setAuthPassword(string $password): void
    {
        $this->authPassword = $password;
    }

    /**
     * Sets the authentication scheme
     *
     * @param string $scheme
     *
     * @return void
     */
    public function setAuthScheme(string $scheme): void
    {
        $this->authScheme = $scheme;
    }
}
