<?php

namespace Phing\Test\Type;

use Phing\Type\Parameter;
use Phing\Util\RegisterSlot;
use PHPUnit\Framework\TestCase;

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
 *
 * @internal
 */
class ParameterTest extends TestCase
{
    private $parameter;

    protected function setUp(): void
    {
        $this->parameter = new Parameter();
    }

    public function testSetName(): void
    {
        $this->parameter->setName(1);
        $this->assertEquals('1', $this->parameter->getName());
        $this->parameter->setName('foo');
        $this->assertEquals('foo', $this->parameter->getName());
    }

    public function testSetType(): void
    {
        $this->parameter->setType(1);
        $this->assertEquals('1', $this->parameter->getType());
        $this->parameter->setType('foo');
        $this->assertEquals('foo', $this->parameter->getType());
    }

    public function testSetValue(): void
    {
        $this->parameter->setValue(1);
        $this->assertEquals('1', $this->parameter->getValue());
        $this->parameter->setValue('foo');
        $this->assertEquals('foo', $this->parameter->getValue());
    }

    public function testGetParamsNoneSet(): void
    {
        $params = $this->parameter->getParams();
        $this->assertEquals([], $params);
    }

    public function testCreateParamGetParams(): void
    {
        $param = $this->parameter->createParam();
        $class = get_class($param);
        $this->assertEquals(Parameter::class, $class);
        $params = $this->parameter->getParams();
        $this->assertNotEquals([], $params);
    }

    public function testSetListeningValue(): void
    {
        $slot = new RegisterSlot('key');
        $slot->setValue('value1');
        $this->parameter->setListeningValue($slot);
        $this->assertEquals('value1', $this->parameter->getValue());
    }
}
