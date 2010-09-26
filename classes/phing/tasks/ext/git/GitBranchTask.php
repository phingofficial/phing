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
 * Wrapper aroung git-branch
 *
 * @author Victor Farazdagi <simple.square@gmail.com>
 * @version $Id$
 * @package phing.tasks.ext.git
 * @see VersionControl_Git
 * @since 2.4.3
 */
class GitBranchTask extends GitBaseTask
{
    /**
     * Branch name
     * @var string
     */
    private $branchname;

    /**
     * If not HEAD, specify starting point
     * @var string
     */
    private $startPoint;

    /**
     * --set-upstream key to git-branch
     * @var boolean
     */
    private $setUpstream = false;

    /**
     * --track key to git-branch
     * @var boolean
     */
    private $track = false;

    /**
     * --no-track key to git-branch
     * @var boolean
     */
    private $noTrack = false;

    /**
     * --force, -f key to git-branch
     * @var boolean
     */
    private $force = false;

    /**
     * The main entry point for the task
     */
    public function main()
    {
        if (null === $this->getRepository()) {
            throw new BuildException('"repository" is required parameter');
        }
        if (null === $this->getBranchname()) {
            throw new BuildException('"branchname" is required parameter');
        }

        $client = $this->getGitClient(false, $this->getRepository());
        $command = $client->getCommand('branch');
        $command
            ->setOption('set-upstream', $this->isSetUpstream())
            ->setOption('no-track', $this->isNoTrack())
            ->setOption('force', $this->isForce());
        if ($this->isNoTrack() == false) {
            $command->setOption('track', $this->getTrack());
        }

        $command->addArgument($this->getBranchname());

        if (null !== $this->getStartPoint()) {
            $command->addArgument($this->getStartPoint());
        }

        //echo $command->createCommandString();
        //exit;

        try {
            $output = $command->execute();
        } catch (Exception $e) {
            throw new BuildException('Task execution failed');
        }

        $this->log(
            sprintf('git-branch: branch "%s" repository', $this->getRepository()), 
            Project::MSG_INFO); 
        $this->log('git-branch output: ' . trim($output), Project::MSG_INFO);
    }

    public function setSetUpstream($flag)
    {
        $this->setUpstream = $flag;
    }

    public function getSetUpstream()
    {
        return $this->setUpstream;
    }

    public function isSetUpstream()
    {
        return $this->getSetUpstream();
    }

    public function setTrack($flag)
    {
        $this->track = $flag;
    }

    public function getTrack()
    {
        return $this->track;
    }

    public function isTrack()
    {
        return $this->getTrack();
    }

    public function setNoTrack($flag)
    {
        $this->noTrack = $flag;
    }

    public function getNoTrack()
    {
        return $this->noTrack;
    }

    public function isNoTrack()
    {
        return $this->getNoTrack();
    }

    public function setForce($flag)
    {
        $this->force = $flag;
    }

    public function getForce()
    {
        return $this->force;
    }

    public function isForce()
    {
        return $this->getForce();
    }

    public function setBranchname($branchname)
    {
        $this->branchname = $branchname;
    }

    public function getBranchname()
    {
        return $this->branchname;
    }

    public function setStartPoint($startPoint)
    {
        $this->startPoint = $startPoint;
    }

    public function getStartPoint()
    {
        return $this->startPoint;
    }

}
