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

namespace Phing\Task\System;

use Phing\Io\File;
use Phing\Support\BuildFileTest;

/**
 * Tests the DependSet Task
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class DependSetTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/dependset.xml'
        );
    }

    public function tearDown(): void
    {
        $this->executeTarget('cleanup');
    }

    public function test1()
    {
        $this->expectBuildException(__FUNCTION__, "At least one <srcfileset> or <srcfilelist> element must be set");
    }

    public function test2()
    {
        $this->expectBuildException(
            __FUNCTION__,
            "At least one <targetfileset> or <targetfilelist> element must be set"
        );
    }

    public function test3()
    {
        $this->expectBuildException(__FUNCTION__, "At least one <srcfileset> or <srcfilelist> element must be set");
    }

    public function test4()
    {
        $this->expectNotToPerformAssertions();
        $this->executeTarget(__FUNCTION__);
    }

    public function test5()
    {
        $this->expectNotToPerformAssertions();
        $this->executeTarget(__FUNCTION__);
        $f = new File($this->getProjectDir(), 'older.tmp');
        if ($f->exists()) {
            $this->fail('dependset failed to remove out of date file ' . (string) $f);
        }
    }
}
