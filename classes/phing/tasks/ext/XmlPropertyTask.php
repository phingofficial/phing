<?php

/*
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

/** TODO: Create a better base class for PropertyTask and XmlPropertyTask that
 * only contains common functionality (instead of inheriting from PropertyTask).
 */

require_once 'phing/tasks/system/PropertyTask.php';
require_once('phing/util/properties/PropertySetImpl.php');

/**
 * Task for setting properties from an XML file in buildfiles.
 * 
 * @author    Jonathan Bond-Caron <jbondc@openmv.com>
 * @author 	  Matthias Pigulla <mp@webfactory.de>
 * @version   $Id$
 * @package   phing.tasks.ext
 * @since     2.4.0
 * @see       http://ant.apache.org/manual/CoreTasks/xmlproperty.html
 */
class XmlPropertyTask extends PropertyTask {

    private $_keepRoot = true;
    private $_collapseAttr = false;
    private $_delimiter = ',';
    private $_required = false;

    /**
     * Keep the xml root tag as the first value in the property name
     *
     * @param bool $yesNo
     */
    public function setKeepRoot($yesNo) {
        $this->_keepRoot = Boolean::cast($yesNo);
    }

    /**
     * @return bool
     */
    public function getKeepRoot() {
        return $this->_keepRoot;
    }

    /**
     * Treat attributes as nested elements.
     *
     * @param bool $yesNo
     */
    public function setCollapseAttributes($yesNo) {
        $this->_collapseAttr = Boolean::cast($yesNo);
    }

    /**
     * @return bool
     */
    public function getCollapseAttributes() {
        return $this->_collapseAttr;
    }

    /**
     * Delimiter for splitting multiple values.
     *
     * @param string $d
     */
    public function setDelimiter($d) {
        $this->_delimiter = $d;
    }

    /**
     * @return string
     */
    public function getDelimiter() {
        return $this->_delimiter;
    }

    /**
     * File required or not.
     *
     * @param string $d
     */
    public function setRequired($d) {
        $this->_required = $d;
    }

    /**
     * @return string
     */
    public function getRequired() {
        return $this->_required;
    }

    /**
     * set the property in the project to the value.
     * if the task was give a file or env attribute
     * here is where it is loaded
     */
    public function main() {

        if ($this->file === null ) {
            throw new BuildException("You must specify file to load properties from", $this->getLocation());
        }

        $this->loadFile();
    }

    protected function fetchPropertiesFromFile(PhingFile $f, PropertySet $prop) {

    	if (($xml = simplexml_load_file($f)) === false)
			throw new IOException("Unable to parse XML file $f");
    	
        $path = array();

        if ($this->_keepRoot) {
        	$this->addNode($xml, $this->prefix, $prop);
        } else {
        	foreach ($xml as $tag => $node)
        		$this->addNode($node, "{$this->prefix}$tag.", $prop);
        }
    }

    /**
     * Adds an XML node
     * 
     * @param SimpleXMLElement $node
     * @param array $path Path to this node
     * @param Properties $prop Properties will be added as they are found (by reference here)
     *
     * @return void
     */
    protected function addNode($node, $prefix, PropertySet $prop) {
        
    	$pre = $prefix . $node->getName();
    	$index = null; 
            
		// Check for attributes
		foreach($node->attributes() as $attribute => $val) {
			
			if ($attribute == '_index') { $index = (string) $val; continue; }
			
			if ($this->_collapseAttr)
				$prop["$pre.$attribute"] = (string) $val;
			else
				$prop["$pre($attribute)"] = (string) $val;
				
   		}
   		
   		if (count($node->children())) {
   			foreach ($node as $tag => $child)
   				$this->addNode($child, "$pre.", $prop);
   		} else {
                $val = (string) $node;
                
                if (isset($prop[$pre])) {
                	if (!is_array($prop[$pre]))
                		$a = array($prop[$pre]);
                	else
                		$a = $prop[$pre];

                	if ($index)
                		$a[$index] = $val;
                	else
                		$a[] = $val;
                		
                	$prop[$pre] = $a;
                } else if ($index) {
                	$prop[$pre] = array($index => $val);
                } else
                	$prop[$pre] = $val;
        }
    }
}
