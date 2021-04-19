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

namespace Phing\Test\Type\Selector;

use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Task\System\Condition\OsCondition as Os;
use Phing\Type\Selector\PosixPermissionsSelector;
use PHPUnit\Framework\TestCase;

/**
 * Class PosixPermissionsSelectorTest.
 */
class PosixPermissionsSelectorTest extends TestCase
{
    /** @var PosixPermissionsSelector */
    private $selector;

    protected function setUp(): void
    {
        $this->markTestSkipped('Investigate why this test fails.');
        if (!Os::isFamily(Os::FAMILY_UNIX)) {
            $this->markTestSkipped('Not POSIX');
        }

        $this->selector = new PosixPermissionsSelector();
    }

    public function tearDown(): void
    {
        $this->selector = null;
    }

    /**
     * @test
     */
    public function argumentRequired(): void
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('the permissions attribute is required');

        $this->selector->isSelected(new File(__DIR__), '', new File(__FILE__));
    }

    /**
     * @test
     */
    public function isSelected(): void
    {
        $this->selector->setPermissions('rw-rw-r--');
        $this->assertTrue(
            $this->selector->isSelected(
                new File(__DIR__),
                (new File(__FILE__))->getName(),
                new File(__FILE__)
            ),
            'File permission is wrong. Actual ' . decoct(fileperms(__FILE__) & 0777)
        );
    }

    /**
     * @test
     * @dataProvider illegalArgumentProvider
     * @dataProvider legalArgumentProvider
     *
     * @param bool $throws
     */
    public function argument(string $permission, $throws = false): void
    {
        if ($throws) {
            $this->expectException(BuildException::class);
        } else {
            $this->expectNotToPerformAssertions();
        }

        $this->selector->setPermissions($permission);
    }

    public function legalArgumentProvider(): array
    {
        return [
            'legal octal string' => ['750'],
            'legal posix string' => ['rwxr-x---'],
        ];
    }

    public function illegalArgumentProvider(): array
    {
        return [
            ['855', true],
            ['4555', true],
            ['-rwxr-xr-x', true],
            ['xrwr-xr-x', true],
        ];
    }
}
