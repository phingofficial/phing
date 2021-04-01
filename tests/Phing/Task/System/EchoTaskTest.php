<?php

/**
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

namespace Phing\Test\Task\System;

use Phing\Test\Support\BuildFileTest;

/**
 * Tests the Echo Task.
 *
 * @author  Christian Weiske <cweiske@cweiske.de>
 *
 * @internal
 * @coversNothing
 */
class EchoTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/EchoTest.xml'
        );
    }

    public function testPropertyMsg()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('This is a msg');
    }

    public function testPropertyMessage()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('This is a message');
    }

    public function testInlineText()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('This is a nested inline text message');
    }

    public function testFileset()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('EchoTest.xml');
    }

    public function testDirset()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('ext');
        $this->assertInLogs('imports');
        $this->assertInLogs('system');
    }

    public function testFilesetInline()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('foo');
        $this->assertInLogs('EchoTest.xml');
    }

    public function testFilesetMsg()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs("foo\n");
        $this->assertInLogs('EchoTest.xml');
    }
}
