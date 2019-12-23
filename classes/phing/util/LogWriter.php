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
 * Extends the Writer class to output messages to Phing's log
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.util
 */
class LogWriter extends Writer
{
    /**
     * @var Task|null
     */
    private $task = null;

    /**
     * @var int|null
     */
    private $level = null;

    /**
     * Constructs a new LogWriter object
     *
     * @param Task $task
     * @param int  $level
     */
    public function __construct(Task $task, int $level = Project::MSG_INFO)
    {
        $this->task  = $task;
        $this->level = $level;
    }

    /**
     * @see Writer::write()
     *
     * @param string   $buf
     * @param int|null $off
     * @param int|null $len
     *
     * @return void
     *
     * @throws Exception
     */
    public function write(string $buf, ?int $off = null, ?int $len = null): void
    {
        $lines = explode("\n", $buf);

        foreach ($lines as $line) {
            if ($line == '') {
                continue;
            }

            $this->task->log($line, $this->level);
        }
    }

    /**
     * @see Writer::reset()
     *
     * @return void
     */
    public function reset(): void
    {
    }

    /**
     * @see Writer::close()
     *
     * @return void
     */
    public function close(): void
    {
    }

    /**
     * @see Writer::open()
     *
     * @return void
     */
    public function open(): void
    {
    }

    /**
     * @see Writer::getResource()
     *
     * @return Task|null
     */
    public function getResource(): ?Task
    {
        return $this->task;
    }
}
