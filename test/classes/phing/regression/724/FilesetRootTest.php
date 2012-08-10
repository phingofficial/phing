<?php

require_once 'phing/BuildFileTest.php';

class FilesetRootTest extends BuildFileTest { 
        
    public function setUp() { 
        $this->configureProject(__DIR__."/build.xml");
    }

    public function testOnlyFilesetRootMatches() {
      $f = new PhingFile(__DIR__."/build.xml");
      $this->executeTarget("test");

      $this->assertInLogs("include ".__DIR__."/*");
      $this->assertNotInLogs("include ".__DIR__."/foo");
      
      $this->assertNotInLogs("exclude ".__DIR__."/*");
      $this->assertInLogs("exclude ".__DIR__."/foo*");
      $this->assertInLogs("exclude ".__DIR__."/foo/bar/file*");
      
    }
}
