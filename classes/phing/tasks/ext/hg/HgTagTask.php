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
 * Integration/Wrapper for hg tag
 *
 * @link     HgTagTask.php
 *
 * @category Tasks
 * @package  phing.tasks.ext.hg
 * @author   Ken Guest <kguest@php.net>
 * @license  LGPL (see http://www.gnu.org/licenses/lgpl.html)
 */
class HgTagTask extends HgBaseTask
{
    /**
     * Message to be recorded against tagging.
     *
     * @var string
     */
    protected $message = '';

    /**
     * Tag to assign/create.
     *
     * @var string
     */
    protected $name = '';

    /**
     * Revision
     *
     * @var string
     */
    protected $revision = '';

    /**
     * Set the name argument
     *
     * @param string $name Name
     *
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Get the name of the tag to be used.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set message to be used.
     *
     * @param string $message Message to use
     *
     * @return void
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * Get message to apply for the commit.
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Set revision attribute
     *
     * @param string $revision Revision
     *
     * @return void
     */
    public function setRevision(string $revision): void
    {
        $this->revision = $revision;
    }

    /**
     * The main entry point method.
     *
     * @return void
     */
    public function main(): void
    {
        $clone = $this->getFactoryInstance('tag');
        $cwd   = getcwd();

        if ($this->name === '') {
            throw new BuildException('Tag name must be set.');
        }
        if ($this->repository === '') {
            $prog = $this->getProject();
            $dir  = $prog->getProperty('application.startdir');
        } else {
            $dir = $this->repository;
        }

        if ($this->revision !== '') {
            $clone->setRev($this->revision);
        }
        if ($this->user !== null) {
            $clone->setUser($this->user);
        }
        $message = $this->getMessage();
        $clone->setMessage($message);
        $name = $this->getName();
        if ($name == '') {
            throw new BuildException('Name attribute must be set.');
        }
        $clone->addName($name);

        $this->checkRepositoryIsDirAndExists($dir);
        chdir($dir);

        try {
            $this->log('Executing: ' . $clone, Project::MSG_INFO);
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
