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

use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Project;
use SebastianBergmann\PHPCPD\CodeCloneMap;
use SebastianBergmann\PHPCPD\Log\PMD;

/**
 * Prints PMD-XML output of phpcpd run
 *
 * @author Benjamin Schultz <bschultz@proqrent.de>
 *
 * @package phing.tasks.ext.phpcpd.formatter
 */
class PMDPHPCPDResultFormatter extends PHPCPDResultFormatter
{
    /**
     * Processes a list of clones.
     *
     * @param CodeCloneMap $clones
     * @param Project $project
     * @param boolean $useFile
     * @param File|null $outFile
     *
     * @throws BuildException
     */
    public function processClones($clones, Project $project, $useFile = false, $outFile = null)
    {
        if (!$useFile || empty($outFile)) {
            throw new BuildException('Output filename required for this formatter');
        }

        $logger = new PMD($outFile);

        $logger->processClones($clones);
    }
}
