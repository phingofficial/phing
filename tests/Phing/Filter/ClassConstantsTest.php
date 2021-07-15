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

namespace Phing\Test\Filter;

use Phing\Project;
use Phing\Test\Support\BuildFileTest;

/**
 * @author  Siad A6rdroumli <siad.ardroumli@gmail.com>
 *
 * @internal
 * @coversNothing
 */
class ClassConstantsTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(PHING_TEST_BASE . '/etc/filters/classconstants.xml');
    }

    public function tearDown(): void
    {
        $this->executeTarget('cleanup');
    }

    public function testClassConstants(): void
    {
        $this->executeTarget(__FUNCTION__);

        $this->assertInLogs('Setting project property: CONST1 -> CONST 1', Project::MSG_DEBUG);
        $this->assertInLogs('Setting project property: CONST2 -> CONST 2', Project::MSG_DEBUG);
        $this->assertInLogs('Setting project property: CONST3 -> CONST 3', Project::MSG_DEBUG);
    }
}
