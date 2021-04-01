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
 */
class HttpGetTaskTest extends BaseHttpTaskTest
{
    public function setUp(): void
    {
        $this->configureProject(PHING_TEST_BASE . "/etc/tasks/ext/http/httpget.xml");
    }

    public function testMissingDir()
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('Required attribute \'dir\' is missing');

        $this->executeTarget('missingDir');
    }

    public function testError404()
    {
        $this->createMockHandler([new Response(404, [], 'The file you seek is not here')]);

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('resulted in a `404 Not Found`');

        $this->executeTarget('error404');
    }

    public function testFileNamingOptions()
    {
        $this->executeTarget('mkdir');

        $this->createMockHandler(
            [
                new Response(200, [], 'This file is named explicitly'),
                new Response(200, ['Content-Disposition' => 'attachment; filename="disposition.txt"'], 'This file is named according to Content-Disposition header'),
                new Response(200, [], 'This file is named according to an URL part'),
            ]
        );
        $this->executeTarget('filenames');

        $this->assertStringEqualsFile(
            PHING_TEST_BASE . '/tmp/httpget/foobar.txt',
            'This file is named explicitly'
        );
        $this->assertStringEqualsFile(
            PHING_TEST_BASE . '/tmp/httpget/disposition.txt',
            'This file is named according to Content-Disposition header'
        );
        $this->assertStringEqualsFile(
            PHING_TEST_BASE . '/tmp/httpget/foo.bar',
            "This file is named according to an URL part"
        );

        $this->executeTarget('rmdir');
    }

    public function testExplicitConfiguration()
    {
        $this->createMockHandler([new Response(404, [], '')]);

        try {
            $this->executeTarget('configuration');
        } catch (BuildException $e) {
        }

        $options = [
            'proxy' => 'socks5://localhost:1080/',
            'verify' => false,
            'allow_redirects' => true,
        ];

        $this->assertEquals($options['proxy'], $this->traces[0]['options']['proxy']);
        $this->assertEquals($options['verify'], $this->traces[0]['options']['verify']);
        $this->assertEquals(\GuzzleHttp\RedirectMiddleware::$defaultSettings, $this->traces[0]['options']['allow_redirects']);
    }

    public function testAuthentication()
    {
        $this->createMockHandler([new Response(404, [], '')]);

        try {
            $this->executeTarget('authentication');
        } catch (BuildException $e) {
        }

        $this->assertEquals(
            ['luser', 'secret', 'basic'],
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

        $this->assertEquals(15, $this->traces[0]['options']['timeout']);
        $this->assertEquals('Phing HttpGetTask', $this->traces[0]['request']->getHeader('user-agent')[0]);
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

    public function testConfigurationViaEmptyProperty()
    {
        $this->createMockHandler([new Response(404, [], '')]);

        try {
            $this->executeTarget('config-properties-empty');
        } catch (BuildException $e) {
        }

        $options = [
            'proxy' => null,
            'timeout' => 20,
        ];

        $this->assertEquals($options['proxy'], $this->traces[0]['options']['proxy']);
        $this->assertEquals($options['timeout'], $this->traces[0]['options']['timeout']);
    }
}
