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
 * Tests the Apply Task
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system
 */
class PathConvertTest extends BuildFileTest
{
    /**
     * Setup the test
     */
    public function setUp(): void
    {
        // Tests definitions
        $this->configureProject(PHING_TEST_BASE . '/etc/tasks/system/PathConvertTest.xml');
    }

    /**
     * Tests the OS execution for the unspecified OS
     */
    public function testDirChar()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('def|ghi', 'def|ghi');
    }

    public function testMap()
    {
        $this->assertTarget('testmap');
    }

    public function testMapper()
    {
        $this->assertTarget('testmapper');
    }

    public function testUnique()
    {
        $p = new Path($this->project, '/a:/a');
        $p->setPath('\\a;/a');
        $l = $p->listPaths();
        $this->assertCount(1, $l, '1 after setPath');
        $p->append(new Path($this->project, '/a;\\a:\\a'));
        $l = $p->listPaths();
        $this->assertCount(1, $l, '1 after append');
        $p->createPath()->setPath('\\a:/a');
        $l = $p->listPaths();
        $this->assertCount(1, $l, '1 after append');
        $l = $p->listPaths(true);
        $this->assertCount(6, $l, '6 after preserved duplicates');
    }

    private function assertTarget(string $target)
    {
        $this->executeTarget($target);
        $this->assertEquals('test#PathConvertTest.xml', $this->getProject()->getProperty('result'));
    }
}
