<?php

require_once 'phing/BuildFileTest.php';

class FilesetRootTest extends BuildFileTest { 
        
    public function setUp() { 
        $this->configureProject(__DIR__."/build.xml");
    }

    public function testOnlyFilesetRootMatches() {
      $f = new PhingFile(__DIR__."/build.xml");
      $this->executeTarget("test");
      $this->assertInLogs("Match ".__DIR__);
      $this->assertNotInLogs("Match ".__DIR__."/foo");
      $this->assertNotInLogs("Match ".__DIR__."/foo/bar");
    }
}
