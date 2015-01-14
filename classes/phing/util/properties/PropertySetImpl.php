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
 * A container for properties (name-value-pairs). Basically it behaves
 * like an array, but has special semantics to support "name[]" style
 * keys that are arrays themselves.
 * 
 * @author Matthias Pigulla <mp@webfactory.de>
 */
class PropertySetImpl implements PropertySet {
		protected $p = array();
		
		public function getIterator() {
			return new ArrayIterator($this->p);
		}
		
		public function offsetExists($key) {
			if (preg_match('/(.*)\[([^\]]*)\]$/', $key, $matches)) {
				$key = $matches[1];
				$index = $matches[2];
				
				return isset($this->p[$key]) && is_array($this->p[$key]) && isset($this->p[$key][$index]);
			} else 
				return isset($this->p[$key]);
		}
		
		public function offsetUnset($key) {
			unset($this->p[$key]);
		}

		public function offsetGet($key) {
			if (preg_match('/(.*)\[([^\]]+)\]$/', $key, $matches)) {
				$key = $matches[1];
				$index = $matches[2];

				if (!isset($this->p[$key]) || !is_array($this->p[$key]) || !isset($this->p[$key][$index]))
					return null;
				
				return $this->p[$key][$index];
			} else 
				return $this->p[$key];
		}
		
		public function offsetSet($key, $value) {
			if (!$key) 
				throw new Exception("Properties must have names.");
				
			if (preg_match('/(.*)\[([^\]]*)\]$/', $key, $matches)) {
				$key = $matches[1];
				$index = $matches[2];
				
				if (!isset($this->p[$key]) || !is_array($this->p[$key]))
					$this->p[$key] = array();
					
				if ($index)
					$this->p[$key][$index] = $value;
				else
					$this->p[$key][] = $value;
			} else 
				$this->p[$key] = $value;
		} 
		
		public function keys() {
			return array_keys($this->p);
		}
		
		public function isEmpty() {
			return empty($this->p);
		}
		
		public function prefix($pre) {
			if (substr($pre, -1) !== '.') $pre .= '.';
			
			$r = array();
			$l = strlen($pre);
			
			foreach ($this->p as $k => $v) {
				if (strpos($k, $pre) === 0)
					$r[substr($k, $l)] = $v;
			}
			
			$i = new PropertySetImpl(); $i->p = $r;
			return $i;
		}
	}