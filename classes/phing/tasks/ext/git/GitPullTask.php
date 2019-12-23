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
 * Wrapper aroung git-pull
 *
 * @see     VersionControl_Git
 *
 * @author  Victor Farazdagi <simple.square@gmail.com>
 * @package phing.tasks.ext.git
 * @since   2.4.3
 */
class GitPullTask extends GitBaseTask
{
    /**
     * <repository> argument to git-pull
     *
     * @var string
     */
    private $source = 'origin';

    /**
     * <refspec> argument to git-pull
     *
     * @var string
     */
    private $refspec;

    /**
     * --rebase key to git-pull
     *
     * @var bool
     */
    private $rebase = false;

    /**
     * --no-rebase key to git-pull
     * Allow to override --rebase (if set to default true in configuration)
     *
     * @var bool
     */
    private $noRebase = false;

    /**
     * Merge strategy. See -s <strategy> of git-pull
     *
     * @var string
     */
    private $strategy;

    /**
     * -X or --strategy-option of git-pull
     *
     * @var string
     */
    private $strategyOption;

    /**
     * Fetch all remotes
     * --all key to git-pull
     *
     * @var bool
     */
    private $allRemotes = false;

    /**
     * --append key to git-pull
     *
     * @var bool
     */
    private $append = false;

    /**
     * Keep downloaded pack
     * --keep key to git-pull
     *
     * @var bool
     */
    private $keepFiles = false;

    /**
     * Disable/enable automatic tag following
     * --no-tags key to git-pull
     *
     * @var bool
     */
    private $noTags = false;

    /**
     * Fetch all tags (even not reachable from branch heads)
     * --tags key to git-pull
     *
     * @var bool
     */
    private $tags = false;

    /**
     * --quiet, -q key to git-pull
     *
     * @var bool
     */
    private $quiet = true;

    /**
     * --force, -f key to git-pull
     *
     * @var bool
     */
    private $force = false;

    /**
     * Valid merge strategies
     *
     * @var array
     */
    private $validStrategies = [
        'octopus',
        'ours',
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
     */
    public function main(): void
    {
        if (null === $this->getRepository()) {
            throw new BuildException('"repository" is required parameter');
        }

        $client  = $this->getGitClient(false, $this->getRepository());
        $command = $client->getCommand('pull');
        $command
            ->setOption('rebase', $this->isRebase());

        if (!$this->isRebase()) {
            $command->setOption('no-rebase', $this->isNoRebase());
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

        // order of arguments is important
        $command
            ->setOption('tags', $this->isTags())
            ->setOption('no-tags', $this->isNoTags())
            ->setOption('keep', $this->isKeepFiles())
            ->setOption('append', $this->isAppend())
            ->setOption('q', $this->isQuiet())
            ->setOption('force', $this->isForce());

        // set operation target
        if ($this->isAllRemotes()) { // --all
            $command->setOption('all', true);
            $this->log('git-pull: fetching from all remotes', Project::MSG_INFO);
        } elseif ($this->getSource()) { // <repository> [<refspec>]
            $command->addArgument($this->getSource());
            if ($this->getRefspec()) {
                $command->addArgument($this->getRefspec());
            }
            $this->log(
                sprintf(
                    'git-pull: pulling from %s %s',
                    $this->getSource(),
                    $this->getRefspec()
                ),
                Project::MSG_INFO
            );
        } else {
            throw new BuildException('No source repository specified');
        }

        $this->log('git-pull command: ' . $command->createCommandString(), Project::MSG_INFO);

        try {
            $output = $command->execute();
        } catch (Throwable $e) {
            throw new BuildException('Task execution failed.', $e);
        }

        $this->log('git-pull: complete', Project::MSG_INFO);
        $this->log('git-pull output: ' . trim($output), Project::MSG_INFO);
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
     * @param string $source
     *
     * @return void
     */
    public function setSource(string $source): void
    {
        $this->source = $source;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @param string $spec
     *
     * @return void
     */
    public function setRefspec(string $spec): void
    {
        $this->refspec = $spec;
    }

    /**
     * @return string
     */
    public function getRefspec(): string
    {
        return $this->refspec;
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setAll(bool $flag): void
    {
        $this->allRemotes = $flag;
    }

    /**
     * @return bool
     */
    public function getAll(): bool
    {
        return $this->allRemotes;
    }

    /**
     * @return bool
     */
    public function isAllRemotes(): bool
    {
        return $this->getAll();
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setAppend(bool $flag): void
    {
        $this->append = (bool) $flag;
    }

    /**
     * @return bool
     */
    public function getAppend(): bool
    {
        return $this->append;
    }

    /**
     * @return bool
     */
    public function isAppend(): bool
    {
        return $this->getAppend();
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setKeep(bool $flag): void
    {
        $this->keepFiles = $flag;
    }

    /**
     * @return bool
     */
    public function getKeep(): bool
    {
        return $this->keepFiles;
    }

    /**
     * @return bool
     */
    public function isKeepFiles(): bool
    {
        return $this->getKeep();
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setNoTags(bool $flag): void
    {
        $this->noTags = $flag;
    }

    /**
     * @return bool
     */
    public function getNoTags(): bool
    {
        return $this->noTags;
    }

    /**
     * @return bool
     */
    public function isNoTags(): bool
    {
        return $this->getNoTags();
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setTags(bool $flag): void
    {
        $this->tags = $flag;
    }

    /**
     * @return bool
     */
    public function getTags(): bool
    {
        return $this->tags;
    }

    /**
     * @return bool
     */
    public function isTags(): bool
    {
        return $this->getTags();
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
    public function isQuiet()
    {
        return $this->getQuiet();
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setRebase(bool $flag): void
    {
        $this->rebase = (bool) $flag;
    }

    /**
     * @return bool
     */
    public function getRebase(): bool
    {
        return $this->rebase;
    }

    /**
     * @return bool
     */
    public function isRebase(): bool
    {
        return $this->getRebase();
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setNoRebase(bool $flag): void
    {
        $this->noRebase = (bool) $flag;
    }

    /**
     * @return bool
     */
    public function getNoRebase(): bool
    {
        return $this->noRebase;
    }

    /**
     * @return bool
     */
    public function isNoRebase(): bool
    {
        return $this->getNoRebase();
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setForce(bool $flag): void
    {
        $this->force = $flag;
    }

    /**
     * @return bool
     */
    public function getForce(): bool
    {
        return $this->force;
    }

    /**
     * @return bool
     */
    public function isForce(): bool
    {
        return $this->getForce();
    }
}
