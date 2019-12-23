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

/**
 * @author Alexey Borzov <avb@php.net>
 * @package phing.tasks.ext
 */
class HttpRequestTaskTest extends BaseHttpTaskTest
{
    /**
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     */
    protected function setUp(): void
    {
        $this->configureProject(PHING_TEST_BASE . '/etc/tasks/ext/http/httprequest.xml');
    }

    /**
     * @return HTTP_Request2
     */
    protected function createRequestWithMockAdapter(): HTTP_Request2
    {
        return $this->createRequest(
            $this->createMockAdapter(
                [
                    "HTTP/1.1 200 OK\r\n" .
                    "Content-Type: text/plain; charset=iso-8859-1\r\n" .
                    "\r\n" .
                    "The response containing a 'foo' string"
                ]
            )
        );
    }

    /**
     * @return void
     */
    public function testMatchesRegexp(): void
    {
        $this->copyTasksAddingCustomRequest('matchesRegexp', 'recipient', $this->createRequestWithMockAdapter());

        $this->expectLog('recipient', 'The response body matched the provided regex.');
    }

    /**
     * @return void
     */
    public function testMatchesCodeRegexp(): void
    {
        $this->copyTasksAddingCustomRequest('matchesCodeRegexp', 'recipient', $this->createRequestWithMockAdapter());

        $this->expectLog('recipient', 'The response status-code matched the provided regex.');
    }

    /**
     * @return void
     */
    public function testDoesntMatchRegexp(): void
    {
        $this->copyTasksAddingCustomRequest('doesNotMatchRegexp', 'recipient', $this->createRequestWithMockAdapter());

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('The received response body did not match the given regular expression');

        $this->executeTarget('recipient');
    }

    /**
     * @return void
     */
    public function testPostRequest(): void
    {
        $trace = new TraceHttpAdapter();

        $this->copyTasksAddingCustomRequest('post', 'recipient', $this->createRequest($trace));
        $this->executeTarget('recipient');

        self::assertEquals('POST', $trace->requests[0]['method']);
        self::assertEquals('foo=bar&baz=quux', $trace->requests[0]['body']);
    }

    /**
     * @return void
     */
    public function testAuthentication(): void
    {
        $trace = new TraceHttpAdapter();

        $this->copyTasksAddingCustomRequest('authentication', 'recipient', $this->createRequest($trace));
        $this->executeTarget('recipient');

        self::assertEquals(
            ['user' => 'luser', 'password' => 'secret', 'scheme' => 'digest'],
            $trace->requests[0]['auth']
        );
    }

    /**
     * @return void
     */
    public function testConfigAndHeaderTags(): void
    {
        $trace = new TraceHttpAdapter();

        $this->copyTasksAddingCustomRequest('nested-tags', 'recipient', $this->createRequest($trace));
        $this->executeTarget('recipient');

        self::assertEquals(10, $trace->requests[0]['config']['timeout']);
        self::assertEquals('Phing HttpRequestTask', $trace->requests[0]['headers']['user-agent']);
    }

    /**
     * @return void
     *
     * @throws HTTP_Request2_LogicException
     */
    public function testConfigurationViaProperties(): void
    {
        $trace = new TraceHttpAdapter();

        $this->copyTasksAddingCustomRequest('config-properties', 'recipient', $this->createRequest($trace));
        $this->executeTarget('recipient');

        $request = new HTTP_Request2(null, 'GET', [
            'proxy' => 'http://localhost:8080/',
            'max_redirects' => 9,
        ]);

        self::assertEquals($request->getConfig(), $trace->requests[0]['config']);
    }
}
