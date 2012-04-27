<?php
/**
 * $Id$
 *
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

require_once 'phing/Task.php';
require_once 'phing/tasks/ext/svn/SvnBaseTask.php';

/**
 * Stores the output of a list command on a workingcopy or repositoryurl in a property.
 * This stems from the SvnLastRevisionTask.
 *
 * @author Anton Stöckl <anton@stoeckl.de>
 * @author Michiel Rook <mrook@php.net> (SvnLastRevisionTask)
 * @version $Id$
 * @package phing.tasks.ext.svn
 * @see VersionControl_SVN
 * @since 2.1.0
 */
class SvnListTask extends SvnBaseTask
{
    private $propertyName = "svn.list";
    private $forceCompatible = true;
    private $limit = null;
    private $orderDescending = false;

    /**
     * Sets the name of the property to use
     */
    function setPropertyName($propertyName)
    {
        $this->propertyName = $propertyName;
    }

    /**
     * Returns the name of the property to use
     */
    function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * Sets whether to force compatibility with older SVN versions (< 1.2)
     */
    public function setForceCompatible($force)
    {
        //$this->forceCompatible = (bool) $force;
        // see below, we need this to be true as xml mode does not work
    }

    /**
     * Sets the max num of tags to display
     */
    function setLimit($limit)
    {
        $this->limit = (int) $limit;
    }

    /**
     * Sets whether to sort tags in descending order
     */
    function setOrderDescending($orderDescending)
    {
        $this->orderDescending = (bool) $orderDescending;
    }

    /**
     * The main entry point
     *
     * @throws BuildException
     */
    function main()
    {
        $this->setup('list');

        if ($this->forceCompatible) {
            $output = $this->run(array('--verbose'));
            $result = null;

            $lines = $output['.']['name'];

            if ($this->orderDescending) {
                $lines = array_reverse($lines);
            }

            $count = 0;
            $dotSkipped = false;
            foreach ($lines as $line) {
                if ($this->limit > 0 && $count >= $this->limit) {
                    break;
                }
                if (preg_match('@\s+(\d+)\s+(\S+)\s+(\S+ \S+ \S+)\s+(\S+)@', $line, $matches)) {
                    if ($matches[4] == '.') {
                        $dotSkipped = true;
                        continue;
                    }
                    $result .= (!empty($result)) ? "\n" : '';
                    $result .= $matches[1] . ' | ' . $matches[2] . ' | ' . $matches[3] . ' | ' . $matches[4];
                    $count++;
                }
            }

            if (!empty($result)) {
                $this->project->setProperty($this->getPropertyName(), $result);
            } elseif ($dotSkipped) {
                $this->project->setProperty($this->getPropertyName(), "The list is empty.");
            } else {
                throw new BuildException("Failed to parse the output of 'svn list --verbose'.");
            }
        } else {
            // this is not possible at the moment as SvnBaseTask always uses fetchmode ASSOC
            // which transfers everything into nasty assoc array instead of xml
        }
    }
}
