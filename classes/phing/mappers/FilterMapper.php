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

require_once 'phing/mappers/FileNameMapper.php';
require_once 'phing/types/FilterChain.php';

/**
 *  This is a FileNameMapper based on a FilterChain.
 *
 * @author   Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package  phing.mappers
 */
class FilterMapper extends FilterChain implements FileNameMapper
{
    /**
     * From attribute not supported.
     * @param string $from a string
     * @throws BuildException always
     */
    public function setFrom($from)
    {
        throw new BuildException(
            "filtermapper doesn't support the \"from\" attribute."
        );
    }

    /**
     * From attribute not supported.
     * @param string $to a string
     * @throws BuildException always
     */
    public function setTo($to)
    {
        throw new BuildException(
            "filtermapper doesn't support the \"to\" attribute."
        );
    }

    /**
     * Return the result of the filters on the sourcefilename.
     *
     * @param string $sourceFileName the filename to map
     * @return array a one-element array of converted filenames, or null if
     *          the filterchain returns an empty string.
     *
     * @throws BuildException
     */
    public function main($sourceFileName)
    {
        try {
            $stringReader = new StringReader($sourceFileName);

            $filterChains = [$this];

            $chainedReader = FileUtils::getChainedReader(
                $stringReader,
                $filterChains,
                $this->getProject()
            );

            $result = $chainedReader->read();

            return $result !== '' ? [$result] : null;
        } catch (BuildException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            throw new BuildException($ex);
        }
    }
}
