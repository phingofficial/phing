<?php
require_once 'phing/BuildFileTest.php';

class Regression383Test extends BuildFileTest { 
        
    public function setUp() { 
        $this->configureProject(__DIR__."/build.xml");
    }

    public function testPhpUnitProperties() {
      $this->scanAssertionsInLogs('test');
    }
}
