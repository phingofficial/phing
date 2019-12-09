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

/**
 * Tests the Condition Task
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.tasks.system
 */
class ConditionTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/Task/System/ConditionTest.xml'
        );
    }

    public function testEquals()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertySet('isEquals');
    }

    public function testContains()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertySet('isContains');
    }

    public function testCustomCondition()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertySet('isCustom');
    }

    public function testReferenceExists()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('ref.exists');
    }

    /**
     * @requires extension sockets
     */
    public function testSocketCondition()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('socket');
    }

    public function testMatches()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('matches', 'true');
    }
}
