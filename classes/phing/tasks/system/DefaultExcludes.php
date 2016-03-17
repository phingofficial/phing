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

include_once 'phing/Task.php';
include_once 'phing/util/DefaultExcludesContainer.php';
include_once 'phing/util/DirectoryScanner.php';

/**
 * Alters the default excludes for the <strong>entire</strong> build.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system
 */
class DefaultExcludes extends Task
{
    /** @var string $add */
    private $append = "";

    /** @var string $remove */
    private $remove = "";

    /** @var boolean $defaultrequested */
    private $defaultrequested = false;

    /** @var boolean $echo */
    private $echo = false;

    /**
     * by default, messages are always displayed
     * @var int
     */
    private $logLevel = Project::MSG_WARN;

    /**
     * Does the work.
     *
     * @throws BuildException if something goes wrong with the build
     */
    public function main()
    {
        if (!$this->defaultrequested && $this->append === "" && $this->remove === "" && !$this->echo) {
            throw new BuildException(
                "<defaultexcludes> task must set at least one attribute (echo=\"false\")"
                . " doesn't count since that is the default");
        }

        $excludes = new DefaultExcludesContainer();
        $excludes->clear();
        if ($this->defaultrequested) {
            $excludes->reset();
        }
        if ($this->append !== "") {
            $excludes->append($this->append);
        }
        if ($this->remove !== "") {
            $excludes->remove($this->remove);
        }
        $this->getProject()->setGlobalExcludes($excludes);
        if ($this->echo) {
            $lineSep = Phing::getProperty('line.separator');
            $message = "Current Default Excludes:";
            $message .= $lineSep . "  ";
            $message .= implode($lineSep . "  ", $this->getProject()->getGlobalExcludes()->getArrayCopy());
            $this->log($message, $this->logLevel);
        }
    }

    /**
     * go back to standard default patterns
     *
     * @param boolean $def if true go back to default patterns
     */
    public function setDefault($def)
    {
        $this->defaultrequested = $def;
    }

    /**
     * Pattern to append to the default excludes
     *
     * @param string $append Sets the value for the pattern to exclude.
     */
    public function setAppend($append)
    {
        $this->append = $append;
    }

    /**
     * Pattern to remove from the default excludes.
     *
     * @param string $remove Sets the value for the pattern that
     *                       should no longer be excluded.
     */
    public function setRemove($remove)
    {
        $this->remove = $remove;
    }

    /**
     * If true, echo the default excludes.
     *
     * @param boolean $echo whether or not to echo the contents of
     *                      the default excludes.
     */
    public function setEcho($echo)
    {
        $this->echo = $echo;
    }
}
