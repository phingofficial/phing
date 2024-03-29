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
 * Tests the Truncate Task.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 *
 * @internal
 */
class TruncateTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/tasks/system/TruncateTaskTest.xml'
        );
        $this->executeTarget('setup');
    }

    public function tearDown(): void
    {
        $this->executeTarget('clean');
    }

    public function testBasic(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertSame($this->getProject()->getProperty('test.basic.length'), 0.0);
    }

    public function testExplicit(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertSame($this->getProject()->getProperty('test.explicit.length'), 1034.0);
    }

    public function testExplicitUnit(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertSame($this->getProject()->getProperty('test.explicit.unit.length'), 1024.0);
    }

    public function testExtend(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertSame($this->getProject()->getProperty('test.extend.length'), 5.0);
        $this->assertSame($this->getProject()->getProperty('test.extend.adjust.length'), 10.0);
    }

    public function testTruncate(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertSame($this->getProject()->getProperty('test.truncate.length'), 5.0);
        $this->assertSame($this->getProject()->getProperty('test.truncate.adjust.length'), 0.0);
    }

    public function testNoCreate(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileDoesNotExist($this->getProject()->getProperty('tmp.dir') . '/foo');
    }

    public function testMkdirs(): void
    {
        $this->assertFileDoesNotExist($this->getProject()->getProperty('tmp.dir') . '/baz');
        $this->executeTarget(__FUNCTION__);
        $this->assertSame($this->getProject()->getProperty('test.mkdirs.length'), 0.0);
    }

    public function testInvalidAttrs(): void
    {
        $this->expectException(BuildException::class);

        $this->executeTarget(__FUNCTION__);
    }

    public function testBadLength(): void
    {
        $this->expectException(BuildException::class);

        $this->executeTarget(__FUNCTION__);
    }

    public function testNoFiles(): void
    {
        $this->expectException(BuildException::class);

        $this->executeTarget(__FUNCTION__);
    }
}
