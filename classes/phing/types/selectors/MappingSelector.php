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

require_once 'phing/types/selectors/BaseSelector.php';

/**
 * A mapping selector is an abstract class adding mapping support to the
 * base selector
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.types.selectors
 */
abstract class MappingSelector extends BaseSelector
{
    /** @var PhingFile $targetdir */
    protected $targetdir;

    /** @var Mapper $mapperElement */
    protected $mapperElement;

    /** @var FileNameMapper $map */
    protected $map;

    /**
     * The name of the file or directory which is checked for out-of-date
     * files.
     *
     * @param PhingFile $targetdir the directory to scan looking for files.
     */
    public function setTargetdir(PhingFile $targetdir)
    {
        $this->targetdir = $targetdir;
    }

    /**
     * Defines the FileNameMapper to use (nested mapper element).
     * @return a mapper to be configured
     * @throws BuildException if more than one mapper defined
     */
    public function createMapper()
    {
        if ($this->map !== null || $this->mapperElement !== null) {
            throw new BuildException('Cannot define more than one mapper');
        }
        $this->mapperElement = new Mapper($this->getProject());
        return $this->mapperElement;
    }

    /**
     * Add a configured FileNameMapper instance.
     *
     * @param FileNameMapper $fileNameMapper the FileNameMapper to add
     *
     * @throws BuildException if more than one mapper defined
     */
    public function addConfigured(FileNameMapper $fileNameMapper)
    {
        if ($this->map !== null || $this->mapperElement !== null) {
            throw new BuildException('Cannot define more than one mapper');
        }
        $this->map = $fileNameMapper;
    }

    /**
     * Checks to make sure all settings are kosher. In this case, it
     * means that the dest attribute has been set and we have a mapper.
     */
    public function verifySettings()
    {
        if ($this->targetdir === null) {
            $this->setError("The targetdir attribute is required.");
        }
        if ($this->map === null) {
            if ($this->mapperElement === null) {
                $this->map = new IdentityMapper();
            } else {
                $this->map = $this->mapperElement->getImplementation();
                if ($this->map === null) {
                    $this->setError("Could not set <mapper> element.");
                }
            }
        }
    }

    /**
     * The heart of the matter. This is where the selector gets to decide
     * on the inclusion of a file in a particular fileset.
     *
     * @param PhingFile $basedir the base directory the scan is being done from
     * @param string $filename is the name of the file to check
     * @param PhingFile $file is a java.io.File object the selector can use
     *
     * @return bool whether the file should be selected or not
     *
     * @throws BuildException
     */
    public function isSelected(PhingFile $basedir, $filename, PhingFile $file)
    {

        // throw BuildException on error
        $this->validate();

        // Determine file whose out-of-dateness is to be checked
        $destfiles = $this->map->main($filename);
        // If filename does not match the To attribute of the mapper
        // then filter it out of the files we are considering
        if (empty($destfiles)) {
            return false;
        }
        // Sanity check
        if (count($destfiles) !== 1 || $destfiles[0] === null) {
            throw new BuildException('Invalid destination file results for '
                . $this->targetdir->getName() . ' with filename ' . $filename);
        }
        $destname = $destfiles[0];
        $fu = new FileUtils();
        $destfile = $fu->resolveFile($this->targetdir, $destname);

        return $this->selectionTest($file, $destfile);
    }

    /**
     * this test is our selection test that compared the file with the destfile
     * @param PhingFile $srcfile file to test; may be null
     * @param PhingFile $destfile destination file
     * @return true if source file compares with destination file
     */
    protected abstract function selectionTest(PhingFile $srcfile, PhingFile $destfile);
}
