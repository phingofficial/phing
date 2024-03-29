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

namespace Phing\Task\Ext\Svn;

use Phing\Exception\BuildException;

/**
 * Updates a repository in local directory
 *
 * @author  Andrew Eddie <andrew.eddie@jamboworks.com>
 * @package phing.tasks.ext.svn
 * @since   2.3.0
 */
class SvnUpdateTask extends SvnBaseTask
{
    /**
     * Which Revision to Export
     *
     * @todo check if version_control_svn supports constants
     *
     * @var string
     */
    private $revision = 'HEAD';

    /**
     * The main entry point
     *
     * @throws BuildException
     */
    public function main()
    {
        $this->setup('update');

        $this->log(
            "Updating SVN repository at '" . $this->getToDir() . "'" . ($this->revision === 'HEAD' ? '' : " (revision: {$this->revision})")
        );

        // revision
        $switches = [
            'r' => $this->revision,
        ];

        $this->run([$this->getToDir()], $switches);
    }

    /**
     * @param $revision
     */
    public function setRevision($revision)
    {
        $this->revision = $revision;
    }
}
