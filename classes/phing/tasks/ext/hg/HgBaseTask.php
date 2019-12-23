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

use Siad007\VersionControl\HG\Command\AbstractCommand;
use Siad007\VersionControl\HG\Factory;

/**
 * Base task for integrating phing and mercurial.
 *
 * @link     HgBaseTask.php
 *
 * @category Tasks
 * @package  phing.tasks.ext.hg
 * @author   Ken Guest <kguest@php.net>
 * @license  LGPL (see http://www.gnu.org/licenses/lgpl.html)
 */
abstract class HgBaseTask extends Task
{
    /**
     * Insecure argument
     *
     * @var bool
     */
    protected $insecure = false;

    /**
     * Repository directory
     *
     * @var string|null
     */
    protected $repository;

    /**
     * Whether to be quiet... --quiet argument.
     *
     * @var bool
     */
    protected $quiet = false;

    /**
     * Username.
     *
     * @var string
     */
    protected $user = '';

    /**
     * @var \Siad07\VersionControl\HG\Factory|null
     */
    public static $factory = null;

    /**
     * Set repository attribute
     *
     * @param string $repository Repository
     *
     * @return void
     */
    public function setRepository(string $repository): void
    {
        $this->repository = $repository;
    }

    /**
     * Set the quiet attribute --quiet
     *
     * @param string $quiet yes|no|true|false|1|0
     *
     * @return void
     */
    public function setQuiet(string $quiet): void
    {
        $this->quiet = StringHelper::booleanValue($quiet);
    }

    /**
     * Get the quiet attribute value.
     *
     * @return bool
     */
    public function getQuiet(): bool
    {
        return $this->quiet;
    }

    /**
     * Get Repository attribute/directory.
     *
     * @return string|null
     */
    public function getRepository(): ?string
    {
        return $this->repository;
    }

    /**
     * Set insecure attribute
     *
     * @param string $insecure 'yes', etc.
     *
     * @return void
     */
    public function setInsecure(string $insecure): void
    {
        $this->insecure = StringHelper::booleanValue($insecure);
    }

    /**
     * Get 'insecure' attribute value. (--insecure or null)
     *
     * @return bool
     */
    public function getInsecure(): bool
    {
        return $this->insecure;
    }

    /**
     * Set user attribute
     *
     * @param string $user username/email address.
     *
     * @return void
     */
    public function setUser(string $user): void
    {
        $this->user = $user;
    }

    /**
     * Get username attribute.
     *
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * Check provided repository directory actually is an existing directory.
     *
     * @param string $dir Repository directory
     *
     * @return bool
     *
     * @throws BuildException
     */
    public function checkRepositoryIsDirAndExists(string $dir): bool
    {
        if (!file_exists($dir)) {
            throw new BuildException(sprintf("Repository directory '%s' does not exist.", $dir));
        }

        if (!is_dir($dir)) {
            throw new BuildException(sprintf("Repository '%s' is not a directory.", $dir));
        }
        return true;
    }

    /**
     * Initialise the task.
     *
     * @return void
     */
    public function init(): void
    {
        @include_once 'vendor/autoload.php';
    }

    /**
     * @param mixed $command
     * @param array $options
     *
     * @return AbstractCommand
     */
    public function getFactoryInstance($command, $options = []): AbstractCommand
    {
        self::$factory = Factory::getInstance($command, $options);
        return self::$factory;
    }
}
