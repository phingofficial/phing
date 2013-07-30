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
 * Tests the Zip and Unzip tasks
 * 
 * @author  Michiel Rook <mrook@php.net>
 * @version $Id$
 * @package phing.tasks.ext
 */
class ZipUnzipTaskTest extends BuildFileTest 
{ 
    public function setUp() 
    { 
        $this->configureProject(PHING_TEST_BASE 
                              . "/etc/tasks/ext/ZipUnzipTaskTest.xml");
        $this->executeTarget("setup");
    }
    
    public function tearDown()
    {
        $this->executeTarget("clean");
    }

    public function testSimpleZipContainsOneFile()
    {
        $filename = PHING_TEST_BASE .
          "/etc/tasks/ext/tmp/simple-test.zip";
        
        $this->executeTarget(__FUNCTION__);
        $this->assertFileExists($filename);
        
        $archive = new ZipArchive();
        $archive->open($filename);
        
        $this->assertEquals('test.txt', $archive->getNameIndex(0));
    }

    public function testUnzipSimpleZip()
    {
        $filename = PHING_TEST_BASE .
            "/etc/tasks/ext/tmp/test.txt";
        
        $this->assertFileNotExists($filename);
          
        $this->executeTarget(__FUNCTION__);
        
        $this->assertFileExists($filename);
        $this->assertEquals('TEST', file_get_contents($filename));
    }
}

