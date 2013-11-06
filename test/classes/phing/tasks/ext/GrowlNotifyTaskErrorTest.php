<?php
/**
 * Copyright (c) 2012-2013, Laurent Laville <pear@laurent-laville.org>
 *
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the authors nor the names of its contributors
 *       may be used to endorse or promote products derived from this software
 *       without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS
 * BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * PHP version 5
 *
 * @category   Tasks
 * @package    phing.tasks.ext
 * @version    $Id$
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       https://github.com/llaville/phing-GrowlNotifyTask
 */

/**
 * Tests for GrowlNotifyTask that raised error
 *
 * @category   Tasks
 * @package    phing.tasks.ext
 * @version    $Id$
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       https://github.com/llaville/phing-GrowlNotifyTask
 */
class GrowlNotifyTaskErrorTest extends BuildFileTest
{
    /**
     * Mock task's instance.
     *
     * @var object
     */
    protected $mockTask;

    /**
     * Sets up the fixture.
     *
     * @return void
     */
    public function setUp()
    {
        if (!class_exists('Net_Growl')) {
            $this->markTestSkipped("Need Net_Growl installed to test");
            return;
        }

        $this->configureProject(PHING_TEST_BASE . '/etc/tasks/ext/growl/build.xml');

        $name = '';
        
        $gntpMock = Net_Growl::singleton(
            $name, array(), '', array('protocol' => 'gntpMock')
        );

        /*
            Should be the right response in real condition (without mock)

        $gntpMock->addResponse(
            "GNTP/1.0 -ERROR NONE\r\n" .
            "Error-Code: 303\r\n" .
            "Error-Description: Required header missing"
        );
        */

        $gntpMock->addResponse(
            "GNTP/1.0 -OK NONE\r\n" .
            "Response-Action: REGISTER\r\n" .
            ""
        );
        $gntpMock->addResponse(
            "GNTP/1.0 -OK NONE\r\n" .
            "Response-Action: NOTIFY\r\n" .
            ""
        );

        $this->mockTask = new GrowlNotifyTask($gntpMock);
        $this->mockTask->setProject($this->project);
        $targets = $this->project->getTargets();
        $targets['test']->addTask($this->mockTask);
        $this->mockTask->setOwningTarget($targets['test']);
    }

    /**
     * Test for empty name attribute. So use the default value
     *
     * @return void
     */
    public function testEmptyName()
    {
        $this->executeTarget(__FUNCTION__);

        $this->assertInLogs('Application-Name: Growl for Phing');
    }

    /**
     * Test for empty title attribute. So use the default value
     *
     * @return void
     */
    public function testEmptyTitle()
    {
        try {
            $this->executeTarget(__FUNCTION__);
        } catch (BuildException $e) {
            $this->fail(
                $e->getMessage() . ' exception has been raised while not expected.'
            );
        }
        $this->assertInLogs('Notification-Title: GrowlNotify');
    }

    /**
     * Test for empty notification attribute. So use the default value
     *
     * @return void
     */
    public function testEmptyNotification()
    {
        try {
            $this->executeTarget(__FUNCTION__);
        } catch (BuildException $e) {
            $this->fail(
                $e->getMessage() . ' exception has been raised while not expected.'
            );
        }
        $this->assertInLogs('Notification-Name: General Notification');
    }

    /**
     * Test for empty appicon attribute. So use the default value
     *
     * @return void
     */
    public function testEmptyAppIcon()
    {
        try {
            $this->executeTarget(__FUNCTION__);
        } catch (BuildException $e) {
            $this->fail(
                $e->getMessage() . ' exception has been raised while not expected.'
            );
        }
        $this->assertInLogs('Application-Icon:');
    }

    /**
     * Test for empty priority attribute. So use the default value
     *
     * @return void
     */
    public function testEmptyPriority()
    {
        try {
            $this->executeTarget(__FUNCTION__);
        } catch (BuildException $e) {
            $this->fail(
                $e->getMessage() . ' exception has been raised while not expected.'
            );
        }
        $this->assertInLogs('Notification-Priority: ' . Net_Growl::PRIORITY_NORMAL);
    }

    /**
     * Test for empty protocol attribute. So use the default value
     *
     * @return void
     */
    public function testEmptyProtocol()
    {
        try {
            $this->executeTarget(__FUNCTION__);
        } catch (BuildException $e) {
            $this->fail(
                $e->getMessage() . ' exception has been raised while not expected.'
            );
        }
    }

}
