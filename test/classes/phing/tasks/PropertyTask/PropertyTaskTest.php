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
 
require_once 'phing/BuildFileTest.php';

/**
 * @author Hans Lellelid (Phing)
 * @author Conor MacNeill (Ant)
 * @package phing.tasks.system
 */
class PropertyTaskTest extends BuildFileTest { 
        
    public function setUp() { 
        $this->configureProject(__DIR__."/build.xml");
    }
        

    public function test1() { 
        // should get no output at all
        $this->expectOutputAndError("test1", "", "");
    }

    public function test2() { 
    	$this->scanAssertionsInLogs("test2");
    }
    
    public function test3() {        
        try {
            $this->executeTarget("test3");
        } catch (BuildException $e) {
            $this->assertTrue(strpos($e->getMessage(), "was circularly defined") !== false, "Circular definition not detected - ");
            return;                     
        }
        $this->fail("Did not throw exception on circular expression");          
    }
    
    
    public function test4() { 
    	$this->scanAssertionsInLogs("test4");
    }
    
    public function test5() {
    	$this->scanAssertionsInLogs("test5");
    }
    
    public function test7() {
    	$this->scanAssertionsInLogs("test7");
    }
    
    public function testPropertyArrays() {
    	$this->scanAssertionsInLogs("property-arrays");
    }
    
    public function testPrefixSuccess() {
        $this->scanAssertionsInLogs("prefix.success");
    }
    
    public function testPropertyFileSections1() {
        $this->scanAssertionsInLogs("property-file-sections-1");
        $this->assertPropertyUnset('section');
    }
    
	public function testPropertyFileSections2() { 
        $this->scanAssertionsInLogs("property-file-sections-2");
	}
	
	public function testPropertyFileSections3() {
        $this->scanAssertionsInLogs("property-file-sections-3");
    }
    
    public function testPrefixFailure() {
       try {
            $this->executeTarget("prefix.fail");
        } catch (BuildException $e) {
            $this->assertTrue(strpos($e->getMessage(), "Prefix is only valid") !== false, "Prefix allowed on non-resource/file load - ");
            return;                     
        }
        $this->fail("Did not throw exception on invalid use of prefix");
    }
    
    public function testFilterChain()
    {
        $this->scanAssertionsInLogs(__FUNCTION__);
    }
    
}
