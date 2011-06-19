<?php

require_once 'phing/BuildFileTest.php';

class Ticket571Test extends BuildFileTest { 
        
    public function setUp() { 
        $this->configureProject(__DIR__."/build.xml");
    }
    
    protected function check($trg) {
		$this->executeTarget($trg);
		$this->assertInLogs("match a");
		$this->assertNotInLogs("match b");
    }

    public function testFilesetAttributes() { $this->check('test1'); }
	public function testFilesetNested() { $this->check('test2');}
	public function testPatternsetAttributes() { $this->check('test3');}
	public function testPatternsetNested() { $this->check('test4');}

}
