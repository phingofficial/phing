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
 * Abstract Liquibase task. Base class for all Liquibase Phing tasks.
 *
 * @author  Stephan Hochdoerfer <S.Hochdoerfer@bitExpert.de>
 * @since   2.4.10
 * @package phing.tasks.ext.liquibase
 */
abstract class AbstractLiquibaseTask extends Task
{
    /**
     * Used for liquibase -Dname=value properties.
     */
    private $properties = [];

    /**
     * Used to set liquibase --name=value parameters
     */
    private $parameters = [];

    /**
     * @var string
     */
    protected $jar;

    /**
     * @var string
     */
    protected $changeLogFile;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $classpathref;

    /**
     * Whether to display the output of the command.
     * True by default to preserve old behaviour
     *
     * @var bool
     */
    protected $display = true;

    /**
     * Whether liquibase return code can cause a Phing failure.
     *
     * @var bool
     */
    protected $checkreturn = false;

    /**
     * Set true if we should run liquibase with PHP passthru
     * instead of exec.
     *
     * @var bool
     */
    protected $passthru = true;

    /**
     * Property name to set with output value from exec call.
     *
     * @var string
     */
    protected $outputProperty;

    /**
     * Sets the absolute path to liquibase jar.
     *
     * @param string $jar the absolute path to the liquibase jar.
     *
     * @return void
     */
    public function setJar(string $jar): void
    {
        $this->jar = $jar;
    }

    /**
     * Sets the absolute path to the changelog file to use.
     *
     * @param string $changelogFile the absolute path to the changelog file
     *
     * @return void
     */
    public function setChangeLogFile(string $changelogFile): void
    {
        $this->changeLogFile = $changelogFile;
    }

    /**
     * Sets the username to connect to the database.
     *
     * @param string $username the username
     *
     * @return void
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * Sets the password to connect to the database.
     *
     * @param string $password the password
     *
     * @return void
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * Sets the url to connect to the database in jdbc style, e.g.
     * <code>
     * jdbc:postgresql://psqlhost/mydatabase
     * </code>
     *
     * @param string $url jdbc connection string
     *
     * @return void
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * Sets the Java classpathref.
     *
     * @param string $classpathref A reference to the classpath that contains the database
     *                             driver, liquibase.jar, and the changelog.xml file
     *
     * @return void
     */
    public function setclasspathref(string $classpathref): void
    {
        $this->classpathref = $classpathref;
    }

    /**
     * Sets whether to display the output of the command
     *
     * @param bool $display
     *
     * @return void
     */
    public function setDisplay(bool $display): void
    {
        $this->display = StringHelper::booleanValue($display);
    }

    /**
     * Whether to check the liquibase return code.
     *
     * @param bool $checkreturn
     *
     * @return void
     */
    public function setCheckreturn(bool $checkreturn): void
    {
        $this->checkreturn = StringHelper::booleanValue($checkreturn);
    }

    /**
     * Whether to check the liquibase return code.
     *
     * @param bool $passthru
     *
     * @return void
     */
    public function setPassthru(bool $passthru): void
    {
        $this->passthru = StringHelper::booleanValue($passthru);
    }

    /**
     * the name of property to set to output value from exec() call.
     *
     * @param string $prop property name
     *
     * @return void
     */
    public function setOutputProperty(string $prop): void
    {
        $this->outputProperty = $prop;
    }

    /**
     * Creates a nested <property> tag.
     *
     * @return LiquibaseProperty Argument object
     */
    public function createProperty(): LiquibaseProperty
    {
        $prop               = new LiquibaseProperty();
        $this->properties[] = $prop;

        return $prop;
    }

    /**
     * Creates a nested <parameter> tag.
     *
     * @return LiquibaseParameter Argument object
     */
    public function createParameter(): LiquibaseParameter
    {
        $param              = new LiquibaseParameter();
        $this->parameters[] = $param;

        return $param;
    }

    /**
     * Ensure that correct parameters were passed in.
     *
     * @return void
     *
     * @throws BuildException
     */
    protected function checkParams(): void
    {
        if ((null === $this->jar) || !file_exists($this->jar)) {
            throw new BuildException(
                sprintf(
                    'Specify the name of the LiquiBase.jar. "%s" does not exist!',
                    $this->jar
                )
            );
        }

        $this->checkChangeLogFile();

        if (null === $this->classpathref) {
            throw new BuildException('Please provide a classpath!');
        }

        if (null === $this->username) {
            throw new BuildException('Please provide a username for database acccess!');
        }

        if (null === $this->password) {
            throw new BuildException('Please provide a password for database acccess!');
        }

        if (null === $this->url) {
            throw new BuildException('Please provide a url for database acccess!');
        }
    }

    /**
     * Executes the given command and returns the output.
     *
     * @param string $lbcommand
     * @param string $lbparams  the command to execute
     *
     * @return void
     *
     * @throws BuildException
     */
    protected function execute(string $lbcommand, string $lbparams = ''): void
    {
        $nestedparams = '';
        foreach ($this->parameters as $p) {
            $nestedparams .= $p->getCommandline($this->project) . ' ';
        }
        $nestedprops = '';
        foreach ($this->properties as $p) {
            $nestedprops .= $p->getCommandline($this->project) . ' ';
        }

        $command = sprintf(
            'java -jar %s --changeLogFile=%s --url=%s --username=%s --password=%s --classpath=%s %s %s %s %s 2>&1',
            escapeshellarg($this->jar),
            escapeshellarg($this->changeLogFile),
            escapeshellarg($this->url),
            escapeshellarg($this->username),
            escapeshellarg($this->password),
            escapeshellarg($this->classpathref),
            $nestedparams,
            escapeshellarg($lbcommand),
            $lbparams,
            $nestedprops
        );

        if ($this->passthru) {
            passthru($command);
        } else {
            $output = [];
            $return = null;
            exec($command, $output, $return);
            $output = implode(PHP_EOL, $output);

            if ($this->display) {
                print $output;
            }

            if (!empty($this->outputProperty)) {
                $this->project->setProperty($this->outputProperty, $output);
            }

            if ($this->checkreturn && $return != 0) {
                throw new BuildException('Liquibase exited with code ' . $return);
            }
        }
    }

    /**
     * @return void
     */
    protected function checkChangeLogFile(): void
    {
        if (null === $this->changeLogFile) {
            throw new BuildException('Specify the name of the changelog file.');
        }

        foreach (explode(':', $this->classpathref) as $path) {
            if (file_exists($path . DIRECTORY_SEPARATOR . $this->changeLogFile)) {
                return;
            }
        }

        if (!file_exists($this->changeLogFile)) {
            throw new BuildException(
                sprintf(
                    'The changelog file "%s" does not exist!',
                    $this->changeLogFile
                )
            );
        }
    }
}
