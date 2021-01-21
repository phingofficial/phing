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

namespace Phing\Filter;

use A;
use Phing\Io\FileUtils;
use Phing\Io\IOException;
use Phing\Exception\NullPointerException;
use Phing\Filter\BaseFilterReader;
use Phing\Filter\ChainableReader;
use Phing\Io\FilterReader;
use Project;
use Phing\Io\Reader;
use SplFileObject;

/**
 * Strips whitespace from [php] files using PHP stripwhitespace() method.
 *
 * @author  Hans Lellelid, hans@velum.net
 * @see     FilterReader
 * @package phing.filters
 */
class StripWhitespace extends BaseFilterReader implements ChainableReader
{
    private $processed = false;

    /**
     * Returns the  stream without Php comments and whitespace.
     *
     * @param int $len
     * @return string the resulting stream, or -1
     *             if the end of the resulting stream has been reached
     * @throws IOException
     * @throws NullPointerException
     */
    public function read($len = null)
    {
        if ($this->processed === true) {
            return -1; // EOF
        }

        // Read XML
        $php = null;
        while (($buffer = $this->in->read($len)) !== -1) {
            $php .= $buffer;
        }

        if ($php === null) { // EOF?
            return -1;
        }

        if (empty($php)) {
            $this->log("PHP file is empty!", Project::MSG_WARN);

            return ''; // return empty string, don't attempt to strip whitespace
        }

        // write buffer to a temporary file, since php_strip_whitespace() needs a filename
        $file = new SplFileObject(tempnam(FileUtils::getTempDir(), mt_rand()), 'w+');
        $file->fwrite($php);
        $name = $file->getRealPath();
        $output = trim(php_strip_whitespace($name));
        $file = null;
        unlink($name);

        $this->processed = true;

        return $output;
    }

    /**
     * Creates a new StripWhitespace using the passed in
     * Reader for instantiation.
     *
     * @param A|Reader $reader
     * @return StripWhitespace a new filter based on this configuration, but filtering
     *           the specified reader
     * @internal param A $reader Reader object providing the underlying stream.
     *               Must not be <code>null</code>.
     */
    public function chain(Reader $reader): Reader
    {
        $newFilter = new StripWhitespace($reader);
        $newFilter->setProject($this->getProject());

        return $newFilter;
    }
}
