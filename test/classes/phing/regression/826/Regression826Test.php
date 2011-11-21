<?php
require_once 'phing/BuildFileTest.php';

class Regression826Test extends BuildFileTest { 
        
    public function setUp() { 
        $this->configureProject(__DIR__."/build.xml");
    }

    public function testPropertyMerge() {
      $this->scanAssertionsInLogs('test');
    }
}
