<?php
require_once 'phing/BuildFileTest.php';

class Regression495Test extends BuildFileTest { 
        
    public function setUp() { 
        $this->configureProject(__DIR__."/build.xml");
    }

    public function testTrueFalseHandling() {
      $this->scanAssertionsInLogs('test');
    }
}
