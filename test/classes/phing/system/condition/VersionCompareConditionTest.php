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

use PHPUnit\Framework\TestCase;

class VersionCompareConditionTest extends TestCase
{
    /**
     * @var VersionCompareCondition
     */
    private $condition;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->condition = new VersionCompareCondition();
    }

    /**
     * @return void
     */
    public function testDefaultCompareIsFalseForSmallerRevision(): void
    {
        $this->condition->setVersion('1.2.7');
        $this->condition->setDesiredVersion('1.3');
        $this->assertFalse($this->condition->evaluate());
    }

    /**
     * @return void
     */
    public function testDefaultCompareIsTrueForBiggerRevision(): void
    {
        $this->condition->setVersion('1.6.2');
        $this->condition->setDesiredVersion('1.3');
        $this->assertTrue($this->condition->evaluate());
    }

    /**
     * @return void
     */
    public function testDefaultCompareIsTrueForSameRevision(): void
    {
        $this->condition->setVersion('1.3');
        $this->condition->setDesiredVersion('1.3');
        $this->assertTrue($this->condition->evaluate());
    }

    /**
     * @return void
     */
    public function testCanUseDifferentOperator(): void
    {
        $this->condition->setVersion('1.2.7');
        $this->condition->setDesiredVersion('1.3');
        $this->condition->setOperator('<=');
        $this->assertTrue($this->condition->evaluate());
    }

    /**
     * @return void
     */
    public function testUseDebugMode(): void
    {
        $this->condition->setVersion('1.2.7');
        $this->condition->setDesiredVersion('1.3');
        $this->condition->setDebug(true);
        $this->expectOutputString('Assertion that 1.2.7 >= 1.3 failed' . PHP_EOL);
        $this->condition->evaluate();
    }

    /**
     * @return void
     */
    public function testCanNotUseUnsupportedOperator(): void
    {
        $this->expectException(BuildException::class);

        $this->condition->setOperator('<<<<');
    }
}
