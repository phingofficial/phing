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

use Phing\Project;
use Phing\Task\System\TstampTask;
use Phing\Test\Support\BuildFileTest;

/**
 * Tests the Tstamp Task.
 *
 * - Timezone is always UTC in tests
 * - Locale is always en_US in tests
 *
 * @see tests/build.xml:35
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 *
 * @internal
 */
class TstampTaskTest extends BuildFileTest
{
    /** @var TstampTask */
    private $tstamp;

    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/TstampTest.xml'
        );

        $this->tstamp = new TstampTask();
        $this->tstamp->setProject($this->project);
    }

    public function testMagicProperty(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('DSTAMP', 19700102);
    }

    public function testMagicPropertyIso(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('DSTAMP', 19720417);
    }

    public function testMagicPropertyBoth(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('DSTAMP', 19720417);
    }

    public function testMagicPropertyIsoCustomFormat(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('tstamp.test', '1972-04-17');
    }

    public function testPrefix(): void
    {
        $this->tstamp->setPrefix('prefix');
        $this->tstamp->main();
        $prop = $this->project->getProperty('prefix.DSTAMP');
        $this->assertNotNull($prop);
    }

    public function testWarningOnOldSyntax(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('pattern attribute must use ICU format https://www.phing.info/guide/chunkhtml/TstampTask.html', Project::MSG_WARN);
    }

    public function testNegativeMagicProperty(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('DSTAMP', '19691230');
        $this->assertPropertyEquals('TSTAMP', '2013');
        $this->assertPropertyEquals('TODAY', 'December 30, 1969');
    }

    public function testTimezone(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('caracas', '05:54');
        $this->assertPropertyEquals('TSTAMP', '0954'); // UTC in tests
    }

    public function testLocale(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('español', 'viernes');
    }

    public function testIllegalLocaleShouldNotFail(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('tstamp.test', '');
    }
}
