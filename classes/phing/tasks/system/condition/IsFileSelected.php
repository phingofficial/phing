<?php
/**
 *  $Id$
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
require_once 'phing/ProjectComponent.php';
require_once 'phing/tasks/system/condition/Condition.php';
require_once 'phing/types/selectors/AbstractSelectorContainer.php';

/**
 * This is a condition that checks to see if a file passes an embedded selector.
 */
class IsFileSelected extends AbstractSelectorContainer implements Condition
{
    /** @var PhingFile $file */
    private $file;
    private $baseDir;

    /**
     * The file to check.
     * @param file the file to check if if passes the embedded selector.
     */
    public function setFile(PhingFile $file)
    {
        $this->file = $file;
    }

    /**
     * The base directory to use.
     * @param baseDir the base directory to use, if null use the project's
     *                basedir.
     */
    public function setBaseDir(PhingFile $baseDir)
    {
        $this->baseDir = $baseDir;
    }

    /**
     * validate the parameters.
     */
    public function validate()
    {
        if ($this->selectorCount() != 1) {
            throw new BuildException("Only one selector allowed");
        }
        parent::validate();
    }

    /**
     * Evaluate the selector with the file.
     * @return true if the file is selected by the embedded selector.
     */
    public function evaluate()
    {
        if ($this->file === null) {
            throw new BuildException('file attribute not set');
        }
        $this->validate();
        $myBaseDir = $this->baseDir;
        if ($myBaseDir === null) {
            $myBaseDir = $this->getProject()->getBaseDir();
        }

        /** @var FileSelector $f */
        $file = $this->getSelectors($this->getProject());
        $f = $file[0];
        return $f->isSelected($myBaseDir, $this->file->getName(), $this->file);
    }
}
