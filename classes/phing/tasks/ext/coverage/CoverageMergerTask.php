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
 * Merges code coverage snippets into a code coverage database
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.tasks.ext.coverage
 * @since   2.1.0
 */
class CoverageMergerTask extends Task
{
    use FileSetAware;

    /**
     * Iterate over all filesets and return all the filenames.
     *
     * @return array an array of filenames
     *
     * @throws IOException
     * @throws NullPointerException
     * @throws ReflectionException
     */
    private function getFilenames(): array
    {
        $files = [];

        foreach ($this->filesets as $fileset) {
            $ds = $fileset->getDirectoryScanner($this->project);
            $ds->scan();

            $includedFiles = $ds->getIncludedFiles();

            foreach ($includedFiles as $file) {
                $fs = new PhingFile(basename($ds->getBaseDir()), $file);

                $files[] = $fs->getAbsolutePath();
            }
        }

        return $files;
    }

    /**
     * @return void
     *
     * @throws NullPointerException
     * @throws ReflectionException
     * @throws Exception
     * @throws IOException
     */
    public function main(): void
    {
        $files = $this->getFilenames();

        $this->log('Merging ' . count($files) . ' coverage files');

        foreach ($files as $file) {
            $coverageInformation = unserialize(file_get_contents($file));

            CoverageMerger::merge($this->project, [$coverageInformation]);
        }
    }
}
