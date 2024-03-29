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
use Phing\Listener\StatisticsListener;
use Phing\Project;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class StatisticsListenerTest extends TestCase
{
    /**
     * @test
     */
    public function buildFinished(): void
    {
        $event = new BuildEvent(new Project());
        $logger = new class () extends StatisticsListener {
        };
        $msg = '/' . PHP_EOL . '                           Project Statistics' . PHP_EOL . PHP_EOL .
            'name    count    average                 total                   %      ' . PHP_EOL .
            '------------------------------------------------------------------------' . PHP_EOL .
            '        1        .*    .*    100    ' . PHP_EOL . PHP_EOL . PHP_EOL .
            '           Target Statistics - ' . PHP_EOL . PHP_EOL .
            'name    count    average    total    %    ' . PHP_EOL .
            '------------------------------------------' . PHP_EOL . PHP_EOL . PHP_EOL .
            '            Task Statistics - ' . PHP_EOL . PHP_EOL .
            'name    count    average    total    %    ' . PHP_EOL .
            '------------------------------------------' . PHP_EOL . PHP_EOL . PHP_EOL . '/';
        $this->assertNull($logger->buildFinished($event));
    }
}
