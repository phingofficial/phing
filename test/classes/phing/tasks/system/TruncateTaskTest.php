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


/**
 * Tests the Truncate Task
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system
 */
class TruncateTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE
            . "/etc/tasks/system/TruncateTaskTest.xml"
        );
        $this->executeTarget("setup");
    }

    public function tearDown(): void
    {
        $this->executeTarget("clean");
    }

    public function testBasic()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertSame($this->getProject()->getProperty('test.basic.length'), 0);
    }

    public function testExplicit()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertSame($this->getProject()->getProperty('test.explicit.length'), 1034);
    }

    public function testExplicitUnit()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertSame($this->getProject()->getProperty('test.explicit.unit.length'), 1024);
    }

    public function testExtend()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertSame($this->getProject()->getProperty('test.extend.length'), 5);
        $this->assertSame($this->getProject()->getProperty('test.extend.adjust.length'), 10);
    }

    public function testTruncate()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertSame($this->getProject()->getProperty('test.truncate.length'), 5);
        $this->assertSame($this->getProject()->getProperty('test.truncate.adjust.length'), 0);
    }

    public function testNoCreate()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileNotExists($this->getProject()->getProperty('tmp.dir') . '/foo');
    }

    public function testMkdirs()
    {
        $this->assertFileNotExists($this->getProject()->getProperty('tmp.dir') . '/baz');
        $this->executeTarget(__FUNCTION__);
        $this->assertSame($this->getProject()->getProperty('test.mkdirs.length'), 0);
    }

    public function testInvalidAttrs()
    {
        $this->expectException(BuildException::class);

        $this->executeTarget(__FUNCTION__);
    }

    public function testBadLength()
    {
        $this->expectException(BuildException::class);

        $this->executeTarget(__FUNCTION__);
    }

    public function testNoFiles()
    {
        $this->expectException(BuildException::class);

        $this->executeTarget(__FUNCTION__);
    }
}
