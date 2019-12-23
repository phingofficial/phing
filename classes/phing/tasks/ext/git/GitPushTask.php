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
 * Wrapper aroung git-push
 *
 * @link    http://www.kernel.org/pub/software/scm/git/docs/git-push.html
 * @see     VersionControl_Git
 *
 * @author  Victor Farazdagi <simple.square@gmail.com>
 * @package phing.tasks.ext.git
 * @since   2.4.3
 */
class GitPushTask extends GitBaseTask
{
    /**
     * Instead of naming each ref to push, specifies that all refs
     * --all key to git-push
     *
     * @var bool
     */
    private $allRemotes = false;

    /**
     * Mirror to remote repository
     * --mirror key to git-push
     *
     * @var bool
     */
    private $mirror = false;

    /**
     * Same as prefixing repos with colon
     * --delete argument to git-push
     *
     * @var bool
     */
    private $delete = false;

    /**
     * Push all refs under refs/tags
     * --tags key to git-fetch
     *
     * @var bool
     */
    private $tags = false;

    /**
     * <repository> argument to git-push
     *
     * @var string
     */
    private $destination = 'origin';

    /**
     * <refspec> argument to git-push
     *
     * @var string
     */
    private $refspec;

    /**
     * --force, -f key to git-push
     *
     * @var bool
     */
    private $force = false;

    /**
     * --quiet, -q key to git-push
     *
     * @var bool
     */
    private $quiet = true;

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
        $command = $client->getCommand('push');
        $command
            ->setOption('tags', $this->isTags())
            ->setOption('mirror', $this->isMirror())
            ->setOption('delete', $this->isDelete())
            ->setOption('q', $this->isQuiet())
            ->setOption('force', $this->isForce());

        // set operation target
        if ($this->isAllRemotes()) { // --all
            $command->setOption('all', true);
            $this->log('git-push: push to all refs', Project::MSG_INFO);
        } elseif ($this->isMirror()) { // <repository> [<refspec>]
            $command->setOption('mirror', true);
            $this->log('git-push: mirror all refs', Project::MSG_INFO);
        } elseif ($this->getDestination()) { // <repository> [<refspec>]
            $command->addArgument($this->getDestination());
            if ($this->getRefspec()) {
                $command->addArgument($this->getRefspec());
            }
            $this->log(
                sprintf(
                    'git-push: pushing to %s %s',
                    $this->getDestination(),
                    $this->getRefspec()
                ),
                Project::MSG_INFO
            );
        } else {
            throw new BuildException('At least one destination must be provided');
        }

        $this->log('git-push command: ' . $command->createCommandString(), Project::MSG_INFO);

        try {
            $output = $command->execute();
        } catch (Throwable $e) {
            throw new BuildException('Task execution failed.', $e);
        }

        $this->log('git-push: complete', Project::MSG_INFO);
        if ($this->isDelete()) {
            $this->log('git-push: branch delete requested', Project::MSG_INFO);
        }
        $this->log('git-push output: ' . trim($output), Project::MSG_INFO);
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
    public function setMirror(bool $flag): void
    {
        $this->mirror = (bool) $flag;
    }

    /**
     * @return bool
     */
    public function getMirror(): bool
    {
        return $this->mirror;
    }

    /**
     * @return bool
     */
    public function isMirror(): bool
    {
        return $this->getMirror();
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setDelete(bool $flag): void
    {
        $this->delete = (bool) $flag;
    }

    /**
     * @return bool
     */
    public function getDelete(): bool
    {
        return $this->delete;
    }

    /**
     * @return bool
     */
    public function isDelete(): bool
    {
        return $this->getDelete();
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
     * @param string $destination
     *
     * @return void
     */
    public function setDestination(string $destination): void
    {
        $this->destination = $destination;
    }

    /**
     * @return string
     */
    public function getDestination(): string
    {
        return $this->destination;
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
}
