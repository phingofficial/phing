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
 * Task for setting properties from an XML file in buildfiles.
 *
 * @author  Jonathan Bond-Caron <jbondc@openmv.com>
 * @package phing.tasks.ext
 * @since   2.4.0
 * @link    http://ant.apache.org/manual/CoreTasks/xmlproperty.html
 */
class XmlPropertyTask extends PropertyTask
{
    private $keepRoot     = true;
    private $collapseAttr = false;
    private $delimiter    = ',';
    private $required     = false;

    /**
     * Set a file to use as the source for properties.
     *
     * @param $file
     */
    public function setFile($file)
    {
        if (is_string($file)) {
            $file = new PhingFile($file);
        }
        $this->file = $file;
    }

    /**
     * Get the PhingFile that is being used as property source.
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Prefix to apply to properties loaded using <code>file</code>.
     * A "." is appended to the prefix if not specified.
     *
     * @param string $prefix prefix string
     * @return void
     * @since  2.0
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
        if (!StringHelper::endsWith(".", $prefix)) {
            $this->prefix .= ".";
        }
    }

    /**
     * @return string
     * @since 2.0
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Keep the xml root tag as the first value in the property name
     *
     * @param bool $yesNo
     */
    public function setKeepRoot(bool $yesNo)
    {
        $this->keepRoot = $yesNo;
    }

    /**
     * @return bool
     */
    public function getKeepRoot()
    {
        return $this->keepRoot;
    }

    /**
     * Treat attributes as nested elements.
     *
     * @param bool $yesNo
     */
    public function setCollapseAttributes(bool $yesNo)
    {
        $this->collapseAttr = $yesNo;
    }

    /**
     * @return bool
     */
    public function getCollapseAttributes()
    {
        return $this->collapseAttr;
    }

    /**
     * Delimiter for splitting multiple values.
     *
     * @param string $d
     */
    public function setDelimiter($d)
    {
        $this->delimiter = $d;
    }

    /**
     * @return string
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * File required or not.
     *
     * @param string $d
     */
    public function setRequired($d)
    {
        $this->required = $d;
    }

    /**
     * @return string
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * set the property in the project to the value.
     * if the task was give a file or env attribute
     * here is where it is loaded
     */
    public function main()
    {
        if ($this->file === null) {
            throw new BuildException("You must specify file to load properties from", $this->getLocation());
        }

        $props = $this->loadFile($this->file);
        $this->addProperties($props);
    }

    /**
     * load properties from an XML file.
     *
     * @param PhingFile $file
     * @throws BuildException
     * @return Properties
     */
    protected function loadFile(PhingFile $file)
    {
        $this->log("Loading " . $file->getAbsolutePath(), Project::MSG_INFO);
        try { // try to load file
            if ($file->exists()) {
                $parser = new XmlFileParser();
                $parser->setCollapseAttr($this->collapseAttr);
                $parser->setKeepRoot($this->keepRoot);
                $parser->setDelimiter($this->delimiter);

                $properties = $parser->parseFile($file);

                return new Properties($properties);
            }

            if ($this->getRequired()) {
                throw new BuildException("Could not load required properties file.");
            }

            $this->log(
                "Unable to find property file: " . $file->getAbsolutePath() . "... skipped",
                Project::MSG_WARN
            );
        } catch (IOException $ioe) {
            throw new BuildException("Could not load properties from file.", $ioe);
        }
    }
}
