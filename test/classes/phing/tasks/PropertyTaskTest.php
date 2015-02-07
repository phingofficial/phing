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

use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Test\AbstractBuildFileTest;

/**
 * @author  Hans Lellelid (Phing)
 * @author  Conor MacNeill (Ant)
 * @package phing.tasks.system
 * @covers \PropertyTask
 */
class PropertyTaskTest extends AbstractBuildFileTest
{

    public function setUp()
    {
        $this->configureProject(PHING_TEST_BASE . "/etc/tasks/property.xml");
    }


    public function test1()
    {
        // should get no output at all
        $this->expectOutputAndError("test1", "", "");
    }

    public function test2()
    {
        $this->scanAssertionsInLogs("test2");
    }

    /**
     * @expectedException \Phing\Exception\BuildException
     */
    public function test3()
    {
        $this->executeTarget("test3");
    }


    public function test4()
    {
        $this->scanAssertionsInLogs("test4");
    }

    public function testLoadingPropertiesFromAFileList()
    {
        $this->scanAssertionsInLogs("test-filelist-loading");
    }

    public function test7()
    {
        $this->scanAssertionsInLogs("test7");
    }

    public function testPropertyArrays()
    {
        $this->scanAssertionsInLogs("property-arrays");

        $ps = $this->project->getPropertySet();
        $this->assertTrue(is_array($ps['array']));
        $this->assertTrue(is_array($ps['direct']));
    }

    public function testPropertyFileSections1()
    {
        $this->scanAssertionsInLogs("property-file-sections-1");
    }

    public function testPropertyFileSections2()
    {
        $this->scanAssertionsInLogs("property-file-sections-2");
    }

    public function testPropertyFileSections3()
    {
        $this->scanAssertionsInLogs("property-file-sections-3");
    }

    public function testSettingMixedCdataContent()
    {
        $this->scanAssertionsInLogs('test-setting-mixed-CDATA-content');
    }

    public function testReadingFileWithPrefix()
    {
        $this->scanAssertionsInLogs('test-read-file-with-prefix');
    }

    public function testReadingFileListWithPrefix()
    {
        $this->scanAssertionsInLogs('test-read-filelist-with-prefix');
    }

    /**
     * @expectedException Phing\Exception\BuildException
     */
    public function testPrefixWithNameFails()
    {
        $task = new PropertyTask();
        $task->setName('foo');
        $task->setPrefix('bar');
        $task->main();
    }

    /**
     * @expectedException Phing\Exception\BuildException
     */
    public function testPrefixWithEnvFails()
    {
        $task = new PropertyTask();
        $task->setEnvironment('env');
        $task->setPrefix('bar');
        $task->main();
    }

    /**
     * @expectedException Phing\Exception\BuildException
     */
    public function testUsingNameOnlyFails()
    {
        $task = new PropertyTask();
        $task->setName("foo");
        $task->main();
    }

    /**
     * @expectedException Phing\Exception\BuildException
     */
    public function testUsingNoNameFileEnvironmentOrFilelistFails()
    {
        $task = new PropertyTask();
        $task->main();
    }

    /**
     * @expectedException Phing\Exception\BuildException
     */
    public function testUsingSectionWithNameFails()
    {
        $task = new PropertyTask();
        $task->setName("foo");
        $task->setSection("bar");
        $task->main();
    }

}
