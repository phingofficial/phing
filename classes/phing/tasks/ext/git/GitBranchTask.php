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
 * Wrapper aroung git-branch
 *
 * @see     VersionControl_Git
 *
 * @author  Victor Farazdagi <simple.square@gmail.com>
 * @package phing.tasks.ext.git
 * @since   2.4.3
 */
class GitBranchTask extends GitBaseTask
{
    /**
     * Branch name
     *
     * @var string|null
     */
    private $branchname;

    /**
     * New Branch name for git-branch -m | -M
     *
     * @var string
     */
    private $newbranch;

    /**
     * If not HEAD, specify starting point
     *
     * @var string
     */
    private $startPoint;

    /**
     * --set-upstream key to git-branch
     *
     * @var bool
     */
    private $setUpstream = false;

    /**
     * --track key to git-branch
     *
     * @var bool
     */
    private $track = false;

    /**
     * --no-track key to git-branch
     *
     * @var bool
     */
    private $noTrack = false;

    /**
     * --force, -f key to git-branch
     *
     * @var bool
     */
    private $force = false;

    /**
     * -d, -D, -m, -M options to git-branch
     * Respective task options:
     * delete, forceDelete, move, forceMove
     *
     * @var array
     */
    private $extraOptions = [
        'd' => false,
        'D' => false,
        'm' => false,
        'M' => false,
    ];

    /**
     * @var string $setUpstreamTo
     */
    private $setUpstreamTo = '';

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
        if (null === $this->getBranchname()) {
            throw new BuildException('"branchname" is required parameter');
        }

        // if we are moving branch, we need to know new name
        if ($this->isMove() || $this->isForceMove()) {
            if (null === $this->getNewBranch()) {
                throw new BuildException('"newbranch" is required parameter');
            }
        }

        $client = $this->getGitClient(false, $this->getRepository());

        $command = $client->getCommand('branch');

        if (version_compare($client->getGitVersion(), '2.15.0', '<')) {
            $command->setOption('set-upstream', $this->isSetUpstream());
        } elseif ($this->isSetUpstreamTo()) {
            $command->setOption('set-upstream-to', $this->getSetUpstreamTo());
        }

        $command
            ->setOption('no-track', $this->isNoTrack())
            ->setOption('force', $this->isForce());
        if ($this->isNoTrack() == false) {
            $command->setOption('track', $this->getTrack());
        }

        // check extra options (delete, move)
        foreach ($this->extraOptions as $option => $flag) {
            if ($flag) {
                $command->setOption($option, true);
            }
        }

        $command->addArgument($this->getBranchname());

        if (null !== $this->getStartPoint()) {
            $command->addArgument($this->getStartPoint());
        }

        if (null !== $this->getNewBranch()) {
            $command->addArgument($this->getNewBranch());
        }

        $this->log('git-branch command: ' . $command->createCommandString(), Project::MSG_INFO);

        try {
            $output = $command->execute();
        } catch (Throwable $e) {
            throw new BuildException(
                'Task execution failed with git command "' . $command->createCommandString() . '""',
                $e
            );
        }

        $this->log(
            sprintf('git-branch: branch "%s" repository', $this->getRepository()),
            Project::MSG_INFO
        );
        $this->log('git-branch output: ' . str_replace('\'', '', trim($output)), Project::MSG_INFO);
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setSetUpstream(bool $flag): void
    {
        $this->setUpstream = $flag;
    }

    /**
     * @return bool
     */
    public function getSetUpstream(): bool
    {
        return $this->setUpstream;
    }

    /**
     * @return bool
     */
    public function isSetUpstream(): bool
    {
        return $this->getSetUpstream();
    }

    /**
     * @param string $branch
     *
     * @return void
     */
    public function setSetUpstreamTo(string $branch): void
    {
        $this->setUpstreamTo = $branch;
    }

    /**
     * @return string
     */
    public function getSetUpstreamTo(): string
    {
        return $this->setUpstreamTo;
    }

    /**
     * @return bool
     */
    public function isSetUpstreamTo(): bool
    {
        return $this->getSetUpstreamTo() !== '';
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setTrack(bool $flag): void
    {
        $this->track = $flag;
    }

    /**
     * @return bool
     */
    public function getTrack(): bool
    {
        return $this->track;
    }

    /**
     * @return bool
     */
    public function isTrack(): bool
    {
        return $this->getTrack();
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setNoTrack(bool $flag): void
    {
        $this->noTrack = $flag;
    }

    /**
     * @return bool
     */
    public function getNoTrack(): bool
    {
        return $this->noTrack;
    }

    /**
     * @return bool
     */
    public function isNoTrack(): bool
    {
        return $this->getNoTrack();
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
     * @param string $branchname
     *
     * @return void
     */
    public function setBranchname(string $branchname): void
    {
        $this->branchname = $branchname;
    }

    /**
     * @return string|null
     */
    public function getBranchname(): ?string
    {
        return $this->branchname;
    }

    /**
     * @param string $startPoint
     *
     * @return void
     */
    public function setStartPoint(string $startPoint): void
    {
        $this->startPoint = $startPoint;
    }

    /**
     * @return string
     */
    public function getStartPoint(): string
    {
        return $this->startPoint;
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setDelete(bool $flag): void
    {
        $this->extraOptions['d'] = $flag;
    }

    /**
     * @return bool
     */
    public function getDelete(): bool
    {
        return $this->extraOptions['d'];
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
    public function setForceDelete(bool $flag): void
    {
        $this->extraOptions['D'] = $flag;
    }

    /**
     * @return bool
     */
    public function getForceDelete(): bool
    {
        return $this->extraOptions['D'];
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setMove(bool $flag): void
    {
        $this->extraOptions['m'] = $flag;
    }

    /**
     * @return bool
     */
    public function getMove(): bool
    {
        return $this->extraOptions['m'];
    }

    /**
     * @return bool
     */
    public function isMove(): bool
    {
        return $this->getMove();
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setForceMove(bool $flag): void
    {
        $this->extraOptions['M'] = $flag;
    }

    /**
     * @return bool
     */
    public function getForceMove(): bool
    {
        return $this->extraOptions['M'];
    }

    /**
     * @return bool
     */
    public function isForceMove(): bool
    {
        return $this->getForceMove();
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function setNewBranch(string $name): void
    {
        $this->newbranch = $name;
    }

    /**
     * @return string
     */
    public function getNewBranch(): string
    {
        return $this->newbranch;
    }
}
