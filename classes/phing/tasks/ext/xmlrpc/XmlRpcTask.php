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

require_once "phing/tasks/ext/xmlrpc/AbstractXmlRpcTask.php";

/**
 * Make XML-RPC Calls
 *
 * <xmlrpc url="http://user:pass@127.0.0.1:1337/RPC2" method="supervisor.stopProcess" resultProperty="xmlResult" failonerror="false">
 *   <param name="name" value="foo" />
 * </xmlrpc>
 *
 * @author Suat Özgür <suat.oezguer@mindgeek.com>
 * @package   phing.tasks.ext
 */

class XmlRpcTask extends AbstractXmlRpcTask {

    protected $resultProperty = null;
    protected $method = null;
    private $params = array();

    /**
     * @param Parameter $param
     */
    public function addParam(Parameter $param) {
        $this->params[] = $param;
    }

    public function main()
    {
        $params = array();

        /** @var Parameter $param */
        foreach ($this->params as $param) {

            $value = $param->getValue();
            switch ($param->getType()) {
                case 'boolean':
                    $value = boolval($value);
                    break;

                case 'dateTime.iso8601':
                    xmlrpc_set_type($value, 'datetime');
                    break;

                case 'datetime':
                case 'base64':
                    xmlrpc_set_type($value, $param->getType());
                    break;

                case 'double':
                    $value = floatval($value);
                    break;

                case 'int':
                case 'i4':
                    $value = (int)$value;
                    break;

                case 'string':
                case null:
                    // default
                    break;

                default:
                    $this->log('Warning: unsupported type "' . $param->getType() . '" for param "' . $param->getName() . '"', Project::MSG_WARN );
                    break;
            }

            $params[$param->getName()] = $value;
        }

        $this->log( 'Calling ' . $this->getMethod(), Project::MSG_INFO);

        $result = $this->executeRpcCall($this->getMethod(), $params);
        if (!is_null($this->getResultProperty())) {
            $this->project->setProperty(
                $this->getResultProperty(),
                $result
            );
        }

        $this->log( 'response=' . htmlspecialchars($result), Project::MSG_VERBOSE );
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return null
     */
    public function getResultProperty()
    {
        return $this->resultProperty;
    }

    /**
     * @param null $resultProperty
     */
    public function setResultProperty($resultProperty)
    {
        $this->resultProperty = $resultProperty;
    }

}