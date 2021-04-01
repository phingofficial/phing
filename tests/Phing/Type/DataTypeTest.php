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

namespace Phing\Test\Type;

use Phing\Exception\BuildException;
use Phing\Type\DataType;

/**
 * Unit test for DataType
 *
 */
class DataTypeTest extends \PHPUnit\Framework\TestCase
{
    private $datatype;

    protected function setUp(): void
    {
        $this->datatype = new DataType();
    }

    /**
     * testTooManyAttributes
     */
    public function testTooManyAttributes()
    {
        $ex = $this->datatype->tooManyAttributes();

        $this->assertInstanceOf(BuildException::class, $ex);
        $this->assertSame('You must not specify more than one attribute when using refid', $ex->getMessage());
    }

    /**
     * testNoChildrenAllowedException
     */
    public function testNoChildrenAllowedException()
    {
        $ex = $this->datatype->noChildrenAllowed();

        $this->assertInstanceOf(BuildException::class, $ex);
        $this->assertSame('You must not specify nested elements when using refid', $ex->getMessage());
    }

    /**
     * testCircularReferenceException
     */
    public function testCircularReferenceException()
    {
        $ex = $this->datatype->circularReference();

        $this->assertInstanceOf(BuildException::class, $ex);
        $this->assertSame('This data type contains a circular reference.', $ex->getMessage());
    }

    public function testToString()
    {
        $str = "";
        $str .= $this->datatype;
        $this->assertEquals("DataType", $str);
    }
}
