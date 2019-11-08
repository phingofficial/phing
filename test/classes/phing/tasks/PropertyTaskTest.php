<?php

/*
 *
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
 * @author Hans Lellelid (Phing)
 * @author Conor MacNeill (Ant)
 * @package phing.tasks.system
 */
class PropertyTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(PHING_TEST_BASE . "/etc/tasks/property.xml");
    }

    public function test1()
    {
        // should get no output at all
        $this->expectOutputAndError("test1", "", "");
    }

    public function test2()
    {
        $this->expectLog("test2", "testprop1=aa, testprop3=xxyy, testprop4=aazz");
    }

    public function test4()
    {
        $this->expectLog("test4", "http.url is http://localhost:999");
    }

    public function testPrefixSuccess()
    {
        $this->executeTarget("prefix.success");
        self::assertEquals("80", $this->project->getProperty("server1.http.port"));
    }

    public function testPrefixFailure()
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('Prefix is only valid');

        $this->executeTarget("prefix.fail");
    }

    public function testFilterChain()
    {
        $this->executeTarget(__FUNCTION__);
        self::assertEquals("World", $this->project->getProperty("filterchain.test"));
    }

    public function circularDefinitionTargets()
    {
        return [
            ['test3'],
            ['testCircularDefinition1'],
            ['testCircularDefinition2'],
        ];
    }

    /**
     * @dataProvider circularDefinitionTargets
     */
    public function testCircularDefinitionDetection($target)
    {
        try {
            $this->executeTarget($target);
        } catch (BuildException $e) {
            self::assertContains("was circularly defined", $e->getMessage(), "Circular definition not detected - ");

            return;
        }
        self::fail("Did not throw exception on circular exception");
    }

    public function testToString()
    {
        $this->expectLog(__FUNCTION__, 'sourcefiles = filehash.bin');
    }

    /**
     * Inspired by @link http://www.phing.info/trac/ticket/1118
     * This test should not throw exceptions
     */
    public function testUsingPropertyTwiceInPropertyValueShouldNotThrowException()
    {
        $this->executeTarget(__FUNCTION__);

        self::assertEquals(1, 1); // increase number of positive assertions
    }
}

class HangDetectorPropertyTask extends PropertyTask
{
    protected function loadFile(PhingFile $file)
    {
        $props = new HangDetectorProperties();
        $props->load($file);
        $this->addProperties($props);
    }
}

class HangDetectorProperties extends Properties
{
    private $accesses = 0;

    public function getProperty($prop)
    {
        $this->accesses++;
        if ($this->accesses > 100) {
            throw new Exception('Cirular definition Hanged!');
        }

        return parent::getProperty($prop);
    }
}
