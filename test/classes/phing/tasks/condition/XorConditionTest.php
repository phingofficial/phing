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
 * Tests the XorCondition
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.condition
 */
class XorConditionTest extends BuildFileTest
{
    /**
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     */
    protected function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/XorConditionTest.xml'
        );
    }

    /**
     * @return void
     */
    public function testEmpty(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('isEmpty');
    }

    /**
     * @return void
     */
    public function test1(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('testTrue');
    }

    /**
     * @return void
     */
    public function test0(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('testFalse');
    }

    /**
     * @return void
     */
    public function test10(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('test10');
    }

    /**
     * @return void
     */
    public function test01(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('test01');
    }

    /**
     * @return void
     */
    public function test00(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('test00');
    }

    /**
     * @return void
     */
    public function test11(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('test11');
    }
}
