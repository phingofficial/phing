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

use Phing\Exception\BuildException;
use Phing\Test\Support\BuildFileTest;

/**
 * @author Hans Lellelid (Phing)
 * @author Conor MacNeill (Ant)
 *
 * @internal
 */
class PropertyTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(PHING_TEST_BASE . '/etc/tasks/property.xml');
    }

    public function test1(): void
    {
        putenv('MESSAGE=foo bar baz');
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('testenv.MESSAGE', 'foo bar baz');
    }

    public function test2(): void
    {
        $this->expectLog('test2', 'testprop1=aa, testprop3=xxyy, testprop4=aazz');
    }

    public function testPropertyInFileShouldShadowExistingPropertyWithSameName(): void
    {
        $this->expectLog(__FUNCTION__, 'http.url is http://localhost:80');
        $this->assertPropertyEquals('http.port', '999');
    }

    public function testOverrideExistingPropertyWithNewProperty(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('http.port', '80');
    }

    public function testOverrideExistingPropertyWithNewPropertyFromFile(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('http.port', '80');
    }

    public function testPrefixSuccess(): void
    {
        $this->executeTarget('prefix.success');
        $this->assertEquals('80', $this->project->getProperty('server1.http.port'));
    }

    public function testPrefixFailure(): void
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessageMatches('/Prefix is only valid/');

        $this->executeTarget('prefix.fail');
    }

    public function testFilterChain(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertEquals('World', $this->project->getProperty('filterchain.test'));
    }

    public function circularDefinitionTargets(): array
    {
        return [
            ['test3'],
            ['testCircularDefinition1'],
            ['testCircularDefinition2'],
        ];
    }

    /**
     * @dataProvider circularDefinitionTargets
     *
     * @param mixed $target
     */
    public function testCircularDefinitionDetection($target): void
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessageMatches('/was circularly defined/');

        $this->executeTarget($target);
    }

    public function testToString(): void
    {
        $this->expectLog(__FUNCTION__, 'sourcefiles = filehash.bin');
    }

    /**
     * Inspired by @see http://www.phing.info/trac/ticket/1118
     * This test should not throw exceptions.
     */
    public function testUsingPropertyTwiceInPropertyValueShouldNotThrowException(): void
    {
        $this->expectNotToPerformAssertions();
        $this->executeTarget(__FUNCTION__);
    }

    public function testRequired(): void
    {
        $this->expectBuildException(__FUNCTION__, 'Unable to find property file.');
    }
}
