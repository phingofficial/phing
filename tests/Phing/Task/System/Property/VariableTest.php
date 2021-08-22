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

namespace Phing\Test\Task\System\Property;

use Phing\Test\Support\BuildFileTest;

/**
 * Tests the PropertyRegexTask Task.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 *
 * @internal
 */
class VariableTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/ext/property/VariableTest.xml'
        );
    }

    public function testVariable(): void
    {
        $this->executeTarget(__FUNCTION__);

        $this->assertInLogs('1: aazz');
        $this->assertInLogs('2: aazz');
        $this->assertInLogs('3: x = 6');
        $this->assertInLogs('4: x = 12');
        $this->assertInLogs('5: x = 6 + 12');
        $this->assertInLogs('6: I  am  a  string.');
        $this->assertInLogs('7: x = 6');
        $this->assertInLogs('8: x = 6');
    }
}
