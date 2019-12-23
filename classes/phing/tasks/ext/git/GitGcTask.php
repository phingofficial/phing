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
 * Wrapper around git-gc
 *
 * @see     VersionControl_Git
 *
 * @author  Victor Farazdagi <simple.square@gmail.com>
 * @package phing.tasks.ext.git
 * @since   2.4.3
 */
class GitGcTask extends GitBaseTask
{
    /**
     * --aggressive key to git-gc
     *
     * @var bool
     */
    private $isAggressive = false;

    /**
     * --auto key to git-gc
     *
     * @var bool
     */
    private $isAuto = false;

    /**
     * --no-prune key to git-gc
     *
     * @var bool
     */
    private $noPrune = false;

    /**
     * --prune=<date>option of git-gc
     *
     * @var string
     */
    private $prune = '2.weeks.ago';

    /**
     * The main entry point for the task
     *
     * @return void
     *
     * @throws VersionControl_Git_Exception
     * @throws Exception
     */
    public function main(): void
    {
        if (null === $this->getRepository()) {
            throw new BuildException('"repository" is required parameter');
        }

        $client  = $this->getGitClient(false, $this->getRepository());
        $command = $client->getCommand('gc');
        $command
            ->setOption('aggressive', $this->isAggressive())
            ->setOption('auto', $this->isAuto())
            ->setOption('no-prune', $this->isNoPrune());
        if ($this->isNoPrune() == false) {
            $command->setOption('prune', $this->getPrune());
        }

        // suppress output
        $command->setOption('q');

        $this->log('git-gc command: ' . $command->createCommandString(), Project::MSG_INFO);

        try {
            $command->execute();
        } catch (Throwable $e) {
            throw new BuildException('Task execution failed', $e);
        }

        $this->log(
            sprintf('git-gc: cleaning up "%s" repository', $this->getRepository()),
            Project::MSG_INFO
        );
    }

    /**
     * @see getAggressive()
     *
     * @return bool
     */
    public function isAggressive(): bool
    {
        return $this->getAggressive();
    }

    /**
     * @return bool
     */
    public function getAggressive(): bool
    {
        return $this->isAggressive;
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setAggressive(bool $flag): void
    {
        $this->isAggressive = $flag;
    }

    /**
     * @see getAuto()
     *
     * @return bool
     */
    public function isAuto(): bool
    {
        return $this->getAuto();
    }

    /**
     * @return bool
     */
    public function getAuto(): bool
    {
        return $this->isAuto;
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setAuto(bool $flag): void
    {
        $this->isAuto = $flag;
    }

    /**
     * @see NoPrune()
     *
     * @return bool
     */
    public function isNoPrune(): bool
    {
        return $this->getNoPrune();
    }

    /**
     * @return bool
     */
    public function getNoPrune(): bool
    {
        return $this->noPrune;
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setNoPrune(bool $flag): void
    {
        $this->noPrune = $flag;
    }

    /**
     * @return string
     */
    public function getPrune(): string
    {
        return $this->prune;
    }

    /**
     * @param string $date
     *
     * @return void
     */
    public function setPrune(string $date): void
    {
        $this->prune = $date;
    }
}
