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
require_once "phing/tasks/ext/supervisor/SupervisorProcessAction.php";

/**
 * SupervisorTask
 * start/stop/restart a supervisor process or group via xmlrpc (http://supervisord.org/)
 *
 * <supervisor url="http://user:pass@127.0.0.1:1337/RPC2" failonerror="true">
 *     <process name="myProcessA" action="stop" failonerror="false"/>
 *     <process name="myProcessB" action="restart" />
 *     <process name="myProcessGroup" action="restartGroup" />
 * </supervisor>
 *
 * @author Suat Özgür <suat.oezguer@mindgeek.com>
 * @package   phing.tasks.ext*
 */
class SupervisorTask extends AbstractXmlRpcTask {

    private $methodAliases = array(
        'start'      => 'supervisor.startProcess',
        'stop'       => 'supervisor.stopProcess',
        'restart'    => array(
            'supervisor.stopProcess',
            'supervisor.startProcess',
        ),

        'startGroup' => 'supervisor.startProcessGroup',
        'stopGroup' => 'supervisor.stopProcessGroup',
        'restartGroup'    => array(
            'supervisor.stopProcessGroup',
            'supervisor.startProcessGroup',
        ),

    );

    private $processList = array();

    public function addProcess(SupervisorProcessAction $item) {
        $this->processList[] = $item;
    }

    public function main()
    {
        /** @var SupervisorProcessAction $process */
        foreach ($this->processList as $process) {
            $failonerror = is_null($process->isFailonerror()) ? $this->isFailonerror() : $process->isFailonerror();

            if (!isset($this->methodAliases[$process->getAction()])) {
                throw new BuildException('Invalid action ' . $process->getAction() . ' (valid actions are: ' . implode(', ', array_keys($this->methodAliases)) );
            }

            $methods = $this->methodAliases[$process->getAction()];
            if (!is_array($methods)) {
                $methods = array($methods);
            }

            foreach ($methods as $method) {
                try {
                    $result = $this->executeRpcCall($method, array($process->getName()), $failonerror);
                    $boolResult = xmlrpc_decode($result);
                    if ($boolResult === true) {
                        $this->log($process->getName() . ' ' . $method . ' successfuly done.', Project::MSG_INFO);
                    } else {
                        $this->log($process->getName() . ' ' . $method . ' failed', Project::MSG_INFO);
                    }
                } catch (BuildException $e) {
                    // suppress "not running" exception when stopping a process for a restart as I don't consider that as a restart failure
                    if ($process->getAction() != 'restart' || $method != 'supervisor.stopProcess' || !preg_match('/NOT_RUNNING/', $e->getMessage())) {
                        $this->log($process->getAction() . ' ' . $method . ' ' . $e->getMessage() );
                        throw($e);
                    }
                }
            }

        }

    }


}