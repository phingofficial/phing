<?php
/*
 *  $Id$
 *
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
 
require_once 'phing/Task.php';
require_once 'phing/tasks/ext/git/GitBaseTask.php';

/**
 * Wrapper aroung git-merge
 *
 * @author Victor Farazdagi <simple.square@gmail.com>
 * @version $Id$
 * @package phing.tasks.ext.git
 * @see VersionControl_Git
 * @since 2.4.3
 * @link http://www.kernel.org/pub/software/scm/git/docs/git-merge.html
 */
class GitMergeTask extends GitBaseTask
{
    /**
     * <commit> of git-merge
     * @var
     */
    private $remote;

    /**
     * Commit message
     * @var string
     */
    private $message;

    /**
     * Merge strategy. See -s <strategy> of git-merge
     * @var string
     */
    private $strategy;

    /**
     * -X or --strategy-option of git-merge
     * @var string
     */
    private $strategyOption;

    /**
     * --commit key of git-merge
     * @var boolean
     */
    private $commit = false;

    /**
     * --no-commit key of git-merge
     * @var boolean
     */
    private $noCommit = false;

    /**
     * --quiet, -q key to git-merge
     * @var boolean
     */
    private $quiet = false;

    /**
     * The main entry point for the task
     */
    public function main()
    {
        if (null === $this->getRepository()) {
            throw new BuildException('"repository" is required parameter');
        }
        if (null === $this->getRemote()) {
            throw new BuildException('"remote" is required parameter');
        }

        $client = $this->getGitClient(false, $this->getRepository());
        $command = $client->getCommand('merge');
        $command
            ->setOption('commit', $this->isCommit())
            ->setOption('q', $this->isQuiet());

        if ($this->isNoCommit()) {
            $command->setOption('no-commit', $this->isNoCommit());
        }

        if ($this->getStrategy()) {
            $command->setOption('strategy', $this->getStrategy());
            if ($this->getStrategyOption()) {
                $command->setOption(
                    'strategy-option', $this->getStrategyOption());
            }
        }

        $command->addArgument($this->getRemote());

        //echo $command->createCommandString();
        //exit;

        try {
            $output = $command->execute();
        } catch (Exception $e) {
            throw new BuildException('Task execution failed.');
        }

        $this->log(
            sprintf('git-merge: replaying "%s" commits', $this->getRemote()), 
            Project::MSG_INFO); 
        $this->log('git-merge output: ' . trim($output), Project::MSG_INFO);

    }

    public function setRemote($remote)
    {
        $this->remote = $remote;
    }

    public function getRemote()
    {
        return $this->remote;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setStrategy($strategy)
    {
        $this->strategy = $strategy;
    }

    public function getStrategy()
    {
        return $this->strategy;
    }

    public function setStrategyOption($strategyOption)
    {
        $this->strategyOption = $strategyOption;
    }

    public function getStrategyOption()
    {
        return $this->strategyOption;
    }

    public function setQuiet($flag)
    {
        $this->quiet = $flag;
    }

    public function getQuiet()
    {
        return $this->quiet;
    }

    public function setCommit($flag)
    {
        $this->commit = (boolean)$flag;
    }

    public function getCommit()
    {
        return $this->commit;
    }

    public function isCommit()
    {
        return $this->getCommit();
    }

    public function setNoCommit($flag)
    {
        $this->noCommit = (boolean)$flag;
    }

    public function getNoCommit()
    {
        return $this->noCommit;
    }

    public function isNoCommit()
    {
        return $this->getNoCommit();
    }
}
