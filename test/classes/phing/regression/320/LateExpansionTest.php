<?php
require_once 'phing/BuildFileTest.php';

class LateExpansionTest extends BuildFileTest { 
        
    public function setUp() { 
        $this->configureProject(__DIR__."/build.xml");
    }

    public function testPropertiesAreLateExpanded() {
    	$this->scanAssertionsInLogs("late-expansion");
    }
    
}
