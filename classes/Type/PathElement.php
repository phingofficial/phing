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

/**
 * Helper class, holds the nested <code>&lt;pathelement&gt;</code> values.
 *
 * @package phing.types
 */
class PathElement
{
    /**
     * @var array $parts
     */
    private $parts = [];

    /**
     * @var Path $outer
     */
    private $outer;

    /**
     * @param Path $outer
     */
    public function __construct(Path $outer)
    {
        $this->outer = $outer;
    }

    /**
     * @param PhingFile $loc
     *
     * @return void
     */
    public function setDir(PhingFile $loc)
    {
        $this->parts = [Path::translateFile($loc->getAbsolutePath())];
    }

    /**
     * @param $path
     *
     * @return void
     */
    public function setPath($path)
    {
        $this->parts = Path::translatePath($this->outer->getProject(), $path);
    }

    /**
     * @return array
     */
    public function getParts()
    {
        return $this->parts;
    }
}
