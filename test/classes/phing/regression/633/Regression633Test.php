<?php
require_once 'phing/BuildFileTest.php';

class Regression633Test extends BuildFileTest { 
        
    public function setUp() { 
        $this->configureProject(__DIR__ . "/build.xml");
    }

    public function testCustomTask () {
      $this->executeTarget("main");
      $this->assertEquals("define('WP_DEBUG', true);", file_get_contents(PHING_TEST_TMP.'/newfile'));
    }
}
