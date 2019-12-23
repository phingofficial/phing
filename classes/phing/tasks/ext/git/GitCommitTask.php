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
 * Wrapper around git-commit
 *
 * @see     VersionControl_Git
 *
 * @package phing.tasks.ext.git
 * @author  Jonathan Creasy <jonathan.creasy@gmail.com>
 * @since   2.4.3
 */
class GitCommitTask extends GitBaseTask
{
    use FileSetAware;

    /**
     * @var bool
     */
    private $allFiles = false;

    /**
     * @var string
     */
    private $message;

    /**
     * The main entry point for the task
     *
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     * @throws ReflectionException
     * @throws Exception
     */
    public function main(): void
    {
        if (null === $this->getRepository()) {
            throw new BuildException('"repository" is required parameter');
        }

        if ($this->allFiles !== true && empty($this->filesets)) {
            throw new BuildException('"allFiles" cannot be false if no filesets are specified.');
        }

        $options = [];
        if ($this->allFiles === true) {
            $options['all'] = true;
        }

        $arguments = [];
        if ($this->allFiles !== true) {
            foreach ($this->filesets as $fs) {
                $ds       = $fs->getDirectoryScanner($this->project);
                $srcFiles = $ds->getIncludedFiles();

                foreach ($srcFiles as $file) {
                    $arguments[] = $file;
                }
            }
        }

        if (!empty($this->message)) {
            $options['message'] = $this->message;
        } else {
            $options['allow-empty-message'] = true;
        }

        try {
            $client = $this->getGitClient(false, $this->getRepository());

            $command = $client->getCommand('commit');
            $command->setArguments($arguments);
            $command->setOptions($options);
            $command->execute();
        } catch (Throwable $e) {
            throw new BuildException('The remote end hung up unexpectedly', $e);
        }

        $this->logCommand($options, $arguments);
    }

    /**
     * @param array $options
     * @param array $arguments
     *
     * @return void
     *
     * @throws Exception
     */
    protected function logCommand(array $options, array $arguments): void
    {
        $msg = 'git-commit: Executed git commit ';
        foreach ($options as $option => $value) {
            $msg .= ' --' . $option . '=' . $value;
        }

        foreach ($arguments as $argument) {
            $msg .= ' ' . $argument;
        }

        $this->log($msg, Project::MSG_INFO);
    }

    /**
     * @return bool
     */
    public function getAllFiles(): bool
    {
        return $this->allFiles;
    }

    /**
     * @param bool $flag
     *
     * @return void
     */
    public function setAllFiles(bool $flag): void
    {
        $this->allFiles = $flag;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     *
     * @return void
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}
