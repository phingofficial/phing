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
require_once('phing/util/properties/PropertySet.php');

/**
 * A class that can expand ${}-style references in arbitrary strings with the 
 * corresponding values from a PropertySet.
 */
class PropertyExpansionHelper {
	protected $set;
	
	public function __construct(PropertySet $s) {
		$this->set = $s;
	}
	
	/**
     * Replaces ${} style constructions in the given value with the
     * string value of the corresponding data types.
     *
     * @param value The string to be scanned for property references.
     *              May be <code>null</code>.
     *
     * @return the given string with embedded property names replaced
     *         by values, or <code>null</code> if the given string is
     *         <code>null</code>.
     *
     * @exception BuildException if the given value has an unclosed
     *                           property name, e.g. <code>${xxx</code>
     */
	public function expand($b) {
        if ($b === null) 
            return null;
        
        if (is_array($b)) {
        	$s = $this->expandArray($b);
        	return $s;
        }
        
        $this->refStack = array();
        
        return $this->match($b);
	}
	
	protected function match($b) {
		do {
			$old = $b;
            $b = preg_replace_callback('/\$\{([^\$}]+)\}/', array($this, 'replacePropertyCallback'), $b);
		} while ($old != $b);
        return $b;        
    }
    
    protected function replacePropertyCallback($matches) {
		$propertyName = $matches[1];
		
		if (in_array($propertyName, $this->refStack))
			$this->circularException();
		
		if (!isset($this->set[$propertyName]))			
			return $matches[0];
		
		$propertyValue = $this->set[$propertyName];
		
		if (is_bool($propertyValue))
			$propertyValue = $propertyValue ? 'true' : 'false';
			
        else if (is_array($propertyValue))
        	$propertyValue = implode(',', $propertyValue); 

        array_push($this->refStack, $propertyName);
        $propertyValue = $this->match($propertyValue);
        array_pop($this->refStack);

        return $propertyValue;
    }

    protected function circularException() {
    	$n = array_pop($this->refStack);
    	throw new BuildException("Property $n was circularly defined: " . implode(" => ", $this->refStack));	
    }
    
    protected function expandArray(array $a) {
		$r = array();
		foreach ($a as $key => $value) {
			$r[$key] = $this->expand($value);
		}
		return $r;
	}
}