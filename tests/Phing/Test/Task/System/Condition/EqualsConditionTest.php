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

namespace Phing\Test\Task\System\Condition;

use Phing\Task\System\Condition\EqualsCondition;
use PHPUnit\Framework\TestCase;

/**
 * Testcase for the &lt;equals&gt; condition.
 *
 * @author Hans Lellelid <hans@xmpl.org> (Phing)
 * @author Stefan Bodewig <stefan.bodewig@epost.de> (Ant)
 *
 * @internal
 */
class EqualsConditionTest extends TestCase
{
    public function testTrim(): void
    {
        $eq = new EqualsCondition();
        $eq->setArg1('a');
        $eq->setArg2(' a');
        $this->assertFalse($eq->evaluate());

        $eq->setTrim(true);
        $this->assertTrue($eq->evaluate());

        $eq->setArg2("a\t");
        $this->assertTrue($eq->evaluate());
    }

    public function testCaseSensitive(): void
    {
        $eq = new EqualsCondition();
        $eq->setArg1('a');
        $eq->setArg2('A');
        $this->assertFalse($eq->evaluate());

        $eq->setCasesensitive(false);
        $this->assertTrue($eq->evaluate());
    }
}
