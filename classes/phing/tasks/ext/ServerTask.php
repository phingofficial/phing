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

require_once 'phing/BuildListener.php';
require_once 'phing/TaskContainer.php';
require_once 'phing/Task.php';
require_once 'phing/types/Commandline.php';

/* E_DEPRECATED was introduced in PHP 5.3, but PHING is PHP 5.2 backward
 * compatible. */
if(!defined('E_DEPRECATED')) {
    define('E_DEPRECATED', 0x2000);
}

/* E_TRACE is a custom constant to represent tracing messages. */
if(!defined('E_TRACE')) {
    define('E_TRACE', 0x0);
}

/**
 * Run the build-in php web server.
 *
 * @author    Marcel Metz <mmetz@adrian-broher.net>
 * @version   $Id$
 * @package   phing.tasks.ext
 */
class ServerTask extends Task
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

    /** Additional configuration of the web server.
     *
     * This array of Parameter instances contains addtional
     * configiration settings like ini values for the
     * web server instance. */
    protected $configData = array();

    /** Forwarder for Tasks instance.
     *
     * The forwarder passes Task instances within the tasks node into
     * this webserver instance.
     */
    private $tasksForwarder = null;

    /** Tasks, that should be executed.
     *
     * The tasks contained within the tasks child node that should be
     * executed during the lifetime of the webserver. */
    private $tasks = array();

    /**
     * Commandline manageing object
     *
     * @var Commandline
     */
    protected $commandline;

    /**
     * Constructs a new web server task instance
     */
    public function __construct()
    {
        $this->commandline = new Commandline();
    }

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
     * Prepare task for running
     */
    private function prepare()
    {
        if (!isset($this->docroot)) {
            $this->docroot = $this->project->getProperty("project.basedir");
        }

        $this->commandline->setExecutable(PHP_BINARY);

        foreach ($this->configData as $config) {
            $this->commandline->createArgument()->setValue('-d');
            $this->commandline->createArgument()->setValue($config->getName().'='.$config->getValue());
        }

        $this->commandline->createArgument()->setValue('-S');
        $this->commandline->createArgument()->setValue($this->address.':'.$this->port);

        $this->commandline->createArgument()->setValue('-t');
        $this->commandline->createArgument()->setValue($this->docroot);

        if (isset($this->router)) {
            $this->commandline->createArgument()->setValue($this->router);
        }
    }

    /**
     * Starts the web server and runs the encapsulated tasks.
     */
    public function main()
    {
        $exception = null;

        $this->prepare();

        $cmd = Commandline::toString($this->commandline->getCommandline(), true);

        $temp_out = tmpfile();
        $temp_err = tmpfile();

        $streams = array(
            array("file", "/dev/null", "r"),
            $temp_out,
            $temp_err
        );

        $handle = proc_open($cmd, $streams, $pipes);

        try {
            if (!is_resource($handle)) {
                throw new BuildException(
                    sprintf(
                        "%s failed to start server process.",
                        get_class($this)
                    )
                );
            } else {
                // Wait 0.5 seconds to allow the webserver to shutdown.
                usleep(500000);
                $server_status = proc_get_status($handle);
                if(!$server_status['running'])
                {
                    rewind($temp_err);
                    $err_message = stream_get_contents($temp_err);

                    throw new BuildException(
                        sprintf(
                            "Web server stopped prematurely. Server reported: %s",
                            $err_message
                        )
                    );
                }

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

            $message_multiplexer = new ServerMessageMultiplexer($this, $temp_out, $temp_err);

            $this->project->addBuildListener($message_multiplexer);

            try {
                foreach ($this->tasks as $task) {
                    $task->perform();
                }
            } catch(Exception $e) {
                $exception = $e;
            }

            $this->project->removeBuildListener($message_multiplexer);
        } catch(Exception $e) {
            $exception = $e;
        }

        fclose($temp_out);
        fclose($temp_err);

        // Terminate server with SIGINT
        proc_terminate($handle, 2);

        $this->log("Stopped web server", Project::MSG_INFO);

        if (!is_null($exception)) {
            throw $exception;
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

    /**
     * creates a nested tasks forwarder
     *
     * @return ServerTaskForwarder A server task forwarder
     */
    public function createTasks()
    {
        return ($this->tasksForwarder = new ServerTaskForwarder($this));
    }

    /**
     * Creates a configuration
     *
     * @return Parameter A parameter containing the given configuration.
     */
    public function createConfig()
    {
        $num = array_push($this->configData, new Parameter());

        return $this->configData[$num - 1];
    }

    /** Add an encapsulated task to the webserver.
     *
     * @param Task A Task instance
     */
    public function addTask(Task $task)
    {
        $this->tasks[] = $task;
    }
}

/** A utility class to forward Tasks to the ServerTask
 *
 * This class forwards Task instances from the tasks node into the owning
 * TaskServer instance.
 *
 * @author Marcel Metz <mmetz@adrian-broher.net>
 * @package phing.tasks.ext
 */
class ServerTaskForwarder implements TaskContainer
{
    /** The instance of the owning ServerTask. */
    private $outer;

    /** Creates a new ServerTaskForwarder instance
     *
     * @param ServerTask $outer The server instance, that owns this
     * forwarder.
     */
    public function __construct(ServerTask $outer)
    {
        $this->outer = $outer;
    }

    /** Adds a Task to this forwarder.
     *
     * @param Task $task The Task instance to add.
     */
    public function addTask(Task $task)
    {
        $this->outer->addTask($task);
    }
}

/** A class to multiplex webserver messages into phing log messages
 *
 * @author Marcel Metz <mmetz@adrian-broher.net>
 * @package phing.tasks.ext
 */
class ServerMessageMultiplexer implements BuildListener
{
    /** PCRE pattern to separate PHP CLI server log entries
     *
     * The PHP CLI server uses the asctime_r (POSIX) or ctime_s (WIN32)
     * format to prefix every log entry.  This can be used to split up
     * the log data into log entries, even in the case log entries span
     * over sereral lines.
     */
    const PHP_CLI_SERVER_TIMESTAMP_PATTERN =
        '#\\[[A-Z][a-z]{2} [A-Z][a-z]{2} [ 0-3][0-9] [ 0-2][0-9]:[ 0-5][0-9]:[ 0-5][0-9] [0-9]{4}\\] #';

    /** PCRE pattern to identify and process a HTTP response log entry
     *
     * The PHP CLI server emits a log entry for every processed HTTP
     * request containing the client IP and port, the HTTP status code,
     * the requested URL and optional informations to the request.
     *
     * The pattern contains the following named capture groups to access
     * the mentioned data entries:
     *
     * client_ip:   IPv4 or IPv6 address of the client that initiated
     *              the request.
     * client_port: The TCP port of the client that initiated the request.
     * status:      The HTTP status code sent by the server.
     * url:         The requested URL.
     * info:        Optional additional information;  contents depends
     *              on the request issued.
     */
    const PHP_CLI_SERVER_RESPONSE_PATTERN =
        '#^(?P<client_ip>[][\\.:[:digit:]]+):(?P<client_port>[[:digit:]]{1,5}) \\[(?P<status>[[:digit:]]{3})\\]: (?P<url>[[:^space:]]*)(?: - (?P<info>.+))?$#';

    /** PCRE pattern to identify and process a server log entry
     *
     * The PHP CLI server emits PHP errors or infos on the standard
     * error file stream (STDERR).  These entries contain a severity and
     * an additinal message.
     *
     * The pattern contains the following named capture groups to access
     * the mentioned data entries:
     *
     * level:   A string representing the PHP error level.
     * message: The message logged by the server.
     */
    const PHP_CLI_SERVER_ENTRY_PATTERN =
        '#^PHP (?P<level>Fatal error|Catchable fatal error|Warning|Parse error|Notice|Strict Standards|Deprecated):  (?P<message>.+)$#';

    public function __construct($outer, $out, $err)
    {
        $this->outer = $outer;
        $this->out = $out;
        $this->err = $err;
        $this->injecting = false;
    }

    /** @see BuildListener::buildStarted() */
    public function buildStarted(BuildEvent $event)
    {}

    /** @see BuildListener::buildFinished() */
    public function buildFinished(BuildEvent $event)
    {}

    /** @see BuildListener::targetStarted() */
    public function targetStarted(BuildEvent $event)
    {}

    /** @see BuildListener::targetFinished() */
    public function targetFinished(BuildEvent $event)
    {}

    /** @see BuildListener::taskStarted() */
    public function taskStarted(BuildEvent $event)
    {}

    /** @see BuildListener::taskFinished() */
    public function taskFinished(BuildEvent $event)
    {}

    /** Intercept messages created and inject web server log entries
     *
     * @see BuildListener::messageLogged()
     */
    public function messageLogged(BuildEvent $event)
    {
        if(!$this->injecting)
        {
            $this->injecting = true;

            $out_entries = $this->convertToLogEntries($this->out);
            $err_entries = $this->convertToLogEntries($this->err);

            foreach($out_entries as $entry) {
                $message = sprintf(
                    "Response %1d %2s -> %3s:%4s (%5s): %6s",
                    $entry['status'],
                    $entry['url'],
                    $entry['client_ip'],
                    $entry['client_port'],
                    $this->ErrorLevelToString($entry['level']),
                    $entry['message']
                );
                $this->outer->log($message, Project::MSG_INFO);
            }
            foreach($err_entries as $entry) {
                $message = sprintf(
                    "Response %1d %2s -> %3s:%4s (%5s): %6s",
                    $entry['status'],
                    $entry['url'],
                    $entry['client_ip'],
                    $entry['client_port'],
                    $this->ErrorLevelToString($entry['level']),
                    $entry['message']
                );
                $this->outer->log($message, Project::MSG_INFO);
            }

            $this->injecting = false;
        }
    }

    /** Extract log entries from file stream
     *
     * @param Resource $logHandle An open file handle containing the output
     *        of the PHP CLI web server
     *
     * @return An array containing log entries. The array contains the keys:
     *         client_ip:   IPv4 or IPv6 address of the client that
     *                      initiated the request.
     *         client_port: The TCP port of the client that initiated the
     *                      request.
     *         status:      The HTTP status code sent by the server.
     *         url:         The requested URL.
     *         level:       A PHP error constant or E_TRACE.
     *         message:     The message logged by the server.
     *
     * @throws UnexpectedValueException if the log data contains a line
     *         of a unknown format.
     * @throws UnexpectedValueException if the log data contains messages
     *         that can't be associated to a HTTP request.
     */
    private function convertToLogEntries($logHandle)
    {
        $result= array();

        rewind($logHandle);
        $logText = stream_get_contents($logHandle);
        rewind($logHandle);
        ftruncate($logHandle, 0);

        $logEntries = preg_split(self::PHP_CLI_SERVER_TIMESTAMP_PATTERN, $logText, -1, PREG_SPLIT_NO_EMPTY);

        $messages =  array();

        foreach($logEntries as $logEntry) {
            $response = array();
            $serverEntry = array();

            if(preg_match(self::PHP_CLI_SERVER_RESPONSE_PATTERN, $logEntry, $response)) {
                foreach($response as $key => $match) {
                    if(is_int($key)) {
                        unset($response[$key]);
                    }
                }

                $messages[] = array(
                    'level' => E_TRACE,
                    'message' => ''
                );

                foreach($messages as $message) {
                    if(empty($message['message']) && array_key_exists('info', $response)) {
                        $message['message'] = $response['info'];
                    } else if(empty($message['message'])) {
                        $message['message'] = $this->HTTPStatusToMessage($response['status']);
                    }

                    $result[] = array_merge($response, $message);

                }

                $messages = array();
            } else if(preg_match(self::PHP_CLI_SERVER_ENTRY_PATTERN, $logEntry, $serverEntry)) {
                $messages[] = array(
                    'level' => $this->LogErrorStringToErrorLevel($serverEntry['level']),
                    'message' => $serverEntry['message']
                );
            } else {
                $this->outer->log(
                    sprintf(
                        "Unexpected line in log output: %s",
                        $logEntry
                    ),
                    Project::MSG_ERROR
                );
            }
        }

        foreach($message as $message) {
            $this->outer->log(
                sprintf(
                    "Log entry without associated server response: (%s) %s",
                    $this->ErrorLevelToString($message['level']),
                    $message['message']
                ),
                Project::MSG_ERROR
            );
        }

        return $result;
    }

    /** Convert PHP CLI error level string to PHP error level constant
     *
     * @param $errorString The error string that should be converted
     *        to a error level constant.
     *
     * @return The error level constant associtated with the given
     *         error string.
     *
     * @throws UnexpectedValueException if the error string has no known
     *         conversion.
     */
    private function LogErrorStringToErrorLevel($errorString)
    {
        switch($errorString) {
            case "Fatal error":
                return E_ERROR;
            case "Catchable fatal error":
                return E_RECOVERABLE_ERROR;
            case "Warning":
                return E_WARNING;
            case "Parse error":
                return E_PARSE;
            case "Notice":
                return E_NOTICE;
            case "Strict Standards":
                return E_STRICT;
            case "Deprecated":
                return E_DEPRECATED;
            default:
                throw new UnexpectedValueException(
                    sprintf(
                        "Unhandled error string: \"%s\"",
                        $errorString
                    )
                );
        }
    }

    /** Convert PHP error level constant to human readable text
     *
     * @param $level The error level constant that should be converted
     *        to a human readable string.
     *
     * @return The human readable string associtated with the given
     *         error level constant.
     *
     * @throws UnexpectedValueException if the error level has no known
     *         conversion.
     */
    private function ErrorLevelToString($level)
    {
        switch($level) {
            case E_ERROR:
                return "Error";
            case E_RECOVERABLE_ERROR:
                return "Recoverable Error";
            case E_WARNING:
                return "Warning";
            case E_PARSE:
                return "Parse error";
            case E_NOTICE:
                return "Notice";
            case E_STRICT:
                return "Strict Standards";
            case E_DEPRECATED:
                return "Deprecated";
            case E_TRACE:
                return "Trace";
            default:
                throw new UnexpectedValueException(
                    sprintf(
                        "Unhandled error level: %d",
                        $level
                    )
                );
        }
    }

    /** Convert HTTP status code to human readable text
     *
     * @param $level The HTTP status code that should be converted
     *        to a human readable string.
     *
     * @return The human readable string associtated with the given
     *         HTTP status code.
     *
     * @throws UnexpectedValueException if the HTTP status code has no
     *         known conversion.
     */
    private function HTTPStatusToMessage($status)
    {
        switch($status) {
            case 100:
                return "Continue";
            case 101:
                return "Switching Protocols";
            case 200:
                return "Success";
            case 201:
                return "Created";
            case 202:
                return "Accepted";
            case 203:
                return "Non-Authorative Information";
            case 204:
                return "No Content";
            case 205:
                return "Reset Content";
            case 206:
                return "Partial Content";
            case 300:
                return "Multiple Choices";
            case 301:
                return "Moved Permanenty";
            case 302:
                return "Found";
            case 303:
                return "See Other";
            case 304:
                return "Not Modified";
            case 305:
                return "Use Proxy";
            case 306:
                return "Switch Proxy";
            case 307:
                return "Temporary Redirect";
            case 308:
                return "Permanent Redirect";
            case 400:
                return "Bad Request";
            case 401:
                return "Unauthorized";
            case 402:
                return "Payment Required";
            case 403:
                return "Forbidden";
            case 404:
                return "Not Found";
            case 405:
                return "Method Not Allowed";
            case 406:
                return "Not Acceptable";
            case 407:
                return "Proxy Authentication Required";
            case 408:
                return "Request Timeout";
            case 409:
                return "Conflict";
            case 410:
                return "Gone";
            case 411:
                return "Length Required";
            case 412:
                return "Precondition Failed";
            case 413:
                return "Payload Too Large";
            case 414:
                return "URI Too Long";
            case 415:
                return "Unsupported Media Type";
            case 416:
                return "Range Not Satisfiable";
            case 417:
                return "Expectation Failed";
            case 421:
                return "Misdirected Request";
            case 426:
                return "Upgrade Required";
            case 428:
                return "Precondition Required";
            case 429:
                return "Too Many Requests";
            case 431:
                return "Request Header Fields Too Large";
            case 451:
                return "Unavailable For Legal Reasons";
            case 500:
                return "Internal Server Error";
            case 501:
                return "Not Implemented";
            case 502:
                return "Bad Gateway";
            case 503:
                return "Service Unavailable";
            case 504:
                return "Gateway Timeout";
            case 505:
                return "HTTP Version Not Supported";
            case 506:
                return "Variant Also Negotiates";
            case 510:
                return "Not Extended";
            case 511:
                return "Network Authentication Required";
            default:
                throw new UnexpectedValueException(
                    sprintf(
                        "Unhandled HTTP status code: %d",
                        $status
                    )
                );
        }
    }
}
