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
 * Wrapper around git-describe
 *
 * @see     VersionControl_Git
 *
 * @package phing.tasks.ext.git
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class GitDescribeTask extends GitBaseTask
{
    /**
     * Use any ref found in .git/refs/. See --all of git-describe
     *
     * @var bool
     */
    private $all = false;

    /**
     * Use any tag found in .git/refs/tags. See --tags of git-describe
     *
     * @var bool
     */
    private $tags = false;

    /**
     * Find tag that contains the commit. See --contains of git-describe
     *
     * @var bool
     */
    private $contains = false;

    /**
     * Use <n> digit object name. See --abbrev of git-describe
     *
     * @var int
     */
    private $abbrev;

    /**
     * Consider up to <n> most recent tags. See --candidates of git-describe
     *
     * @var int
     */
    private $candidates;

    /**
     * Always output the long format. See --long of git-describe
     *
     * @var bool
     */
    private $long = false;

    /**
     * Only consider tags matching the given pattern. See --match of git-describe
     *
     * @var string
     */
    private $match;

    /**
     * Show uniquely abbreviated commit object as fallback. See --always of git-describe
     *
     * @var bool
     */
    private $always = false;

    /**
     * <committish> argument to git-describe
     *
     * @var string
     */
    private $committish;

    /**
     * Property name to set with output value from git-describe
     *
     * @var string
     */
    private $outputProperty;

    /**
     * The main entry point for the task
     *
     * @return void
     *
     * @throws Exception
     */
    public function main(): void
    {
        if (null === $this->getRepository()) {
            throw new BuildException('"repository" is required parameter');
        }

        $client  = $this->getGitClient(false, $this->getRepository());
        $command = $client->getCommand('describe');
        $command
            ->setOption('all', $this->isAll())
            ->setOption('tags', $this->isTags())
            ->setOption('contains', $this->isContains())
            ->setOption('long', $this->isLong())
            ->setOption('always', $this->isAlways());

        if (null !== $this->getAbbrev()) {
            $command->setOption('abbrev', $this->getAbbrev());
        }
        if (null !== $this->getCandidates()) {
            $command->setOption('candidates', $this->getCandidates());
        }
        if (null !== $this->getMatch()) {
            $command->setOption('match', $this->getMatch());
        }
        if (null !== $this->getCommittish()) {
            $command->addArgument($this->getCommittish());
        }

        try {
            $output = $command->execute();
        } catch (Throwable $e) {
            throw new BuildException('Task execution failed');
        }

        if (null !== $this->outputProperty) {
            $this->project->setProperty($this->outputProperty, $output);
        }

        $this->log(
            sprintf('git-describe: recent tags for "%s" repository', $this->getRepository()),
            Project::MSG_INFO
        );
        $this->log('git-describe output: ' . trim($output), Project::MSG_INFO);
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setAll(bool $flag): void
    {
        $this->all = $flag;
    }

    /**
     * @return bool
     */
    public function getAll(): bool
    {
        return $this->all;
    }

    /**
     * @return bool
     */
    public function isAll(): bool
    {
        return $this->getAll();
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
    public function setContains(bool $flag): void
    {
        $this->contains = $flag;
    }

    /**
     * @return bool
     */
    public function getContains(): bool
    {
        return $this->contains;
    }

    /**
     * @return bool
     */
    public function isContains(): bool
    {
        return $this->getContains();
    }

    /**
     * @param int $length
     *
     * @return void
     */
    public function setAbbrev(int $length): void
    {
        $this->abbrev = (int) $length;
    }

    /**
     * @return int
     */
    public function getAbbrev(): int
    {
        return $this->abbrev;
    }

    /**
     * @param int $count
     *
     * @return void
     */
    public function setCandidates(int $count): void
    {
        $this->candidates = (int) $count;
    }

    /**
     * @return int
     */
    public function getCandidates(): int
    {
        return $this->candidates;
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setLong(bool $flag): void
    {
        $this->long = $flag;
    }

    /**
     * @return bool
     */
    public function getLong(): bool
    {
        return $this->long;
    }

    /**
     * @return bool
     */
    public function isLong(): bool
    {
        return $this->getLong();
    }

    /**
     * @param string $pattern
     *
     * @return void
     */
    public function setMatch(string $pattern): void
    {
        $this->match = $pattern;
    }

    /**
     * @return string
     */
    public function getMatch(): string
    {
        return $this->match;
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setAlways(bool $flag): void
    {
        $this->always = $flag;
    }

    /**
     * @return bool
     */
    public function getAlways(): bool
    {
        return $this->always;
    }

    /**
     * @return bool
     */
    public function isAlways(): bool
    {
        return $this->getAlways();
    }

    /**
     * @param string $object
     *
     * @return void
     */
    public function setCommittish(string $object): void
    {
        $this->committish = $object;
    }

    /**
     * @return string
     */
    public function getCommittish(): string
    {
        return $this->committish;
    }

    /**
     * @param string $prop
     *
     * @return void
     */
    public function setOutputProperty(string $prop): void
    {
        $this->outputProperty = $prop;
    }
}
