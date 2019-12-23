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
 * Tests the DependSet Task
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system
 */
class DependSetTest extends BuildFileTest
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/dependset.xml'
        );
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $this->executeTarget('cleanup');
    }

    /**
     * @return void
     */
    public function test1(): void
    {
        $this->expectBuildException(__FUNCTION__, 'At least one <srcfileset> or <srcfilelist> element must be set');
    }

    /**
     * @return void
     */
    public function test2(): void
    {
        $this->expectBuildException(
            __FUNCTION__,
            'At least one <targetfileset> or <targetfilelist> element must be set'
        );
    }

    /**
     * @return void
     */
    public function test3(): void
    {
        $this->expectBuildException(__FUNCTION__, 'At least one <srcfileset> or <srcfilelist> element must be set');
    }

    /**
     * @return void
     */
    public function test4(): void
    {
        $this->executeTarget(__FUNCTION__);

        $this->assertEquals(1, 1); // increase number of positive assertions
    }

    /**
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     */
    public function test5(): void
    {
        $this->executeTarget(__FUNCTION__);
        $f = new PhingFile($this->getProjectDir(), 'older.tmp');

        $this->assertFalse($f->exists(), 'dependset failed to remove out of date file ' . (string) $f);
    }
}
