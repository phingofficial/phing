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

namespace Phing\Task\Optional;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use HttpTask;
use Phing\Exception\BuildException;
use Phing\Support\BuildFileTest;

/**
 * @author Alexey Borzov <avb@php.net>
 */
abstract class BaseHttpTaskTest extends BuildFileTest
{
    protected $traces = [];

    /**
     * @param \GuzzleHttp\Psr7\Response[] $responses
     * @return void
     */
    protected function createMockHandler(array $responses): void
    {
        $mockHandler = new MockHandler();
        foreach ($responses as $response) {
            $mockHandler->append($response);
        }

        $requestsHandler = Middleware::history($this->traces);

        HttpTask::getHandlerStack()->setHandler($mockHandler);
        HttpTask::getHandlerStack()->push($requestsHandler);
    }

    public function testMissingUrl()
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('Required attribute \'url\' is missing');
        $this->executeTarget('missingURL');
    }
}
