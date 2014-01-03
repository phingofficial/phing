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
 * Base class for HTTP_Request2-backed tasks
 *
 * Handles nested <config /> and <header /> tags, contains a method for
 * HTTP_Request2 instance creation
 *
 * @package phing.tasks.ext
 * @author  Alexey Borzov <avb@php.net>
 * @version $Id$
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
     * Load the necessary environment for running this task.
     *
     * @throws BuildException
     */
    public function init()
    {
        @include_once 'HTTP/Request2.php';

        if (!class_exists('HTTP_Request2')) {
            throw new BuildException(
                get_class($this) . ' depends on HTTP_Request2 being installed '
                . 'and on include_path.',
                $this->getLocation()
            );
        }
    }

    /**
     * Sets the request URL
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Sets the prototype object that will be cloned in createRequest()
     *
     * Used in tests to inject an instance of HTTP_Request2 containing a custom adapter
     *
     * @param HTTP_Request2 $request
     */
    public function setRequestPrototype(HTTP_Request2 $request)
    {
        $this->requestPrototype = $request;
    }

    /**
     * Creates and configures an instance of HTTP_Request2
     *
     * @return HTTP_Request2
     */
    protected function createRequest()
    {
        if (!$this->requestPrototype) {
            return new HTTP_Request2($this->url);

        } else {
            $request = clone $this->requestPrototype;
            $request->setUrl($this->url);
            return $request;
        }
    }

    /**
     * Processes the server's response
     *
     * @param HTTP_Request2_Response $response
     * @return void
     * @throws BuildException
     */
    abstract protected function processResponse(HTTP_Request2_Response $response);

    /**
     * Makes a HTTP request and processes its response
     *
     * @throws BuildException
     */
    public function main()
    {
        if (!isset($this->url)) {
            throw new BuildException("Required attribute 'url' is missing");
        }

        $this->processResponse($this->createRequest()->send());
    }
}