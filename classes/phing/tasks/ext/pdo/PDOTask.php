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
 * Handles PDO configuration needed by SQL type tasks.
 *
 * @author  Hans Lellelid <hans@xmpl.org> (Phing)
 * @author  Nick Chalko <nick@chalko.com> (Ant)
 * @author  Jeff Martin <jeff@custommonkey.org> (Ant)
 * @author  Michael McCallum <gholam@xtra.co.nz> (Ant)
 * @author  Tim Stephenson <tim.stephenson@sybase.com> (Ant)
 * @package phing.tasks.system
 */
abstract class PDOTask extends Task
{
    /**
     * @var bool
     */
    private $caching = true;

    /**
     * Autocommit flag. Default value is false
     *
     * @var bool
     */
    private $autocommit = false;

    /**
     * DB url.
     *
     * @var string
     */
    private $url;

    /**
     * User name.
     *
     * @var string
     */
    private $userId;

    /**
     * Password
     *
     * @var string
     */
    private $password;

    /**
     * Initialize the PDOTask
     * This method checks if the PDO classes are available and triggers
     * appropriate error if they cannot be found.  This is not done in header
     * because we may want this class to be loaded w/o triggering an error.
     *
     * @return void
     *
     * @throws Exception
     */
    public function init(): void
    {
        if (!class_exists('PDO')) {
            throw new Exception('PDOTask depends on PDO feature being included in PHP.');
        }
    }

    /**
     * Caching loaders / driver. This is to avoid
     * getting an OutOfMemoryError when calling this task
     * multiple times in a row; default: true
     *
     * @param bool $enable
     *
     * @return void
     */
    public function setCaching(bool $enable): void
    {
        $this->caching = $enable;
    }

    /**
     * Sets the database connection URL; required.
     *
     * @param string $url The url to set
     *
     * @return void
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * Sets the password; required.
     *
     * @param string $password The password to set
     *
     * @return void
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * Auto commit flag for database connection;
     * optional, default false.
     *
     * @param bool $autocommit The autocommit to set
     *
     * @return void
     */
    public function setAutocommit(bool $autocommit): void
    {
        $this->autocommit = $autocommit;
    }

    /**
     * Creates a new Connection as using the driver, url, userid and password specified.
     * The calling method is responsible for closing the connection.
     *
     * @return PDO     the newly created connection.
     *
     * @throws BuildException if the UserId/Password/Url is not set or there is no suitable driver or the driver fails
     *                        to load.
     * @throws Exception
     */
    protected function getConnection()
    {
        if ($this->url === null) {
            throw new BuildException('Url attribute must be set!', $this->getLocation());
        }

        try {
            $this->log('Connecting to ' . $this->getUrl(), Project::MSG_VERBOSE);

            $user = null;
            $pass = null;

            if ($this->userId) {
                $user = $this->getUserId();
            }

            if ($this->password) {
                $pass = $this->getPassword();
            }

            $conn = new PDO($this->getUrl(), $user, $pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            try {
                $conn->setAttribute(PDO::ATTR_AUTOCOMMIT, $this->autocommit);
            } catch (PDOException $pe) {
                $this->log(
                    'Unable to enable auto-commit for this database: ' . $pe->getMessage(),
                    Project::MSG_VERBOSE
                );
            }

            return $conn;
        } catch (PDOException $e) {
            throw new BuildException($e->getMessage(), $e, $this->getLocation());
        }
    }

    /**
     * @return bool
     */
    public function isCaching()
    {
        return $this->caching;
    }

    /**
     * Gets the autocommit.
     *
     * @return bool
     */
    public function isAutocommit(): bool
    {
        return $this->autocommit;
    }

    /**
     * Gets the url.
     *
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * Gets the userId.
     *
     * @return string|null
     */
    public function getUserId(): ?string
    {
        return $this->userId;
    }

    /**
     * Set the user name for the connection; required.
     *
     * @param string $userId
     *
     * @return void
     */
    public function setUserid(string $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * Gets the password.
     *
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }
}
