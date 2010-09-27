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
 * Wrapper aroung git-fetch
 *
 * @author Victor Farazdagi <simple.square@gmail.com>
 * @version $Id$
 * @package phing.tasks.ext.git
 * @see VersionControl_Git
 * @since 2.4.3
 */
class GitFetchTask extends GitBaseTask
{
    /**
     * --force, -f key to git-fetch
     * @var boolean
     */
    private $force = false;

    /**
     * Fetch all remotes
     * --all key to git-fetch
     * @var boolean
     */
    private $allRemotes = false;

    /**
     * Keep downloaded pack
     * --keep key to git-fetch
     * @var boolean
     */
    private $keepFiles = false;

    /**
     * After fetching, remove any remote tracking branches which no longer 
     * exist on the remote. 
     * --prune key to git fetch
     * @var boolean
     */
    private $prune = false;

    /**
     * Disable/enable automatic tag following
     * --no-tags key to git-fetch
     * @var boolean
     */
    private $noTags = false;

    /**
     * Fetch all tags (even not reachable from branch heads)
     * --tags key to git-fetch
     * @var boolean
     */
    private $tags = false;

    /**
     * <group> argument to git-fetch
     * @var string
     */
    private $group;

    /**
     * <refspec> argument to git-fetch
     * @var string
     */
    private $refspec;

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

        // if we are moving branch, we need to know new name
        if ($this->isMove() || $this->isForceMove()) {
            if (null === $this->getNewbranch()) {
                throw new BuildException('"newbranch" is required parameter');
            }
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

        if (null !== $this->getNewbranch()) {
            $command->addArgument($this->getNewbranch());
        }


        // I asked Ebihara to make this method public - will see
        //echo $command->createCommandString();

        try {
            $output = $command->execute();
        } catch (Exception $e) {
            throw new BuildException('Task execution failed.');
        }

        $this->log(
            sprintf('git-branch: branch "%s" repository', $this->getRepository()), 
            Project::MSG_INFO); 
        $this->log('git-branch output: ' . trim($output), Project::MSG_INFO);
    }

    public function setForce($flag)
    {
        $this->force = $flag;
    }

    public function getForce($flag)
    {
        return $this->force;
    }

    public function isForce()
    {
        return $this->getForce();
    }

    public function setAll($flag)
    {
        $this->allRemotes = $flag;
    }

    public function getAll()
    {
        return $this->allRemotes;
    }

    public function isAllRemotes()
    {
        return $this->getAll();
    }

    public function setKeep($flag)
    {
        $this->keepFiles = $flag;
    }

    public function getKeep()
    {
        return $this->keepFiles;
    }

    public function isKeepFiles()
    {
        return $this->getKeep();
    }

    public function setPrune($flag)
    {
        $this->prune = $flag;
    }

    public function getPrune()
    {
        return $this->prune;
    }

    public function isPrune()
    {
        return $this->getPrune();
    }
    
    public function setNoTags($flag)
    {
        $this->noTags = $flag;
    }

    public function getNoTags()
    {
        return $this->noTags;
    }

    public function isNoTags()
    {
        return $this->getNoTags();
    }

    public function setTags($flag)
    {
        $this->tags = $flag;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function isTags()
    {
        return $this->getTags();
    }

    public function setRefspec($spec)
    {
        $this->refspec = $spec;
    }

    public function getRefspec()
    {
        return $this->refspec;
    }

    public function setGroup($group)
    {
        $this->group = $group;
    }

    public function getGroup()
    {
        return $this->group;
    }

}
