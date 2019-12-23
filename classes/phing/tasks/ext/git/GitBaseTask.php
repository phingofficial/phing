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
 * Base class for Git tasks
 *
 * @see     VersionControl_Git
 *
 * @author  Victor Farazdagi <simple.square@gmail.com>
 * @package phing.tasks.ext.git
 * @since   2.4.3
 */
abstract class GitBaseTask extends Task
{
    /**
     * Bath to git binary
     *
     * @var string
     */
    private $gitPath = '/usr/bin/git';

    /**
     * @var VersionControl_Git
     */
    private $gitClient = null;

    /**
     * Current repository directory
     *
     * @var string|null
     */
    private $repository;

    /**
     * Initialize Task.
     * Check and include necessary libraries.
     *
     * @return void
     */
    public function init(): void
    {
        @include_once 'VersionControl/Git.php';
        if (false == class_exists('VersionControl_Git')) {
            throw new BuildException(
                "The Git tasks depend on PEAR\'s "
                . 'VersionControl_Git package.',
                $this->getLocation()
            );
        }
    }

    /**
     * Set repository directory
     *
     * @param string|null $repository Repo directory
     *
     * @return void
     */
    public function setRepository(?string $repository): void
    {
        $this->repository = $repository;
    }

    /**
     * Get repository directory
     *
     * @return string|null
     */
    public function getRepository(): ?string
    {
        return $this->repository;
    }

    /**
     * Set path to git executable
     *
     * @param string $gitPath New path to git repository
     *
     * @return void
     */
    public function setGitPath(string $gitPath): void
    {
        $this->gitPath = $gitPath;
    }

    /**
     * Get path to git executable
     *
     * @return string
     */
    public function getGitPath(): string
    {
        return $this->gitPath;
    }

    /**
     * @param bool|null   $reset
     * @param string|null $repository
     *
     * @return VersionControl_Git|null
     *
     * @throws BuildException
     */
    protected function getGitClient(?bool $reset = false, ?string $repository = null): ?VersionControl_Git
    {
        $this->gitClient = $reset === true ? null : $this->gitClient;
        $repository      = $repository ?? $this->getRepository();

        if (null === $this->gitClient) {
            try {
                $this->gitClient = new VersionControl_Git($repository);
            } catch (VersionControl_Git_Exception $e) {
                // re-package
                throw new BuildException(
                    'You must specify readable directory as repository.',
                    $e
                );
            }
        }
        $this->gitClient->setGitCommandPath($this->getGitPath());

        return $this->gitClient;
    }
}
