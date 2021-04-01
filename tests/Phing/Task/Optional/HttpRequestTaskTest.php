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

namespace Phing\Test\Task\Optional;

use GuzzleHttp\Psr7\Response;
use Phing\Exception\BuildException;

/**
 * @author Alexey Borzov <avb@php.net>
 *
 * @internal
 * @coversNothing
 */
class HttpRequestTaskTest extends BaseHttpTaskTest
{
    public function setUp(): void
    {
        $this->configureProject(PHING_TEST_BASE . '/etc/tasks/ext/http/httprequest.xml');
    }

    public function testMatchesRegexp()
    {
        $this->createRequestWithMockAdapter();

        $this->expectLog('matchesRegexp', 'The response body matched the provided regex.');
    }

    public function testMatchesCodeRegexp()
    {
        $this->createRequestWithMockAdapter();

        $this->expectLog('matchesCodeRegexp', 'The response status-code matched the provided regex.');
    }

    public function testDoesntMatchRegexp()
    {
        $this->createRequestWithMockAdapter();

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('The received response body did not match the given regular expression');

        $this->executeTarget('doesNotMatchRegexp');
    }

    public function testPostRequest()
    {
        $this->createRequestWithMockAdapter();

        $this->executeTarget('post');

        $this->assertEquals('POST', $this->traces[0]['request']->getMethod());
        $this->assertEquals('foo=bar&baz=quux', $this->traces[0]['request']->getBody()->getContents());
    }

    public function testAuthentication()
    {
        $this->createMockHandler([new Response(404, [], '')]);

        try {
            $this->executeTarget('authentication');
        } catch (BuildException $e) {
        }

        $this->assertEquals(
            ['luser', 'secret', 'digest'],
            $this->traces[0]['options']['auth']
        );
    }

    public function testConfigAndHeaderTags()
    {
        $this->createMockHandler([new Response(404, [], '')]);

        try {
            $this->executeTarget('nested-tags');
        } catch (BuildException $e) {
        }

        $this->assertEquals(10, $this->traces[0]['options']['timeout']);
        $this->assertEquals('Phing HttpRequestTask', $this->traces[0]['request']->getHeader('user-agent')[0]);
    }

    public function testConfigurationViaProperties()
    {
        $this->createMockHandler([new Response(404, [], '')]);

        try {
            $this->executeTarget('config-properties');
        } catch (BuildException $e) {
        }

        $options = [
            'proxy' => 'http://localhost:8080/',
            'timeout' => 20,
        ];

        $this->assertEquals($options['proxy'], $this->traces[0]['options']['proxy']);
        $this->assertEquals($options['timeout'], $this->traces[0]['options']['timeout']);
    }

    protected function createRequestWithMockAdapter()
    {
        $this->createMockHandler([
            new Response(200, [], "The response containing a 'foo' string"),
        ]);
    }
}
