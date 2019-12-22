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

class FileSystemWhichStub extends UnixFileSystem
{

    private $isWhichSuccessful;

    public function __construct(bool $isWhichSuccessful)
    {
        $this->isWhichSuccessful = $isWhichSuccessful;
    }

    public function which($executable, $fallback = false)
    {
        if ($this->isWhichSuccessful) {
            return $executable;
        }
        return $fallback;
    }

    /**
     * Compare two abstract pathnames lexicographically.
     *
     * @param PhingFile $f1
     * @param PhingFile $f2
     *
     * @throws IOException
     */
    public function compare(PhingFile $f1, PhingFile $f2)
    {
        throw new IOException('compare() not implemented by local fs driver');
    }
}
