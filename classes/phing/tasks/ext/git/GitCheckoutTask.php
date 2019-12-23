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
 * Wrapper around git-checkout
 *
 * @see     VersionControl_Git
 *
 * @author  Victor Farazdagi <simple.square@gmail.com>
 * @package phing.tasks.ext.git
 * @since   2.4.3
 */
class GitCheckoutTask extends GitBaseTask
{
    /**
     * Branch name
     *
     * @var string|null
     */
    private $branchname;

    /**
     * If not HEAD, specify starting point
     *
     * @var string
     */
    private $startPoint;

    /**
     * --force, -f key to git-checkout
     *
     * @var bool
     */
    private $force = false;

    /**
     * --quiet, -q key to git-checkout
     *
     * @var bool
     */
    private $quiet = false;

    /**
     * When creating a new branch, set up "upstream" configuration.
     * --track key to git-checkout
     *
     * @var bool
     */
    private $track = false;

    /**
     * Do not set up "upstream" configuration
     * --no-track key to git-checkout
     *
     * @var bool
     */
    private $noTrack = false;

    /**
     * -b, -B, -m  options to git-checkout
     * Respective task options:
     * create, forceCreate, merge
     *
     * @var array
     */
    private $extraOptions = [
        'b' => false,
        'B' => false,
        'm' => false,
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
        if (null === $this->getBranchname()) {
            throw new BuildException('"branchname" is required parameter');
        }

        $client  = $this->getGitClient(false, $this->getRepository());
        $command = $client->getCommand('checkout');
        $command
            ->setOption('no-track', $this->isNoTrack())
            ->setOption('q', $this->isQuiet())
            ->setOption('force', $this->isForce())
            ->setOption('b', $this->isCreate())
            ->setOption('B', $this->isForceCreate())
            ->setOption('m', $this->isMerge());
        if ($this->isNoTrack()) {
            $command->setOption('track', $this->isTrack());
        }

        $command->addArgument($this->getBranchname());

        if (null !== $this->getStartPoint()) {
            $command->addArgument($this->getStartPoint());
        }

        $this->log('git-checkout command: ' . $command->createCommandString(), Project::MSG_INFO);

        try {
            $output = $command->execute();
        } catch (Throwable $e) {
            throw new BuildException('Task execution failed.', $e);
        }

        $this->log(
            sprintf('git-checkout: checkout "%s" repository', $this->getRepository()),
            Project::MSG_INFO
        );
        $this->log('git-checkout output: ' . str_replace('\'', '', trim($output)), Project::MSG_INFO);
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
    public function setCreate(bool $flag): void
    {
        $this->extraOptions['b'] = $flag;
    }

    /**
     * @return bool
     */
    public function getCreate(): bool
    {
        return $this->extraOptions['b'];
    }

    /**
     * @return bool
     */
    public function isCreate(): bool
    {
        return $this->getCreate();
    }

    // -B flag is not found in all versions of git
    // --force is present everywhere

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setForceCreate(bool $flag): void
    {
        $this->setForce($flag);
    }

    /**
     * @return bool
     */
    public function getForceCreate(): bool
    {
        return $this->extraOptions['B'];
    }

    /**
     * @return bool
     */
    public function isForceCreate(): bool
    {
        return $this->getForceCreate();
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setMerge(bool $flag): void
    {
        $this->extraOptions['m'] = $flag;
    }

    /**
     * @return bool
     */
    public function getMerge(): bool
    {
        return $this->extraOptions['m'];
    }

    /**
     * @return bool
     */
    public function isMerge(): bool
    {
        return $this->getMerge();
    }
}
