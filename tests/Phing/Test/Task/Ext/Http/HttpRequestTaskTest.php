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

namespace Phing\Test\Task\Ext\Http;

use GuzzleHttp\Psr7\Response;
use Phing\Exception\BuildException;
use Phing\Project;

/**
 * @author Alexey Borzov <avb@php.net>
 *
 * @internal
 */
class HttpRequestTaskTest extends BaseHttpTaskTest
{
    public function setUp(): void
    {
        if (!class_exists('\GuzzleHttp\Client')) {
            $this->markTestSkipped('The Http tasks depend on the guzzlehttp/guzzle package being installed.');
        }
        $this->configureProject(PHING_TEST_BASE . '/etc/tasks/ext/http/httprequest.xml');
    }

    public function testMatchesRegexp(): void
    {
        $this->createRequestWithMockAdapter();

        $this->expectLog('matchesRegexp', 'The response body matched the provided regex.');
    }

    public function testMatchesCodeRegexp(): void
    {
        $this->createRequestWithMockAdapter();

        $this->expectLog('matchesCodeRegexp', 'The response status-code matched the provided regex.');
    }

    public function testDoesntMatchRegexp(): void
    {
        $this->createRequestWithMockAdapter();

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('The received response body did not match the given regular expression');

        $this->executeTarget('doesNotMatchRegexp');
    }

    public function testPostRequest(): void
    {
        $this->createRequestWithMockAdapter();

        $this->executeTarget('post');

        $this->assertEquals('POST', $this->traces[0]['request']->getMethod());
        $this->assertEquals('foo=bar&baz=quux', $this->traces[0]['request']->getBody()->getContents());
    }

    public function testAuthentication(): void
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

    public function testConfigAndHeaderTags(): void
    {
        $this->createMockHandler([new Response(404, [], '')]);

        try {
            $this->executeTarget('nested-tags');
        } catch (BuildException $e) {
        }

        $this->assertEquals(10, $this->traces[0]['options']['timeout']);
        $this->assertEquals('Phing HttpRequestTask', $this->traces[0]['request']->getHeader('user-agent')[0]);
    }

    public function testConfigurationViaProperties(): void
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

    public function testPayloadOrPostParameters(): void
    {
        $this->expectBuildException(__FUNCTION__, 'Cannot use <postparameter/> and <payload/> simultaneously.');
        $this->assertInLogs('Cannot use <postparameter/> and <payload/> simultaneously.', Project::MSG_ERR);
    }

    public function testPayload(): void
    {
        $this->createRequestWithMockAdapter();
        $this->executeTarget(__FUNCTION__);
        /** @var \GuzzleHttp\Psr7\Stream $body */
        $body = $this->traces[0]['request']->getBody();
        $this->assertSame('{"email": "foo@example.com"}', $body->getContents());
    }

    public function testPayloadNoTrim(): void
    {
        $this->createRequestWithMockAdapter();
        $this->executeTarget(__FUNCTION__);
        /** @var \GuzzleHttp\Psr7\Stream $body */
        $body = $this->traces[0]['request']->getBody();
        $this->assertSame("\n                19de4dd8-d46b-11f0-a654-d778f66caf95\n            ", $body->getContents());
    }

    public function testResponseProperty(): void
    {
        $this->createRequestWithMockAdapter();
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('example.response', "The response containing a 'foo' string");
        $this->assertInLogs("The response containing a 'foo' string");
    }

    public function testExistentProperty(): void
    {
        $this->createRequestWithMockAdapter();
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('existent.property', '2bf39066-d46e-11f0-8c1a-9795b4c58881');
    }

    protected function createRequestWithMockAdapter(): void
    {
        $this->createMockHandler([
            new Response(200, [], "The response containing a 'foo' string"),
        ]);
    }
}
