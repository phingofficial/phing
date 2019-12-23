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
 * Repository initialization task
 *
 * @see     VersionControl_Git
 *
 * @author  Victor Farazdagi <simple.square@gmail.com>
 * @package phing.tasks.ext.git
 * @since   2.4.3
 */
class GitInitTask extends GitBaseTask
{
    /**
     * Whether --bare key should be set for git-init
     *
     * @var bool
     */
    private $isBare = false;

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

        $client = $this->getGitClient();
        $client->initRepository($this->isBare());

        $msg = 'git-init: initializing '
            . ($this->isBare() ? '(bare) ' : '')
            . '"' . $this->getRepository() . '" repository';
        $this->log($msg, Project::MSG_INFO);
    }

    /**
     * Alias @see getBare()
     *
     * @return bool
     */
    public function isBare(): bool
    {
        return $this->getBare();
    }

    /**
     * @return bool
     */
    public function getBare(): bool
    {
        return $this->isBare;
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setBare(bool $flag): void
    {
        $this->isBare = $flag;
    }
}
