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
 * @author Bryan Davis <bpd@keynetics.com>
 */
class ImportTaskTest extends BuildFileTest { 
        
    public function setUp() { 
        $this->configureProject(PHING_TEST_BASE . "/etc/tasks/importing.xml");
    }

    public function testOverloadedTarget () {
      $this->executeTarget("main");
      $this->assertInLogs("This is " . PHING_TEST_BASE . "/etc/tasks/importing.xml main target.");
    }
        
    public function testImportedTarget () {
      $this->executeTarget("imported");
      $this->assertInLogs("phing.file.imported=" . PHING_TEST_BASE . "/etc/tasks/imports/imported.xml");
      $this->assertInLogs("imported.basedir=" . PHING_TEST_BASE . "/etc/tasks/imports");
    }

    public function testImported2Target () {
      $this->executeTarget("imported2");
      $this->assertInLogs("This is " . PHING_TEST_BASE . "/etc/tasks/imports/importedImport.xml imported2 target.");
    }
        
    public function testCascadeTarget () {
      $this->executeTarget("cascade");
      $this->assertInLogs("This comes from the imported.properties file");
      $this->assertInLogs("This is " . PHING_TEST_BASE . "/etc/tasks/imports/imported.xml main target.");
      $this->assertInLogs("This is " . PHING_TEST_BASE . "/etc/tasks/importing.xml cascade target.");
    }

    public function testFlipFlopTarget () {
      // calls target in main that depends on target in import that depends on 
      // target orverridden in main
      $this->executeTarget("flipflop");
      $this->assertInLogs("This is " . PHING_TEST_BASE . "/etc/tasks/importing.xml flop target.");
      $this->assertInLogs("This is " . PHING_TEST_BASE . "/etc/tasks/imports/imported.xml flip target.");
      $this->assertInLogs("This is " . PHING_TEST_BASE . "/etc/tasks/importing.xml flipflop target.");

    }
}
