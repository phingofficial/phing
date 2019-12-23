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
 * Wrapper aroung git-merge
 *
 * @link    http://www.kernel.org/pub/software/scm/git/docs/git-merge.html
 * @see     VersionControl_Git
 *
 * @author  Victor Farazdagi <simple.square@gmail.com>
 * @package phing.tasks.ext.git
 * @since   2.4.3
 */
class GitMergeTask extends GitBaseTask
{
    /**
     * <commit> of git-merge
     *
     * @var string
     */
    private $remote;

    /**
     * Commit message
     *
     * @var string
     */
    private $message;

    /**
     * Merge strategy. See -s <strategy> of git-merge
     * Available strategies are: octopus ours recursive resolve subtree
     *
     * @var string
     */
    private $strategy;

    /**
     * -X or --strategy-option of git-merge
     *
     * @var string
     */
    private $strategyOption;

    /**
     * --commit key of git-merge
     *
     * @var bool
     */
    private $commit = false;

    /**
     * --no-commit key of git-merge
     *
     * @var bool
     */
    private $noCommit = false;

    /**
     * --ff --no-ff keys to git-merge
     *
     * @var bool
     */
    private $fastForwardCommit = false;

    /**
     * --quiet, -q key to git-merge
     *
     * @var bool
     */
    private $quiet = false;

    /**
     * Valid merge strategies
     *
     * @var array
     */
    private $validStrategies = [
        'octopus',
        'ours',
        'theirs',
        'recursive',
        'resolve',
        'subtree',
    ];

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
        $remotes = trim($this->getRemote());
        if (null === $remotes || '' === $remotes) {
            throw new BuildException('"remote" is required parameter');
        }

        $client  = $this->getGitClient(false, $this->getRepository());
        $command = $client->getCommand('merge');
        $command
            ->setOption('commit', $this->isCommit())
            ->setOption('q', $this->isQuiet());

        if ($this->getMessage()) {
            $command->setOption('message', $this->getMessage());
        }

        if (!$this->isCommit()) {
            $command->setOption('no-commit', $this->isNoCommit());
        }

        if ($this->isFastForwardCommit()) {
            $command->setOption('no-ff', true);
        }

        $strategy = $this->getStrategy();
        if ($strategy) {
            // check if strategy is valid
            if (false === in_array($strategy, $this->validStrategies)) {
                throw new BuildException(
                    "Could not find merge strategy '" . $strategy . "'\n" .
                    'Available strategies are: ' . implode(', ', $this->validStrategies)
                );
            }
            $command->setOption('strategy', $strategy);
            if ($this->getStrategyOption()) {
                $command->setOption(
                    'strategy-option',
                    $this->getStrategyOption()
                );
            }
        }

        $remotes = explode(' ', $this->getRemote());
        foreach ($remotes as $remote) {
            $command->addArgument($remote);
        }

        $this->log('git-merge command: ' . $command->createCommandString(), Project::MSG_INFO);

        try {
            $output = $command->execute();
        } catch (Throwable $e) {
            throw new BuildException('Task execution failed.', $e);
        }

        $this->log(
            sprintf('git-merge: replaying "%s" commits', $this->getRemote()),
            Project::MSG_INFO
        );
        $this->log('git-merge output: ' . trim($output), Project::MSG_INFO);
    }

    /**
     * @param string $remote
     *
     * @return void
     */
    public function setRemote(string $remote): void
    {
        $this->remote = $remote;
    }

    /**
     * @return string
     */
    public function getRemote(): string
    {
        return $this->remote;
    }

    /**
     * @param string $message
     *
     * @return void
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $strategy
     *
     * @return void
     */
    public function setStrategy(string $strategy): void
    {
        $this->strategy = $strategy;
    }

    /**
     * @return string
     */
    public function getStrategy(): string
    {
        return $this->strategy;
    }

    /**
     * @param string $strategyOption
     *
     * @return void
     */
    public function setStrategyOption(string $strategyOption): void
    {
        $this->strategyOption = $strategyOption;
    }

    /**
     * @return string
     */
    public function getStrategyOption(): string
    {
        return $this->strategyOption;
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setQuiet(bool $flag): void
    {
        $this->quiet = $flag;
    }

    /**
     * @return bool
     */
    public function getQuiet(): bool
    {
        return $this->quiet;
    }

    /**
     * @return bool
     */
    public function isQuiet(): bool
    {
        return $this->getQuiet();
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setCommit(bool $flag): void
    {
        $this->commit = (bool) $flag;
    }

    /**
     * @return bool
     */
    public function getCommit(): bool
    {
        return $this->commit;
    }

    /**
     * @return bool
     */
    public function isCommit(): bool
    {
        return $this->getCommit();
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setNoCommit(bool $flag): void
    {
        $this->noCommit = (bool) $flag;
    }

    /**
     * @return bool
     */
    public function getNoCommit(): bool
    {
        return $this->noCommit;
    }

    /**
     * @return bool
     */
    public function isNoCommit(): bool
    {
        return $this->getNoCommit();
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setFastForwardCommit(bool $flag): void
    {
        $this->fastForwardCommit = $flag;
    }

    /**
     * @return bool
     */
    public function getFastForwardCommit(): bool
    {
        return $this->fastForwardCommit;
    }

    /**
     * @return bool
     */
    public function isFastForwardCommit(): bool
    {
        return $this->getFastForwardCommit();
    }
}
