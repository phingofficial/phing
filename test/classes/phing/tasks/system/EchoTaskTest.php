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

declare(strict_types=1);

/**
 * Tests the Echo Task
 *
 * @author  Christian Weiske <cweiske@cweiske.de>
 * @package phing.tasks.system
 */
class EchoTaskTest extends BuildFileTest
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/EchoTest.xml'
        );
    }

    /**
     * @return void
     */
    public function testPropertyMsg(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('This is a msg');
    }

    /**
     * @return void
     */
    public function testPropertyMessage(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('This is a message');
    }

    /**
     * @return void
     */
    public function testInlineText(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('This is a nested inline text message');
    }

    /**
     * @return void
     */
    public function testFileset(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('EchoTest.xml');
    }

    /**
     * @return void
     */
    public function testDirset(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('ext');
        $this->assertInLogs('imports');
        $this->assertInLogs('system');
    }

    /**
     * @return void
     */
    public function testFilesetInline(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('foo');
        $this->assertInLogs('EchoTest.xml');
    }

    /**
     * @return void
     */
    public function testFilesetMsg(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs("foo\n");
        $this->assertInLogs('EchoTest.xml');
    }
}
