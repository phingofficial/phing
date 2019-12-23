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
 * Abstract class for TokenReaders.
 *
 * @author Manuel Holtgewe
 * @package phing.filters.util
 */
abstract class TokenReader
{
    /**
     * Reference to the Project the TokenReader is used in.
     *
     * @var Project
     */
    protected $project;

    /**
     * @param Project $project Reference to the project the TokenReader is used in.
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * Utility function for logging.
     *
     * @param string $level
     * @param int    $msg
     *
     * @return void
     */
    public function log(string $level, int $msg): void
    {
        $this->project->log($level, $msg);
    }

    /**
     * Reads the next token from the Reader.
     *
     * @return Token|null
     *
     * @throws IOException On error
     */
    abstract public function readToken(): ?Token;
}
