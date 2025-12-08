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

use Phing\Listener\BuildEvent;
use Phing\Listener\MonologListener;
use Phing\Project;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class MonologListenerTest extends TestCase
{
    public function setUp(): void
    {
        if (! class_exists('\Monolog\Logger')) {
            $this->markTestSkipped('The Monolog tasks depend on the monolog/monolog package being installed.');
        }
    }

    /**
     * @test
     */
    #[\PHPUnit\Framework\Attributes\Test]
    public function buildStarted(): void
    {
        $listener = new MonologListener();
        $this->assertNull($listener->buildStarted(new BuildEvent(new Project())));
    }
}
