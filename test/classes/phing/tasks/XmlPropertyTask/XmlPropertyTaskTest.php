<?php
require_once 'phing/BuildFileTest.php';

class XmlPropertyTaskTest extends BuildFileTest { 
        
    public function setUp() { 
        $this->configureProject(__DIR__."/build.xml");
    }

    public function testPropertyArrays() {
    	$this->scanAssertionsInLogs("test");
    }
    
}
