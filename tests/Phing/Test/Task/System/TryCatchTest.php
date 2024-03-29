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
 * Tests the Echo Task.
 *
 * @author  Christian Weiske <cweiske@cweiske.de>
 *
 * @internal
 */
class TryCatchTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/TryCatchTest.xml'
        );
    }

    public function testTryCatchFinally(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('In <catch>.');
        $this->assertInLogs('In <finally>.');
        $this->assertStringEndsWith('Tada!', $this->project->getProperty('prop.' . __FUNCTION__));
    }

    public function testExceptionInCatch(): void
    {
        $this->expectBuildExceptionContaining(
            __FUNCTION__,
            'Exception ref.' . __FUNCTION__,
            'Failing in try'
        );
        $this->assertPropertyEquals('prop.' . __FUNCTION__ . '.infinally', 'true');
    }

    public function testExceptionInFinally(): void
    {
        $this->expectBuildExceptionContaining(
            __FUNCTION__,
            'Exception ref.' . __FUNCTION__,
            'Failing in finally'
        );

        $this->assertStringContainsString(
            'Failing in try',
            $this->project->getProperty('prop.' . __FUNCTION__ . '.message')
        );
    }

    public function testNoCatch(): void
    {
        $this->expectBuildExceptionContaining(
            __FUNCTION__,
            'Exception ref.' . __FUNCTION__,
            'Failing in try'
        );

        $this->assertPropertyEquals(
            'prop.' . __FUNCTION__ . '.infinally',
            'true'
        );
    }
}
