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

namespace Phing\Test\Type;

use Phing\Test\Support\BuildFileTest;

/**
 * @internal
 */
class TaskdefForCopyTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(PHING_TEST_BASE . '/etc/types/mapper.xml');
    }

    public function tearDown(): void
    {
        $this->executeTarget('cleanup');
    }

    public function test1(): void
    {
        $this->expectNotToPerformAssertions();
        $this->executeTarget('test1');
    }

    public function test2(): void
    {
        $this->expectNotToPerformAssertions();
        $this->executeTarget('test2');
    }

    public function test3(): void
    {
        $this->executeTarget('test3');
        $this->assertInLogs('php1');
        $this->assertInLogs('php2');
    }

    public function test4(): void
    {
        $this->executeTarget('test4');
        $this->assertNotInLogs('.php1');
        $this->assertInLogs('.php2');
    }

    public function testCutDirsMapper(): void
    {
        $this->executeTarget('testCutDirsMapper');
        $outputDir = $this->getProject()->getProperty('output');
        $this->assertFileExists($outputDir . '/D');
        $this->assertFileExists($outputDir . '/c/E');
    }
}
