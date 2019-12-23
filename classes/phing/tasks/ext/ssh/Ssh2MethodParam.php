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
 * Class that holds parameters for an ssh2_connect $methods parameter
 * This corresponds to the optional $methods parameter
 * for the ssh2_connect function
 *
 * @see http://php.net/ssh2_connect
 *
 * @author Derek Gallo <http://github.com/drock>
 *
 * @package phing.tasks.ext
 */
class Ssh2MethodParam extends DataType
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
     *
     * @return void
     */
    public function setHostkey(string $hostkey): void
    {
        $this->hostkey = $hostkey;
    }

    /**
     * @param Project $p
     *
     * @return string
     *
     * @throws BuildException
     */
    public function getHostkey(Project $p): string
    {
        if ($this->isReference()) {
            return $this->getRef($p)->getHostkey($p);
        }

        return $this->hostkey;
    }

    /**
     * @param string $kex
     *
     * @return void
     */
    public function setKex(string $kex): void
    {
        $this->kex = $kex;
    }

    /**
     * @param Project $p
     *
     * @return string
     *
     * @throws BuildException
     */
    public function getKex(Project $p): string
    {
        if ($this->isReference()) {
            return $this->getRef($p)->getKex($p);
        }

        return $this->kex;
    }

    /**
     * @param Project $p
     *
     * @return Ssh2MethodConnectionParam
     *
     * @throws BuildException
     */
    public function getClientToServer(Project $p): Ssh2MethodConnectionParam
    {
        if ($this->isReference()) {
            return $this->getRef($p)->getClientToServer($p);
        }

        return $this->client_to_server;
    }

    /**
     * @param Project $p
     *
     * @return Ssh2MethodConnectionParam
     *
     * @throws BuildException
     */
    public function getServerToClient(Project $p): Ssh2MethodConnectionParam
    {
        if ($this->isReference()) {
            return $this->getRef($p)->getServerToClient($p);
        }

        return $this->server_to_client;
    }

    /**
     * Handles the <client /> nested element
     *
     * @return Ssh2MethodConnectionParam
     */
    public function createClient(): Ssh2MethodConnectionParam
    {
        $this->client_to_server = new Ssh2MethodConnectionParam();

        return $this->client_to_server;
    }

    /**
     * Handles the <server /> nested element
     *
     * @return Ssh2MethodConnectionParam
     */
    public function createServer(): Ssh2MethodConnectionParam
    {
        $this->server_to_client = new Ssh2MethodConnectionParam();

        return $this->server_to_client;
    }

    /**
     * Convert the params to an array that is suitable to be passed in the ssh2_connect $methods parameter
     *
     * @param Project $p
     *
     * @return array
     */
    public function toArray(Project $p): array
    {
        $client_to_server = $this->getClientToServer($p);
        $server_to_client = $this->getServerToClient($p);

        $array = [
            'kex' => $this->getKex($p),
            'hostkey' => $this->getHostkey($p),
            'client_to_server' => null !== $client_to_server ? $client_to_server->toArray() : null,
            'server_to_client' => null !== $server_to_client ? $server_to_client->toArray() : null,
        ];

        return array_filter($array, [$this, '_filterParam']);
    }

    /**
     * @param mixed $var
     *
     * @return bool
     */
    protected function _filterParam($var): bool
    {
        if (is_array($var)) {
            return !empty($var);
        }

        return null !== $var;
    }

    /**
     * @param Project $p
     *
     * @return Ssh2MethodParam
     *
     * @throws BuildException
     */
    public function getRef(Project $p): Ssh2MethodParam
    {
        $dataTypeName = StringHelper::substring(self::class, strrpos(self::class, '\\') + 1);
        return $this->getCheckedRef(self::class, $dataTypeName);
    }
}
