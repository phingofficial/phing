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

namespace Phing\Test\Listener;

use Exception;
use Phing\Listener\EventObject;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Unit test for EventObject.
 *
 * @author Siad Ardroumli <siad.ardroumli@gmail.com>
 *
 * @internal
 * @coversNothing
 */
class EventObjectTest extends TestCase
{
    /** @var EventObject */
    private $eventObject;

    public function setUp(): void
    {
        $this->eventObject = new EventObject(new stdClass());
    }

    public function tearDown(): void
    {
        unset($this->eventObject);
    }

    public function testEventObject(): void
    {
        $this->assertInstanceOf('stdClass', $this->eventObject->getSource());
        $this->assertSame(EventObject::class . '[source=stdClass]', (string) $this->eventObject);
    }

    public function testEventObjectThrowsExceptionOnNull(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Null source');

        new EventObject(null);
    }
}
