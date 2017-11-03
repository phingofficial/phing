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

require_once 'phing/Task.php';

/**
 * Base for XML-RPC client tasks
 *
 * @author Suat Özgür <suat.oezguer@mindgeek.com>
 * @package   phing.tasks.ext
 */
abstract class AbstractXmlRpcTask extends Task {

    protected $url = null;
    protected $failonerror = true;
    private $params = array();

    public function addParam(Parameter $param) {
        $this->params[] = $param;
    }

    public function init()
    {
        if (!function_exists('xmlrpc_encode_request')) {
            throw new BuildException('php-xmlrpc extension is not installed');
        }

        if (!function_exists('curl_init')) {
            throw new BuildException('php-curl extension is not installed');
        }
    }

    protected function executeRpcCall($method, $params, $failonerror = null)
    {

        if (is_null($failonerror)) {
            $failonerror = $this->isFailonerror();
        }

        if (count($params) == 0) {
            $params = array(null);
        }

        $funcParams =  array_merge( array($method), array_values($params) );
        $postData = call_user_func_array('xmlrpc_encode_request', $funcParams);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getUrl());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,  $postData);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        $response = curl_exec($ch);
        if ($response === false) {
            $msg = 'POST error: ' . curl_error($ch);
            $this->log($msg, ($failonerror ? Project::MSG_ERR : Project::MSG_WARN) );
            if ($this->isFailonerror()) {
                throw new BuildException($msg);
            }
        }

        $responseDecoded = xmlrpc_decode($response);

        if (is_array($responseDecoded) && xmlrpc_is_fault($responseDecoded)) {
            $msg = 'XMLRPC fault: ' . $responseDecoded['faultString'] . ' (' . $responseDecoded['faultCode'] . ')';
            $this->log($msg, ($failonerror ? Project::MSG_ERR : Project::MSG_WARN) );
            if ($this->isFailonerror()) {
                throw new BuildException($msg);
            }
        }

        return $response;
    }

    /**
     * @return boolean
     */
    public function isFailonerror()
    {
        return $this->failonerror;
    }

    /**
     * @param boolean $failonerror
     */
    public function setFailonerror($failonerror)
    {
        $this->failonerror = StringHelper::booleanValue($failonerror);
    }

    /**
     * @return null
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param null $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }
}