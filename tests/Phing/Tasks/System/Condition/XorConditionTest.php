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

namespace Phing\Tasks\System\Condition;

use Phing\Support\BuildFileTest;

/**
 * Tests the XorCondition
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.condition
 */
class XorConditionTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/XorConditionTest.xml'
        );
    }

    public function testEmpty()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('isEmpty');
    }

    public function test1()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('testTrue');
    }

    public function test0()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('testFalse');
    }

    public function test10()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('test10');
    }

    public function test01()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('test01');
    }

    public function test00()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('test00');
    }

    public function test11()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('test11');
    }
}
