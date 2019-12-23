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
 * Wrapper aroung git-fetch
 *
 * @see     VersionControl_Git
 *
 * @author  Victor Farazdagi <simple.square@gmail.com>
 * @package phing.tasks.ext.git
 * @since   2.4.3
 */
class GitFetchTask extends GitBaseTask
{
    /**
     * --force, -f key to git-fetch
     *
     * @var bool
     */
    private $force = false;

    /**
     * --quiet, -q key to git-fetch
     *
     * @var bool
     */
    private $quiet = false;

    /**
     * Fetch all remotes
     * --all key to git-fetch
     *
     * @var bool
     */
    private $allRemotes = false;

    /**
     * Keep downloaded pack
     * --keep key to git-fetch
     *
     * @var bool
     */
    private $keepFiles = false;

    /**
     * After fetching, remove any remote tracking branches which no longer
     * exist on the remote.
     * --prune key to git fetch
     *
     * @var bool
     */
    private $prune = false;

    /**
     * Disable/enable automatic tag following
     * --no-tags key to git-fetch
     *
     * @var bool
     */
    private $noTags = false;

    /**
     * Fetch all tags (even not reachable from branch heads)
     * --tags key to git-fetch
     *
     * @var bool
     */
    private $tags = false;

    /**
     * <group> argument to git-fetch
     *
     * @var string
     */
    private $group;

    /**
     * <repository> argument to git-fetch
     *
     * @var string
     */
    private $source = 'origin';

    /**
     * <refspec> argument to git-fetch
     *
     * @var string
     */
    private $refspec;

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
        $command = $client->getCommand('fetch');
        $command
            ->setOption('tags', $this->isTags())
            ->setOption('no-tags', $this->isNoTags())
            ->setOption('prune', $this->isPrune())
            ->setOption('keep', $this->isKeepFiles())
            ->setOption('q', $this->isQuiet())
            ->setOption('force', $this->isForce());

        // set operation target
        if ($this->isAllRemotes()) { // --all
            $command->setOption('all', true);
        } elseif ($this->getGroup()) { // <group>
            $command->addArgument($this->getGroup());
        } elseif ($this->getSource()) { // <repository> [<refspec>]
            $command->addArgument($this->getSource());
            if ($this->getRefspec()) {
                $command->addArgument($this->getRefspec());
            }
        } else {
            throw new BuildException('No remote repository specified');
        }

        $this->log('git-fetch command: ' . $command->createCommandString(), Project::MSG_INFO);

        try {
            $output = $command->execute();
        } catch (Throwable $e) {
            throw new BuildException('Task execution failed.', $e);
        }

        $this->log(
            sprintf('git-fetch: branch "%s" repository', $this->getRepository()),
            Project::MSG_INFO
        );
        $this->log('git-fetch output: ' . trim($output), Project::MSG_INFO);
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
    public function setPrune(bool $flag): void
    {
        $this->prune = $flag;
    }

    /**
     * @return bool
     */
    public function getPrune(): bool
    {
        return $this->prune;
    }

    /**
     * @return bool
     */
    public function isPrune(): bool
    {
        return $this->getPrune();
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
     * @param string $group
     *
     * @return void
     */
    public function setGroup(string $group): void
    {
        $this->group = $group;
    }

    /**
     * @return string|null
     */
    public function getGroup(): ?string
    {
        return $this->group;
    }
}
