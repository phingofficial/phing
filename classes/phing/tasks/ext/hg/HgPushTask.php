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

declare(strict_types=1);

/**
 * Integration/Wrapper for hg push
 *
 * @link     HgPushTask.php
 *
 * @category Tasks
 * @package  phing.tasks.ext.hg
 * @author   Ken Guest <kguest@php.net>
 * @license  LGPL (see http://www.gnu.org/licenses/lgpl.html)
 */
class HgPushTask extends HgBaseTask
{
    /**
     * Whether the task should halt if an error occurs.
     *
     * @var bool
     */
    protected $haltonerror = false;

    /**
     * Set haltonerror attribute.
     *
     * @param string $halt 'yes', or '1' to halt.
     *
     * @return void
     */
    public function setHaltonerror(string $halt): void
    {
        $this->haltonerror = StringHelper::booleanValue($halt);
    }

    /**
     * Return haltonerror value.
     *
     * @return bool
     */
    public function getHaltonerror(): bool
    {
        return $this->haltonerror;
    }

    /**
     * The main entry point method.
     *
     * @return void
     *
     * @throws Exception
     * @throws BuildException
     */
    public function main(): void
    {
        $clone = $this->getFactoryInstance('push');
        $this->log('Pushing...', Project::MSG_INFO);
        $clone->setInsecure($this->getInsecure());
        $clone->setQuiet($this->getQuiet());
        if ($this->repository === '') {
            $project = $this->getProject();
            $dir     = $project->getProperty('application.startdir');
        } else {
            $dir = $this->repository;
        }
        $cwd = getcwd();
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
            if ($this->haltonerror) {
                throw new BuildException($msg);
            }
            $this->log($msg, Project::MSG_ERR);
        }
        chdir($cwd);
    }
}
