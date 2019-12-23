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
 * Wrapper aroung git-log
 *
 * @see     VersionControl_Git
 *
 * @author  Evan Kaufman <evan@digitalflophouse.com>
 * @author  Victor Farazdagi <simple.square@gmail.com>
 * @package phing.tasks.ext.git
 * @since   2.4.5
 */
class GitLogTask extends GitBaseTask
{
    /**
     * Generate a diffstat. See --stat of git-log
     *
     * @var string|bool
     */
    private $stat = false;

    /**
     * Names + status of changed files. See --name-status of git-log
     *
     * @var bool
     */
    private $nameStatus = false;

    /**
     * Number of commits to show. See -<n>|-n|--max-count of git-log
     *
     * @var int
     */
    private $maxCount;

    /**
     * Don't show commits with more than one parent. See --no-merges of git-log
     *
     * @var bool
     */
    private $noMerges = false;

    /**
     * Commit format. See --format of git-log
     *
     * @var string
     */
    private $format = 'medium';

    /**
     * Date format. See --date of git-log
     *
     * @var string
     */
    private $date;

    /**
     * <since> argument to git-log
     *
     * @var string
     */
    private $since;

    /**
     * <until> argument to git-log
     *
     * @var string
     */
    private $until;

    /**
     * <path> arguments to git-log
     * Accepts one or more paths delimited by PATH_SEPARATOR
     *
     * @var string
     */
    private $paths;

    /**
     * Property name to set with output value from git-log
     *
     * @var string
     */
    private $outputProperty;

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
        $command = $client->getCommand('log');
        $command
            ->setOption('stat', $this->getStat())
            ->setOption('name-status', $this->isNameStatus())
            ->setOption('no-merges', $this->isNoMerges())
            ->setOption('format', $this->getFormat());

        if (null !== $this->getMaxCount()) {
            $command->setOption('max-count', $this->getMaxCount());
        }

        if (null !== $this->getDate()) {
            $command->setOption('date', $this->getDate());
        }

        if (null !== $this->getSince()) {
            $command->setOption('since', $this->getSince());
        }

        if (null !== $this->getUntil()) {
            $command->setOption('until', $this->getUntil());
        }

        $command->addDoubleDash(true);
        if (null !== $this->getPaths()) {
            $command->addDoubleDash(false);
            $paths = explode(PATH_SEPARATOR, $this->getPaths());
            foreach ($paths as $path) {
                $command->addArgument($path);
            }
        }

        $this->log('git-log command: ' . $command->createCommandString(), Project::MSG_INFO);

        try {
            $output = $command->execute();
        } catch (Throwable $e) {
            throw new BuildException('Task execution failed', $e);
        }

        if (null !== $this->outputProperty) {
            $this->project->setProperty($this->outputProperty, trim($output));
        }

        $this->log(
            sprintf('git-log: commit log for "%s" repository', $this->getRepository()),
            Project::MSG_INFO
        );
        $this->log('git-log output: ' . trim($output), Project::MSG_INFO);
    }

    /**
     * @param bool|string $stat
     *
     * @return void
     */
    public function setStat($stat): void
    {
        $this->stat = $stat;
    }

    /**
     * @return bool|string
     */
    public function getStat()
    {
        return $this->stat;
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setNameStatus(bool $flag): void
    {
        $this->nameStatus = (bool) $flag;
    }

    /**
     * @return bool
     */
    public function getNameStatus(): bool
    {
        return $this->nameStatus;
    }

    /**
     * @return bool
     */
    public function isNameStatus(): bool
    {
        return $this->getNameStatus();
    }

    /**
     * @param int $count
     *
     * @return void
     */
    public function setMaxCount(int $count): void
    {
        $this->maxCount = (int) $count;
    }

    /**
     * @return int
     */
    public function getMaxCount(): int
    {
        return $this->maxCount;
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setNoMerges(bool $flag): void
    {
        $this->noMerges = $flag;
    }

    /**
     * @return bool
     */
    public function getNoMerges(): bool
    {
        return $this->noMerges;
    }

    /**
     * @return bool
     */
    public function isNoMerges(): bool
    {
        return $this->getNoMerges();
    }

    /**
     * @param string $format
     *
     * @return void
     */
    public function setFormat(string $format): void
    {
        $this->format = $format;
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * @param string $date
     *
     * @return void
     */
    public function setDate(string $date): void
    {
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @param string $since
     *
     * @return void
     */
    public function setSince(string $since): void
    {
        $this->since = $since;
    }

    /**
     * @return string
     */
    public function getSince(): string
    {
        return $this->since;
    }

    /**
     * @param string $after
     *
     * @return void
     */
    public function setAfter(string $after): void
    {
        $this->setSince($after);
    }

    /**
     * @param string $until
     *
     * @return void
     */
    public function setUntil(string $until): void
    {
        $this->until = $until;
    }

    /**
     * @return string
     */
    public function getUntil(): string
    {
        return $this->until;
    }

    /**
     * @param string $before
     *
     * @return void
     */
    public function setBefore(string $before): void
    {
        $this->setUntil($before);
    }

    /**
     * @param string $paths
     *
     * @return void
     */
    public function setPaths(string $paths): void
    {
        $this->paths = $paths;
    }

    /**
     * @return string
     */
    public function getPaths(): string
    {
        return $this->paths;
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
