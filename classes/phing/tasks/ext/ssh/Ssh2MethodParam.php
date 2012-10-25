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
require_once 'Ssh2MethodConnectionParam.php';



/**
 * Class that holds parameters for an ssh2_connect $methods parameter
 * This corresponds to the optional $methods parameter
 * for the ssh2_connect function
 * @see http://php.net/ssh2_connect
 *
 * @author Derek Gallo <http://github.com/drock>
 *
 * @package   phing.tasks.ext
 */
class Ssh2Methods
{
    /**
     * @var string
     */
    private $kex;

    /**
     * @var string
     */
    private $hostkey;

    /**
     * @var Ssh2MethodConnectionParam
     */
    private $client_to_server;

    /**
     * @var Ssh2MethodConnectionParam
     */
    private $server_to_client;

    /**
     * @param string $hostkey
     */
    public function setHostkey($hostkey)
    {
        $this->hostkey = $hostkey;
    }

    /**
     * @return string
     */
    public function getHostkey()
    {
        return $this->hostkey;
    }

    /**
     * @param string $kex
     */
    public function setKex($kex)
    {
        $this->kex = $kex;
    }

    /**
     * @return string
     */
    public function getKex()
    {
        return $this->kex;
    }


    /**
     * Handles the <client /> nested element
     * @return Ssh2MethodConnectionParam
     */
    public function createClient()
    {
        $this->client_to_server = new Ssh2MethodConnectionParam();
        return $this->client_to_server;
    }

    /**
     * Handles the <server /> nested element
     * @return Ssh2MethodConnectionParam
     */
    public function createServer()
    {
        $this->server_to_client = new Ssh2MethodConnectionParam();
        return $this->server_to_client;
    }

    /**
     * Convert the params to an array that is suitable to be passed in the ssh2_connect $methods parameter
     * @return array
     */
    public function toArray()
    {
        $array = array(
            'kex' => $this->getKex(),
            'hostkey' => $this->getHostkey(),
            'client_to_server' => !empty($this->client_to_server) ? $this->client_to_server->toArray() : null,
            'server_to_client' => !empty($this->server_to_client) ? $this->server_to_client->toArray() : null
        );

        return array_filter($array,function($var){
            if(is_array($var))
            {
                return !empty($var);
            }

            return !is_null($var);
        });
    }
}

