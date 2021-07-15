<?php

namespace Phing\Test\Type;

use ArrayObject;
use Phing\Exception\BuildException;
use Phing\Type\Environment;
use Phing\Type\EnvVariable;
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
 * @coversNothing
 */
class EnvironmentTest extends TestCase
{
    private $environment;

    public function setUp(): void
    {
        $this->environment = new Environment();
    }

    public function testVariablesNull(): void
    {
        $count = $this->environment->getVariables();
        $this->assertNull($count);
    }

    public function testVariablesObjectIsArrayObject(): void
    {
        $variablesObj = $this->environment->getVariablesObject();
        $this->assertInstanceOf(ArrayObject::class, $variablesObj);
    }

    public function testValidateWithoutKeyAndValueSetRaisesException(): void
    {
        $ev = new EnvVariable();

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('key and value must be specified for environment variables.');

        $ev->validate();
    }

    public function testValuesAgainstGetContent(): void
    {
        $ev = new EnvVariable();
        $ev->setKey(' key ');
        $ev->setValue(' value ');
        $ev->validate();
        $content = $ev->getContent();
        $this->assertEquals('key=value', $content);
    }
}
