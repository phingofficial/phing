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

require_once 'phing/tasks/system/SequentialTask.php';

/**
 * Run the build-in php web server.
 *
 * @author    Marcel Metz <mmetz@adrian-broher.net>
 * @version   $Id$
 * @package   phing.tasks.ext
 */
class ServerTask extends SequentialTask
{

    /** The document root directory used by the server.
     *
     * Defaults to the phing project.basedir property.
     */
    protected $docroot = null;

    /** The ip address the server should listen on.
     *
     * Defaults to 127.0.0.1.
     */
    protected $address = "127.0.0.1";

    /** The port the server should listen on.
     *
     * Defaults to 8080.
     */
    protected $port = 8080;

    /** The path to the router script, which should displatch requests. */
    protected $router = null;

    /**
     * Load the necessary environment for running this task.
     *
     * @throws BuildException
     */
    public function init()
    {
        if (version_compare(PHP_VERSION, '5.5.0') <= 0) {
            throw new BuildException(
                get_class($this) . ' requires at least PHP version 5.5.'
            );
        }
    }

    /**
     * Starts the web server and runs the encapsulated tasks.
     */
    public function main()
    {
        if (!isset($this->docroot)) {
            $this->docroot = $this->project->getProperty("project.basedir");
        }

        $router = isset($this->router) ? escapeshellarg($this->router) : '';

        $cmd = sprintf(
            "%s -S %s:%d -t %s %s",
            escapeshellarg(PHP_BINARY),
            $this->address,
            $this->port,
            escapeshellarg($this->docroot),
            $router
        );

        $this->log("\$cmd: " . $cmd, Project::MSG_DEBUG);

        $streams = [
            ["file", "/dev/null", "r"],
            ["file", "/dev/null", "w"],
            ["file", "/dev/null", "w"],
        ];

        $handle = proc_open($cmd, $streams, $pipes);

        if (!is_resource($handle)) {
            throw new BuildException(
                get_class($this) . ' could not start web server.'
            );
        } else {
            $this->log(
                sprintf(
                    "Started web server, listening on http://%s:%d",
                    $this->address,
                    $this->port
                ),
                Project::MSG_INFO
            );

            $msg = isset($this->router)
                ? sprintf("with %s as docroot and %s as router", $this->docroot, $this->router)
                : sprintf("with %s as docroot", $this->docroot);

            $this->log($msg, Project::MSG_VERBOSE);
        }

        try {
            foreach ($this->nestedTasks as $task) {
                $task->perform();
            }
        } finally {
            // Terminate server with SIGINT
            proc_terminate($handle, 2);

            $this->log("Stopped web server", Project::MSG_INFO);
        }
    }

    /**
     * @param string $address The ip address the server should listen on.
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @param string $docroot The directory which should be used as document
     *        root.
     */
    public function setDocRoot($docroot)
    {
        $this->docroot = $docroot;
    }

    /**
     * @param string $port The port the server should listen on.
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * @param string $router The router script, that should dispatch
     *        requests.
     */
    public function setRouter($router)
    {
        $this->router = $router;
    }
}
