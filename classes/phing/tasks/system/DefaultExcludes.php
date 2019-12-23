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
 * Alters the default excludes for the <strong>entire</strong> build.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system
 */
class DefaultExcludes extends Task
{
    /**
     * @var string $add
     */
    private $add = '';

    /**
     * @var string $remove
     */
    private $remove = '';

    /**
     * @var bool $defaultrequested
     */
    private $defaultrequested = false;

    /**
     * @var bool $echo
     */
    private $echo = false;

    /**
     * by default, messages are always displayed
     *
     * @var int
     */
    private $logLevel = Project::MSG_WARN;

    /**
     * Does the work.
     *
     * @return void
     *
     * @throws BuildException if something goes wrong with the build
     * @throws Exception
     */
    public function main(): void
    {
        if (!$this->defaultrequested && $this->add === '' && $this->remove === '' && !$this->echo) {
            throw new BuildException(
                '<defaultexcludes> task must set at least one attribute (echo="false")'
                . " doesn't count since that is the default"
            );
        }
        if ($this->defaultrequested) {
            DirectoryScanner::resetDefaultExcludes();
        }
        if ($this->add !== '') {
            DirectoryScanner::addDefaultExclude($this->add);
        }
        if ($this->remove !== '') {
            DirectoryScanner::removeDefaultExclude($this->remove);
        }
        if ($this->echo) {
            $lineSep  = Phing::getProperty('line.separator');
            $message  = 'Current Default Excludes:';
            $message .= $lineSep;
            $excludes = DirectoryScanner::getDefaultExcludes();
            $message .= '  ';
            $message .= implode($lineSep . '  ', $excludes);
            $this->log($message, $this->logLevel);
        }
    }

    /**
     * go back to standard default patterns
     *
     * @param bool $def if true go back to default patterns
     *
     * @return void
     */
    public function setDefault(bool $def): void
    {
        $this->defaultrequested = $def;
    }

    /**
     * Pattern to add to the default excludes
     *
     * @param string $add Sets the value for the pattern to exclude.
     *
     * @return void
     */
    public function setAdd(string $add): void
    {
        $this->add = $add;
    }

    /**
     * Pattern to remove from the default excludes.
     *
     * @param string $remove Sets the value for the pattern that
     *                       should no longer be excluded.
     *
     * @return void
     */
    public function setRemove(string $remove): void
    {
        $this->remove = $remove;
    }

    /**
     * If true, echo the default excludes.
     *
     * @param bool $echo Whether or not to echo the contents of
     *                   the default excludes.
     *
     * @return void
     */
    public function setEcho(bool $echo): void
    {
        $this->echo = $echo;
    }
}
