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
 * Task for setting properties from an XML file in buildfiles.
 *
 * @link    http://ant.apache.org/manual/CoreTasks/xmlproperty.html
 *
 * @author  Jonathan Bond-Caron <jbondc@openmv.com>
 * @package phing.tasks.ext
 * @since   2.4.0
 */
class XmlPropertyTask extends PropertyTask
{
    /**
     * @var bool
     */
    private $keepRoot = true;

    /**
     * @var bool
     */
    private $collapseAttr = false;

    /**
     * @var string
     */
    private $delimiter = ',';

    /**
     * Keep the xml root tag as the first value in the property name
     *
     * @param bool $yesNo
     *
     * @return void
     */
    public function setKeepRoot(bool $yesNo): void
    {
        $this->keepRoot = $yesNo;
    }

    /**
     * @return bool
     */
    public function getKeepRoot(): bool
    {
        return $this->keepRoot;
    }

    /**
     * Treat attributes as nested elements.
     *
     * @param bool $yesNo
     *
     * @return void
     */
    public function setCollapseAttributes(bool $yesNo): void
    {
        $this->collapseAttr = $yesNo;
    }

    /**
     * @return bool
     */
    public function getCollapseAttributes(): bool
    {
        return $this->collapseAttr;
    }

    /**
     * Delimiter for splitting multiple values.
     *
     * @param string $d
     *
     * @return void
     */
    public function setDelimiter(string $d): void
    {
        $this->delimiter = $d;
    }

    /**
     * @return string
     */
    public function getDelimiter(): string
    {
        return $this->delimiter;
    }

    /**
     * set the property in the project to the value.
     * if the task was give a file or env attribute
     * here is where it is loaded
     *
     * @return void
     *
     * @throws IOException
     */
    public function main(): void
    {
        if ($this->file === null) {
            throw new BuildException('You must specify file to load properties from', $this->getLocation());
        }

        $this->loadFile($this->file);
    }

    /**
     * load properties from an XML file.
     *
     * @param PhingFile $file
     *
     * @return void
     *
     * @throws IOException
     * @throws BuildException
     * @throws Exception
     */
    protected function loadFile(PhingFile $file): void
    {
        $this->log('Loading ' . $file->getAbsolutePath(), Project::MSG_INFO);
        try { // try to load file
            if ($file->exists()) {
                $parser = new XmlFileParser();
                $parser->setCollapseAttr($this->collapseAttr);
                $parser->setKeepRoot($this->keepRoot);
                $parser->setDelimiter($this->delimiter);

                $properties = $parser->parseFile($file);

                $this->addProperties(new Properties($properties));
                return;
            }

            if ($this->getRequired()) {
                throw new BuildException('Could not load required properties file.');
            }

            $this->log(
                'Unable to find property file: ' . $file->getAbsolutePath() . '... skipped',
                Project::MSG_WARN
            );
        } catch (IOException $ioe) {
            throw new BuildException('Could not load properties from file.', $ioe);
        }
    }
}
