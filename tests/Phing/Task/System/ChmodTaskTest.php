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
 * Tests the Chmod Task.
 *
 * @author  Michiel Rook <mrook@php.net>
 *
 * @requires OSFAMILY Windows
 */
class ChmodTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/ChmodTaskTest.xml'
        );
    }

    public function tearDown(): void
    {
        $this->executeTarget('clean');
    }

    public function testChangeModeFile()
    {
        $this->executeTarget(__FUNCTION__);

        clearstatcache();
        $mode = fileperms(PHING_TEST_BASE . '/etc/tasks/system/tmp/chmodtest');

        $this->assertEquals(octdec('0700'), $mode & 0777, 'chmodtest mode should have changed to 0400');
    }

    public function testChangeModeFileSet()
    {
        $this->executeTarget(__FUNCTION__);

        clearstatcache();
        $mode = fileperms(PHING_TEST_BASE . '/etc/tasks/system/tmp/chmodtest');

        $this->assertEquals(octdec('0700'), $mode & 0777, 'chmodtest mode should have changed to 0400');
    }

    public function testChangeModeDirSet()
    {
        $this->executeTarget(__FUNCTION__);

        clearstatcache();
        $mode = fileperms(PHING_TEST_BASE . '/etc/tasks/system/tmp/A');

        $this->assertEquals(octdec('0700'), $mode & 0777, 'chmodtest mode should have changed to 0400');
    }
}
