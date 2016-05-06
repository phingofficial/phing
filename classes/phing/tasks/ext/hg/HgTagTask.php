<?php
/**
 * Utilise Mercurial from within Phing.
 *
 * PHP Version 5.4
 *
 * @category Tasks
 * @package  phing.tasks.ext
 * @author   Ken Guest <kguest@php.net>
 * @license  LGPL (see http://www.gnu.org/licenses/lgpl.html)
 * @link     https://github.com/kenguest/Phing-HG
 */

/**
 * Pull in Base class.
 */
require_once 'HgBaseTask.php';

/**
 * Pull in and use https://packagist.org/packages/siad007/versioncontrol_hg
 */
use Siad007\VersionControl\HG\Factory;

/**
 * Integration/Wrapper for hg tag
 *
 * @category Tasks
 * @package  phing.tasks.ext.hg
 * @author   Ken Guest <kguest@php.net>
 * @license  LGPL (see http://www.gnu.org/licenses/lgpl.html)
 * @link     HgTagTask.php
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
    protected $tag = '';

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
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Set message to be used.
     *
     * @param string $message Message to use
     *
     * @return void
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * Get message to apply for the commit.
     *
     * @return string
     */
    public function getMessage()
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
    public function setRevision($revision)
    {
        $this->revision = $revision;
    }

    /**
     * The main entry point method.
     *
     * @return void
     */
    public function main()
    {
        $clone = Factory::getInstance('tag');
        $cwd = getcwd();

        if ($this->repository === '') {
            $prog = $this->getProject();
            $dir = $prog->getProperty('application.startdir');
        } else {
            $dir = $this->repository;
        }
        $clone->setCwd($dir);

        if ($this->revision !== '') {
            $clone->setRev($this->revision);
        }
        $message = $this->getMessage();
        $clone->setMessage($message);
        $clone->setTag($this->getTag());

        chdir($dir);

        try {
            $this->log("Executing: " . $tag->asString(), Project::MSG_INFO);
            $output = $tag->execute();
            if ($output !== '') {
                $this->log($output);
            }
        } catch(Exception $ex) {
            $msg = $ex->getMessage();
            $p = strpos($msg, 'hg returned:');
            if ($p !== false) {
                $msg = substr($msg, $p + 13);
            }
            chdir($cwd);
            throw new BuildException($msg);
        }
        chdir($cwd);
    }
}
