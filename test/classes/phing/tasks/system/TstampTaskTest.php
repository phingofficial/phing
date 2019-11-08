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
 * Tests the Tstamp Task
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system
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

    public function testMagicProperty()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('DSTAMP', 19700102);
    }

    public function testMagicPropertyIso()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('DSTAMP', 19720417);
    }

    public function testMagicPropertyBoth()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('DSTAMP', 19720417);
    }

    public function testMagicPropertyIsoCustomFormat()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('tstamp.test', '1972-04-17');
    }

    public function testPrefix()
    {
        $this->tstamp->setPrefix('prefix');
        $this->tstamp->main();
        $prop = $this->project->getProperty('prefix.DSTAMP');
        self::assertNotNull($prop);
    }
}
