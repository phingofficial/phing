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

namespace Phing\Util;

use Phing\Util\Character;

/**
 * Unit test for Character
 *
 * @author Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.system.lang
 */
class CharacterTest extends \PHPUnit\Framework\TestCase
{
    /** @var Character */
    private $char;

    public function setUp(): void
    {
        $this->char = new Character();
    }

    public function tearDown(): void
    {
        $this->char = null;
    }

    /**
     * @dataProvider getChars
     */
    public function testIsChar($elem, bool $expected)
    {
        $this->assertSame($this->char::isLetter($elem), $expected);
    }

    public function getChars(): array
    {
        return [
            'more than 2' => ['as', false],
            'no char' => ['1', false],
            'legal' => ['s', true],
        ];
    }
}
