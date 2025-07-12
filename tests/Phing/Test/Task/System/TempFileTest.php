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

use Phing\Test\Support\BuildFileTest;

/**
 * Tests the TempFile Task.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 *
 * @internal
 */
class TempFileTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/TempFileTest.xml'
        );
    }

    public function testTempFile(): void
    {
        $this->executeTarget(__FUNCTION__);
        self::assertStringStartsWith(
            $this->getProject()->getProperty('php.tmpdir'),
            $this->getProject()->getProperty('test.temp')
        );
    }

    public function testUniqueFilenames()
    {
        $this->executeTarget(__FUNCTION__);
        $filenames = [
            $this->getProject()->getProperty('file1'),
            $this->getProject()->getProperty('file2'),
            $this->getProject()->getProperty('file3'),
            $this->getProject()->getProperty('file4'),
            $this->getProject()->getProperty('file5'),
            $this->getProject()->getProperty('file6'),
            $this->getProject()->getProperty('file7'),
            $this->getProject()->getProperty('file8'),
        ];
        $this->assertCount(8, array_unique($filenames));
    }
}
