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
require_once('phing/util/properties/PropertyExpansionIterator.php');

class PropertyExpansionWrapper implements PropertySet {

	protected $helper;
	protected $set;
	
	public function __construct(PropertySet $s, $helper = null) {

		if ($helper !== null && !($helper instanceof PropertyExpansionHelper))
			throw new Exception("Provide a instanceof PropertyExpansionHelper");
		
		if ($helper === null) {
			require_once('phing/util/properties/PropertyExpansionHelper.php');
			$helper = new PropertyExpansionHelper($s);
		}
			
		$this->helper = $helper;
		$this->set = $s;
	}
	
	public function offsetGet($key) { return $this->helper->expand($this->set->offsetGet($key)); }
	public function offsetSet($key, $value) { $this->set->offsetSet($key, $value); }
	public function offsetExists($key) { return $this->set->offsetExists($key); }
	public function offsetUnset($key) { $this->set->offsetUnset($key); }
	public function getIterator() { return new PropertyExpansionIterator($this->helper, $this->set->getIterator()); }
	public function isEmpty() { return $this->set->isEmpty(); }
	public function keys() { return $this->set->keys(); }
	public function prefix($pre) { return new PropertyExpansionWrapper($this->set->prefix($pre), $this->helper); } 
	
}
