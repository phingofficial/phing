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

namespace phing::tasks::system::condition;
use phing::BuildException;
use phing::ProjectComponent;

/**
 *  Abstract baseclass for the <condition> task as well as several
 *  conditions - ensures that the types of conditions inside the task
 *  and the "container" conditions are in sync.
 * 
 *    @author    Hans Lellelid <hans@xmpl.org>
 *  @author    Andreas Aderhold <andi@binarycloud.com>
 *  @copyright © 2001,2002 THYRELL. All rights reserved
 *  @version   $Revision: 1.16 $
 *  @package   phing.tasks.system.condition
 */
abstract class ConditionBase extends ProjectComponent implements IteratorAggregate {
        
    public $conditions = array(); // needs to be public for "inner" class access

    function countConditions() {
        return count($this->conditions);
    }
    
    /**
     * Required for IteratorAggregate
     */
    function getIterator() {
        return new ConditionEnumeration($this);
    }
    
    function getConditions() {
        return $this->conditions;
    }

    /**
     * @return void
     */
    function addAvailable(AvailableTask $a) {
        $this->conditions[] = $a;
    }

    /**
     * @return NotCondition
     */
    function createNot() {
        
        $num = array_push($this->conditions, new NotCondition());
        return $this->conditions[$num-1];        
    }

    /**
     * @return AndCondition
     */
    function createAnd() {
        
        $num = array_push($this->conditions, new AndCondition());
        return $this->conditions[$num-1];
    }
    
    /**
     * @return OrCondition
     */
    function createOr() {
        
        $num = array_push($this->conditions, new OrCondition());
        return $this->conditions[$num-1];        
    }

    /**
     * @return EqualsCondition
     */
    function createEquals() {
          
        $num = array_push($this->conditions, new EqualsCondition());
        return $this->conditions[$num-1];
    }

    /**
     * @return OsCondition
     */
    function createOs() {
        
        $num = array_push($this->conditions, new OsCondition());
        return $this->conditions[$num-1];
    }
   
    /**
     * @return IsFalseCondition
     */
    function createIsFalse() {
        
        $num = array_push($this->conditions, new IsFalseCondition());
        return $this->conditions[$num-1];
    }
   
    /**
     * @return IsTrueCondition
     */
    function createIsTrue() {
        
        $num = array_push($this->conditions, new IsTrueCondition());
        return $this->conditions[$num-1];
    }
   
    /**
     * @return ContainsCondition
     */
    function createContains() {
        
        $num = array_push($this->conditions, new ContainsCondition());
        return $this->conditions[$num-1];
    }
   
    /**
     * @return IsSetCondition
     */
    function createIsSet() {
        
        $num = array_push($this->conditions, new IsSetCondition());
        return $this->conditions[$num-1];
    }

    /**
     * @return ReferenceExistsCondition
     */
    function createReferenceExists() {
        
        $num = array_push($this->conditions, new ReferenceExistsCondition());
        return $this->conditions[$num-1];
    }

}

/**
 * "Inner" class for handling enumerations.
 * Uses build-in PHP5 iterator support.
 */
class ConditionEnumeration implements Iterator {
    
    /** Current element number */
    private $num = 0;
    
    /** "Outer" ConditionBase class. */
    private $outer;

    function __construct(ConditionBase $outer) {
        $this->outer = $outer;
    }
    
    public function valid() {
        return $this->outer->countConditions() > $this->num;
    }

    function current() {
        $o = $this->outer->conditions[$this->num];
        if ($o instanceof ProjectComponent) {
            $o->setProject($this->outer->getProject());
        }
        return $o;
    }
    
    function next() {
        $this->num++;
    }
    
    function key() {
        return $this->num;
    }
    
    function rewind() {
        $this->num = 0;
    }
}
