<?php
/**
 * $Id$
 *
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

require_once 'PHPCPD/TextUI/ResultPrinter.php';
require_once 'phing/tasks/ext/phpcpd/formatter/PHPCPDResultFormatter.php';

/**
 * Prints plain text output of phpcpd run
 *
 * @package phing.tasks.ext.phpcpd.formatter
 * @author  Benjamin Schultz <bschultz@proqrent.de>
 * @version $Id$
 */
class DefaultPHPCPDResultFormatter extends PHPCPDResultFormatter
{

    /**
     * Processes a list of clones.
     *
     * @param PHPCPD_CloneMap $clones
     */
    public function processClones(PHPCPD_CloneMap $clones, PhingFile $outfile, Project $project)
    {
        $logger = new PHPCPD_TextUI_ResultPrinter();
        // default format goes to logs, no buffering
        ob_start();
        $logger->printResult($clones, $project->getBaseDir());
        $output = ob_get_contents();
        ob_end_clean();

        if (!$this->usefile) {
            echo $output;
        } else {
            $outputFile = $outfile->getPath();
            file_put_contents($outputFile, $output);
        }
    }
}