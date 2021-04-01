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
 * Tests the Copy Task.
 *
 * @author Siad Ardroumli <siad.ardroumli@gmail.com>
 *
 * @internal
 * @coversNothing
 */
class ReflexiveTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/tasks/system/ReflexiveTaskTest.xml'
        );
        $this->executeTarget('setup');
    }

    public function tearDown(): void
    {
        $this->executeTarget('clean');
    }

    public function test1(): void
    {
        $this->expectBuildException(__FUNCTION__, 'You must specify a file or fileset(s) for the <reflexive> task.');
    }

    public function test2(): void
    {
        $this->expectBuildException(__FUNCTION__, 'File cannot be a directory.');
    }

    public function test3(): void
    {
        $this->expectBuildException(__FUNCTION__, 'You must specify a filterchain for the <reflexive> task.');
    }

    public function testReplaceCharactersUsingFileset(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertStringEqualsFile(
            $this->getProject()->getProperty('tmp.dir') . '/foo.bar',
            'y'
        );
    }
}
