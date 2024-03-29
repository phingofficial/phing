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
 * Tests the Condition Task.
 *
 * @author  Michiel Rook <mrook@php.net>
 *
 * @internal
 */
class ConditionTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/ConditionTest.xml'
        );
    }

    public function testEquals(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertySet('isEquals');
    }

    public function testContains(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertySet('isContains');
    }

    public function testCustomCondition(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertySet('isCustom');
    }

    public function testReferenceExists(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('ref.exists');
    }

    public function testSocketCondition(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('socket');
    }

    public function testMatches(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('matches', 'true');
    }

    public function testIsTrue(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('istrueEqOne', 'true');
        $this->assertPropertyEquals('istrueEqEleven', 'true');
    }

    public function testZero(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('zero', '0');
        $this->assertPropertyEquals('one', '1');
    }
}
