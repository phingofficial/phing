<?php
/**
 * Utilise Mercurial from within Phing.
 *
 * PHP Version 5.4
 *
 * @link     https://github.com/kenguest/Phing-HG
 *
 * @category Tasks
 * @package  phing.tasks.ext
 * @author   Ken Guest <kguest@php.net>
 * @license  LGPL (see http://www.gnu.org/licenses/lgpl.html)
 */

/**
 * Integration/Wrapper for hg update
 *
 * @link     HgPullTask.php
 *
 * @category Tasks
 * @package  phing.tasks.ext.hg
 * @author   Ken Guest <kguest@php.net>
 * @license  LGPL (see http://www.gnu.org/licenses/lgpl.html)
 */
class HgPullTask extends HgBaseTask
{
    /**
     * Path to target directory
     *
     * @var string
     */
    protected $targetPath;

    /**
     * Set path to source repo
     *
     * @param string $targetPath Path to repository used as source
     *
     * @return void
     */
    public function setTargetPath($targetPath)
    {
        $this->targetPath = $targetPath;
    }

    /**
     * The main entry point method.
     *
     * @return void
     *
     * @throws BuildException
     */
    public function main()
    {
        $clone = $this->getFactoryInstance('pull');
        $clone->setInsecure($this->getInsecure());
        $clone->setQuiet($this->getQuiet());

        $cwd = getcwd();

        if ($this->repository === '') {
            $project = $this->getProject();
            $dir     = $project->getProperty('application.startdir');
        } else {
            $dir = $this->repository;
        }
        $this->checkRepositoryIsDirAndExists($dir);
        chdir($dir);

        try {
            $this->log('Executing: ' . $clone->asString(), Project::MSG_INFO);
            $output = $clone->execute();
            if ($output !== '') {
                $this->log($output);
            }
        } catch (Throwable $ex) {
            $msg = $ex->getMessage();
            $p   = strpos($msg, 'hg returned:');
            if ($p !== false) {
                $msg = substr($msg, $p + 13);
            }
            chdir($cwd);
            throw new BuildException($msg);
        }
        chdir($cwd);
    }
}
