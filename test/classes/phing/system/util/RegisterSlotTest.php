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

/**
 * Unit test for RegisterSlot
 *
 * @author Michiel Rook <mrook@php.net>
 * @package phing.system.util
 */
class RegisterSlotTest extends TestCase
{
    /**
     * @var RegisterSlot
     */
    private $slot;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->slot = new RegisterSlot('key123');
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $this->slot = null;
    }

    /**
     * @return void
     */
    public function testToString(): void
    {
        $this->slot->setValue('test123');

        self::assertEquals((string) $this->slot, 'test123');
    }

    /**
     * @return void
     */
    public function testArrayToString(): void
    {
        $this->slot->setValue(['test1', 'test2', 'test3']);

        self::assertEquals((string) $this->slot, '{test1,test2,test3}');
    }

    /**
     * @return void
     */
    public function testMultiArrayToString(): void
    {
        $this->slot->setValue(['test1', 'test2', ['test4', 'test5', ['test6', 'test7']], 'test3']);

        self::assertEquals((string) $this->slot, '{test1,test2,{test4,test5,{test6,test7}},test3}');
    }
}
