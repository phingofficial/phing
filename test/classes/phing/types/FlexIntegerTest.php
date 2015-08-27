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

include_once 'phing/types/FlexInteger.php';

/**
 * Unit test for `FlexInteger`.
 *
 * @author  Siad Aördroumli <siad.ardroumli@gmail.com>
 * @package phing.types
 */
class FlexIntegerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider checkValues()
     */
    public function testFlexInteger($value, $result, $string)
    {
        $flexInt =  new FlexInteger($value);

        $this->assertSame($string, (string) $flexInt);
        $this->assertSame($result, $flexInt->intValue());
    }

    public function checkValues()
    {
        return array(
            array('0x1a', 26, '26'),
            array('057', 47, '47'),
            array('42', 42,  '42'),
        );
    }
}
