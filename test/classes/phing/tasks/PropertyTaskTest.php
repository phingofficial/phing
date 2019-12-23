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
 * @author Hans Lellelid (Phing)
 * @author Conor MacNeill (Ant)
 * @package phing.tasks.system
 */
class PropertyTaskTest extends BuildFileTest
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->configureProject(PHING_TEST_BASE . '/etc/tasks/property.xml');
    }

    /**
     * @return void
     */
    public function test1(): void
    {
        // should get no output at all
        $this->expectOutputAndError('test1', '', '');
    }

    /**
     * @return void
     */
    public function test2(): void
    {
        $this->expectLog('test2', 'testprop1=aa, testprop3=xxyy, testprop4=aazz');
    }

    /**
     * @return void
     */
    public function test4(): void
    {
        $this->expectLog('test4', 'http.url is http://localhost:999');
    }

    /**
     * @return void
     */
    public function testPrefixSuccess(): void
    {
        $this->executeTarget('prefix.success');
        $this->assertEquals('80', $this->project->getProperty('server1.http.port'));
    }

    /**
     * @return void
     */
    public function testPrefixFailure(): void
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessageRegExp('/Prefix is only valid/');

//        try {
//            $this->executeTarget("prefix.fail");
//        } catch (BuildException $e) {
//            $this->assertContains("Prefix is only valid", $e->getMessage(), "Prefix allowed on non-resource/file load - ");
//
//            return;
//        }
//        $this->fail("Did not throw exception on invalid use of prefix");

        $this->executeTarget('prefix.fail');
    }

    /**
     * @return void
     */
    public function testFilterChain(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertEquals('World', $this->project->getProperty('filterchain.test'));
    }

    /**
     * @return array[]
     */
    public function circularDefinitionTargets(): array
    {
        return [
            ['test3'],
            ['testCircularDefinition1'],
            ['testCircularDefinition2'],
        ];
    }

    /**
     * @param string $target
     *
     * @return void
     *
     * @throws Exception
     *
     * @dataProvider circularDefinitionTargets
     */
    public function testCircularDefinitionDetection(string $target): void
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessageRegExp('/was circularly defined/');

//        try {
//            $this->executeTarget($target);
//        } catch (BuildException $e) {
//            $this->assertContains("was circularly defined", $e->getMessage(), "Circular definition not detected - ");
//
//            return;
//        }
//        $this->fail("Did not throw exception on circular exception");

        $this->executeTarget($target);
    }

    /**
     * @return void
     */
    public function testToString(): void
    {
        $this->expectLog(__FUNCTION__, 'sourcefiles = filehash.bin');
    }

    /**
     * Inspired by @link http://www.phing.info/trac/ticket/1118
     * This test should not throw exceptions
     *
     * @return void
     */
    public function testUsingPropertyTwiceInPropertyValueShouldNotThrowException(): void
    {
        $this->executeTarget(__FUNCTION__);

        $this->assertEquals(1, 1); // increase number of positive assertions
    }

    public function testRequired()
    {
        $this->expectBuildException(__FUNCTION__, 'Unable to find property file.');
    }
}
