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
 * Repository archive task
 *
 * @see     VersionControl_Git
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.ext.git
 */
class GitArchiveTask extends GitBaseTask
{
    /**
     * @var string|null $format
     */
    private $format = null;

    /**
     * @var PhingFile $output
     */
    private $output;

    /**
     * @var string|null
     */
    private $prefix = null;

    /**
     * @var string|null
     */
    private $treeish;

    /**
     * @var string|null $remoteRepo
     */
    private $remoteRepo = null;

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
        if (null === $this->getRepository() && false === $this->getRemoteRepo()) {
            throw new BuildException('"repository" is required parameter');
        }

        if (null === $this->getTreeish()) {
            throw new BuildException('"treeish" is required parameter');
        }

        $cmd = $this->getGitClient(false, $this->getRepository() ?? './')
            ->getCommand('archive')
            ->setOption('prefix', $this->prefix)
            ->setOption('output', $this->output !== null ? $this->output->getPath() : false)
            ->setOption('format', $this->format)
            ->setOption('remote', $this->remoteRepo)
            ->addArgument($this->treeish);

        $this->log('Git command : ' . $cmd->createCommandString(), Project::MSG_DEBUG);

        $cmd->execute();

        $msg = 'git-archive: archivating "' . $this->getRepository() . '" repository (' . $this->getTreeish() . ')';
        $this->log($msg, Project::MSG_INFO);
    }

    /**
     * @return string|null
     */
    public function getFormat(): ?string
    {
        return $this->format;
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
     * @return PhingFile
     */
    public function getOutput(): PhingFile
    {
        return $this->output;
    }

    /**
     * @param PhingFile $output
     *
     * @return void
     */
    public function setOutput(PhingFile $output): void
    {
        $this->output = $output;
    }

    /**
     * @return string|null
     */
    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix
     *
     * @return void
     */
    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    /**
     * @return string|null
     */
    public function getTreeish(): ?string
    {
        return $this->treeish;
    }

    /**
     * @param string $treeish
     *
     * @return void
     */
    public function setTreeish(string $treeish): void
    {
        $this->treeish = $treeish;
    }

    /**
     * @return string|null
     */
    public function getRemoteRepo(): ?string
    {
        return $this->remoteRepo;
    }

    /**
     * @param string $repo
     *
     * @return void
     */
    public function setRemoteRepo(string $repo): void
    {
        $this->remoteRepo = $repo;
    }
}
