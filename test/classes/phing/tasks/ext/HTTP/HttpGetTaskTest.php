<?php
/*
 *  $Id$
 *
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

require_once dirname(__FILE__) . '/BaseHttpTaskTest.php';

/**
 * @author Alexey Borzov <avb@php.net>
 * @package phing.tasks.ext
 */
class HttpGetTaskTest extends BaseHttpTaskTest
{
    public function setUp()
    {
        $this->configureProject(PHING_TEST_BASE . "/etc/tasks/ext/http/httpget.xml");
    }

    /**
     * @expectedException BuildException
     * @expectedExceptionMessage Required attribute 'dir' is missing
     */
    public function testMissingDir()
    {
        $this->executeTarget('missingDir');
    }

    /**
     * @expectedException BuildException
     * @expectedExceptionMessage Response from server: 404 Not Found
     */
    public function testError404()
    {
        $this->copyTasksAddingCustomRequest(
            'error404', 'recipient', $this->createRequest($this->createMockAdapter(
                array(
                    "HTTP/1.1 404 Not Found\r\n" .
                    "Content-Type: text/plain; charset=iso-8859-1\r\n" .
                    "\r\n" .
                    "The file you seek is not here"
                )
            ))
        );
        $this->executeTarget('recipient');
    }

    public function testFileNamingOptions()
    {
        $this->executeTarget('mkdir');

        $this->copyTasksAddingCustomRequest(
            'filenames', 'recipient', $this->createRequest($this->createMockAdapter(
                array(
                    "HTTP/1.1 200 OK\r\n" .
                    "Content-Type: text/plain; charset=iso-8859-1\r\n" .
                    "\r\n" .
                    "This file is named explicitly",

                    "HTTP/1.1 200 OK\r\n" .
                    "Content-Type: text/plain; charset=iso-8859-1\r\n" .
                    "Content-Disposition: attachment; filename=\"disposition.txt\"\r\n" .
                    "\r\n" .
                    "This file is named according to Content-Disposition header",

                    "HTTP/1.1 200 OK\r\n" .
                    "Content-Type: text/plain; charset=iso-8859-1\r\n" .
                    "\r\n" .
                    "This file is named according to an URL part"
                )
            ))
        );
        $this->executeTarget('recipient');

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
        $trace = new TraceHttpAdapter();
        $this->copyTasksAddingCustomRequest('configuration', 'recipient', $this->createRequest($trace));

        try {
            $this->executeTarget('recipient');
        } catch (BuildException $e) {
            // the request returns error 400, but we don't really care
        }

        $request = new HTTP_Request2(null, 'GET', array(
            'proxy'            => 'socks5://localhost:1080/',
            'ssl_verify_peer'  => false,
            'follow_redirects' => true
        ));

        $this->assertEquals($request->getConfig(), $trace->requests[0]['config']);
    }

    public function testAuthentication()
    {
        $trace = new TraceHttpAdapter();

        $this->copyTasksAddingCustomRequest('authentication', 'recipient', $this->createRequest($trace));
        try {
            $this->executeTarget('recipient');
        } catch (BuildException $e) {
            // the request returns error 400, but we don't really care
        }

        $this->assertEquals(
            array('user' => 'luser', 'password' => 'secret', 'scheme' => 'basic'),
            $trace->requests[0]['auth']
        );
    }

    public function testConfigAndHeaderTags()
    {
        $trace = new TraceHttpAdapter();

        $this->copyTasksAddingCustomRequest('nested-tags', 'recipient', $this->createRequest($trace));
        try {
            $this->executeTarget('recipient');
        } catch (BuildException $e) {
            // the request returns error 400, but we don't really care
        }

        $this->assertEquals(15, $trace->requests[0]['config']['timeout']);
        $this->assertEquals('Phing HttpGetTask', $trace->requests[0]['headers']['user-agent']);
    }

    public function testConfigurationViaProperties()
    {
        $trace = new TraceHttpAdapter();
        $this->copyTasksAddingCustomRequest('config-properties', 'recipient', $this->createRequest($trace));

        try {
            $this->executeTarget('recipient');
        } catch (BuildException $e) {
            // the request returns error 400, but we don't really care
        }

        $request = new HTTP_Request2(null, 'GET', array(
            'proxy'         => 'http://localhost:8080/',
            'timeout'       => 20,
            'max_redirects' => 9
        ));

        $this->assertEquals($request->getConfig(), $trace->requests[0]['config']);
    }
}