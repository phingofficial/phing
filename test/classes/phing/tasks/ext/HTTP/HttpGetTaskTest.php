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
class HttpGetTaskTest extends BaseHttpTaskTest
{
    /**
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     */
    protected function setUp(): void
    {
        $this->configureProject(PHING_TEST_BASE . '/etc/tasks/ext/http/httpget.xml');
    }

    /**
     * @return void
     */
    public function testMissingDir(): void
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('Required attribute \'dir\' is missing');

        $this->executeTarget('missingDir');
    }

    /**
     * @return void
     *
     * @throws HTTP_Request2_Exception
     * @throws HTTP_Request2_LogicException
     */
    public function testError404(): void
    {
        $this->copyTasksAddingCustomRequest(
            'error404',
            'recipient',
            $this->createRequest(
                $this->createMockAdapter(
                    [
                        "HTTP/1.1 404 Not Found\r\n" .
                        "Content-Type: text/plain; charset=iso-8859-1\r\n" .
                        "\r\n" .
                        'The file you seek is not here'
                    ]
                )
            )
        );

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('Response from server: 404 Not Found');

        $this->executeTarget('recipient');
    }

    /**
     * @return void
     *
     * @throws HTTP_Request2_Exception
     * @throws HTTP_Request2_LogicException
     */
    public function testFileNamingOptions(): void
    {
        $this->executeTarget('mkdir');

        $this->copyTasksAddingCustomRequest(
            'filenames',
            'recipient',
            $this->createRequest(
                $this->createMockAdapter(
                    [
                        "HTTP/1.1 200 OK\r\n" .
                        "Content-Type: text/plain; charset=iso-8859-1\r\n" .
                        "\r\n" .
                        'This file is named explicitly',
                        "HTTP/1.1 200 OK\r\n" .
                        "Content-Type: text/plain; charset=iso-8859-1\r\n" .
                        "Content-Disposition: attachment; filename=\"disposition.txt\"\r\n" .
                        "\r\n" .
                        'This file is named according to Content-Disposition header',
                        "HTTP/1.1 200 OK\r\n" .
                        "Content-Type: text/plain; charset=iso-8859-1\r\n" .
                        "\r\n" .
                        'This file is named according to an URL part',
                    ]
                )
            )
        );
        $this->executeTarget('recipient');

        self::assertStringEqualsFile(
            PHING_TEST_BASE . '/tmp/httpget/foobar.txt',
            'This file is named explicitly'
        );
        self::assertStringEqualsFile(
            PHING_TEST_BASE . '/tmp/httpget/disposition.txt',
            'This file is named according to Content-Disposition header'
        );
        self::assertStringEqualsFile(
            PHING_TEST_BASE . '/tmp/httpget/foo.bar',
            'This file is named according to an URL part'
        );

        $this->executeTarget('rmdir');
    }

    /**
     * @return void
     *
     * @throws HTTP_Request2_LogicException
     */
    public function testExplicitConfiguration(): void
    {
        $trace = new TraceHttpAdapter();
        $this->copyTasksAddingCustomRequest('configuration', 'recipient', $this->createRequest($trace));

        try {
            $this->executeTarget('recipient');
        } catch (BuildException $e) {
            // the request returns error 400, but we don't really care
        }

        $request = new HTTP_Request2(null, 'GET', [
            'proxy' => 'socks5://localhost:1080/',
            'ssl_verify_peer' => false,
            'follow_redirects' => true,
        ]);

        self::assertEquals($request->getConfig(), $trace->requests[0]['config']);
    }

    /**
     * @return void
     *
     * @throws HTTP_Request2_LogicException
     */
    public function testAuthentication(): void
    {
        $trace = new TraceHttpAdapter();

        $this->copyTasksAddingCustomRequest('authentication', 'recipient', $this->createRequest($trace));
        try {
            $this->executeTarget('recipient');
        } catch (BuildException $e) {
            // the request returns error 400, but we don't really care
        }

        self::assertEquals(
            ['user' => 'luser', 'password' => 'secret', 'scheme' => 'basic'],
            $trace->requests[0]['auth']
        );
    }

    /**
     * @return void
     *
     * @throws HTTP_Request2_LogicException
     */
    public function testConfigAndHeaderTags(): void
    {
        $trace = new TraceHttpAdapter();

        $this->copyTasksAddingCustomRequest('nested-tags', 'recipient', $this->createRequest($trace));
        try {
            $this->executeTarget('recipient');
        } catch (BuildException $e) {
            // the request returns error 400, but we don't really care
        }

        self::assertEquals(15, $trace->requests[0]['config']['timeout']);
        self::assertEquals('Phing HttpGetTask', $trace->requests[0]['headers']['user-agent']);
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

        try {
            $this->executeTarget('recipient');
        } catch (BuildException $e) {
            // the request returns error 400, but we don't really care
        }

        $request = new HTTP_Request2(null, 'GET', [
            'proxy' => 'http://localhost:8080/',
            'timeout' => 20,
            'max_redirects' => 9,
        ]);

        self::assertEquals($request->getConfig(), $trace->requests[0]['config']);
    }
}
