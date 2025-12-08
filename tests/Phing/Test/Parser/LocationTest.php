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

namespace Phing\Test\Parser;

use Phing\Parser\Location;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class LocationTest extends TestCase
{
    /**
     * @test
     * @dataProvider locationsProvider
     *
     * @param mixed $fileName
     * @param mixed $lineNumber
     * @param mixed $columnNumber
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('locationsProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function location($fileName, $lineNumber, $columnNumber): void
    {
        $loc = new Location($fileName, $lineNumber, $columnNumber);
        $this->assertSame(sprintf('%s:%s:%s', $fileName, $lineNumber, $columnNumber), (string) $loc);
    }

    public static function locationsProvider(): array
    {
        return [
            'normal' => ['test.php', 10, 20],
            'negative' => ['test.php', -10, -20],
        ];
    }
}
