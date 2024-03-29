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

namespace Phing\Task\Ext;

use MehrAlsNix;
use Phing\Exception\BuildException;
use Phing\Task\System\SequentialTask;

/**
 * Uses the DocBlox_Parallel library to run nested Phing tasks concurrently.
 *
 * WARNING: this task is highly experimental!
 *
 * @author  Michiel Rook <mrook@php.net>
 *
 * @see     https://github.com/phpdocumentor/Parallel
 * @since   2.4.10
 */
class ParallelTask extends SequentialTask
{
    /**
     * Maximum number of threads / processes.
     *
     * @var int
     */
    private $threadCount = 2;

    /**
     * Sets the maximum number of threads / processes to use.
     *
     * @param int $threadCount
     */
    public function setThreadCount($threadCount)
    {
        $this->threadCount = $threadCount;
    }

    public function init()
    {
    }

    public function main()
    {
        if (!class_exists('MehrAlsNix\Parallel\Worker')) {
            throw new BuildException(
                'ParallelTask depends on DocBlox being installed and on include_path.',
                $this->getLocation()
            );
        }

        $mgr = new MehrAlsNix\Parallel\Manager();
        $mgr->setProcessLimit($this->threadCount);

        foreach ($this->nestedTasks as $task) {
            $worker = new MehrAlsNix\Parallel\Worker(
                [$task, 'perform'],
                [$task]
            );

            $mgr->addWorker($worker);
        }

        $mgr->execute();

        /**
         * @var MehrAlsNix\Parallel\Worker $nestedTask
         */
        foreach ($mgr as $nestedTask) {
            if ('' === $nestedTask->getError()) {
                continue;
            }

            throw new BuildException($nestedTask->getError());
        }
    }
}
