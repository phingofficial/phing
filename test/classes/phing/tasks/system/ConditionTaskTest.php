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

declare(strict_types=1);

/**
 * Tests the Condition Task
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.tasks.system
 */
class ConditionTaskTest extends BuildFileTest
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/ConditionTest.xml'
        );
    }

    /**
     * @return void
     */
    public function testEquals(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertySet('isEquals');
    }

    /**
     * @return void
     */
    public function testContains(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertySet('isContains');
    }

    /**
     * @return void
     */
    public function testCustomCondition(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertySet('isCustom');
    }

    /**
     * @return void
     */
    public function testReferenceExists(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('ref.exists');
    }

    /**
     * @return void
     *
     * @requires extension sockets
     */
    public function testSocketCondition(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('socket');
    }

    /**
     * @return void
     */
    public function testMatches(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('matches', 'true');
    }
}
