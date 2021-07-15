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

namespace Phing\Test\Util;

use Phing\Util\RegisterSlot;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for RegisterSlot.
 *
 * @author Michiel Rook <mrook@php.net>
 *
 * @internal
 * @coversNothing
 */
class RegisterSlotTest extends TestCase
{
    private $slot;

    public function setUp(): void
    {
        $this->slot = new RegisterSlot('key123');
    }

    public function tearDown(): void
    {
        unset($this->slot);
    }

    public function testToString(): void
    {
        $this->slot->setValue('test123');

        $this->assertEquals('test123', (string) $this->slot);
    }

    public function testArrayToString(): void
    {
        $this->slot->setValue(['test1', 'test2', 'test3']);

        $this->assertEquals('{test1,test2,test3}', (string) $this->slot);
    }

    public function testMultiArrayToString(): void
    {
        $this->slot->setValue(['test1', 'test2', ['test4', 'test5', ['test6', 'test7']], 'test3']);

        $this->assertEquals('{test1,test2,{test4,test5,{test6,test7}},test3}', (string) $this->slot);
    }
}
