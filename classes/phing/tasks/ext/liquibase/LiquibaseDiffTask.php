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
 * Task to create the diff between two databases. Will output the changes needed
 * to convert the reference database to the database.
 *
 * @author  Stephan Hochdoerfer <S.Hochdoerfer@bitExpert.de>
 * @since   2.4.10
 * @package phing.tasks.ext.liquibase
 */
class LiquibaseDiffTask extends AbstractLiquibaseTask
{
    /**
     * @var string
     */
    protected $referenceUsername;

    /**
     * @var string
     */
    protected $referencePassword;

    /**
     * @var string
     */
    protected $referenceUrl;

    /**
     * Sets the username to connect to the reference database.
     *
     * @param string $username the username
     *
     * @return void
     */
    public function setReferenceUsername(string $username): void
    {
        $this->referenceUsername = $username;
    }

    /**
     * Sets the password to connect to the reference database.
     *
     * @param string $password the password
     *
     * @return void
     */
    public function setReferencePassword(string $password): void
    {
        $this->referencePassword = $password;
    }

    /**
     * Sets the url to connect to the reference database in jdbc style, e.g.
     * <code>
     * jdbc:postgresql://psqlhost/myrefdatabase
     * </code>
     *
     * @param string $url jdbc connection string
     *
     * @return void
     */
    public function setReferenceUrl(string $url): void
    {
        $this->referenceUrl = $url;
    }

    /**
     * @see AbstractTask::checkParams()
     *
     * @return void
     */
    protected function checkParams(): void
    {
        parent::checkParams();

        if (null === $this->referenceUsername) {
            throw new BuildException('Please provide a username for the reference database acccess!');
        }

        if (null === $this->referencePassword) {
            throw new BuildException('Please provide a password for the reference database acccess!');
        }

        if (null === $this->referenceUrl) {
            throw new BuildException('Please provide a url for the reference database acccess!');
        }
    }

    /**
     * @see Task::main()
     *
     * @return void
     */
    public function main(): void
    {
        $this->checkParams();

        $refparams = sprintf(
            '--referenceUsername=%s --referencePassword=%s --referenceUrl=%s',
            escapeshellarg($this->referenceUsername),
            escapeshellarg($this->referencePassword),
            escapeshellarg($this->referenceUrl)
        );

        // save main changelog file
        $changelogFile = $this->changeLogFile;

        // set the name of the new generated changelog file
        $this->setChangeLogFile(dirname($changelogFile) . '/diffs/' . date('YmdHis') . '.xml');
        if (!is_dir(dirname($changelogFile) . '/diffs/')) {
            mkdir(dirname($changelogFile) . '/diffs/', 0777, true);
        }
        $this->execute('diffChangeLog', $refparams);

        $xmlFile = new DOMDocument();
        $xmlFile->load($changelogFile);

        // create the new node
        $rootNode    = $xmlFile->getElementsByTagName('databaseChangeLog')->item(0);
        $includeNode = $rootNode->appendChild($xmlFile->createElement('include'));

        // set the attributes for the new node
        $includeNode->setAttribute('file', str_replace(dirname($changelogFile) . '/', '', $this->changeLogFile));
        $includeNode->setAttribute('relativeToChangelogFile', 'true');
        file_put_contents($changelogFile, $xmlFile->saveXML());

        $this->setChangeLogFile($changelogFile);
        $this->execute('markNextChangeSetRan');
    }
}
