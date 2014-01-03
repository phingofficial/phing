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
     * @expectedExceptionMessage Required attribute 'url' is missing
     */
    public function testMissingUrl()
    {
        $this->executeTarget('missingURL');
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
        $proto = new HTTP_Request2();
        $mock  = new HTTP_Request2_Adapter_Mock();

        $mock->addResponse(
            "HTTP/1.1 404 Not Found\r\n" .
            "Content-Type: text/plain; charset=iso-8859-1\r\n" .
            "\r\n" .
            "The file you seek is not here"
        );
        $proto->setAdapter($mock);

        $this->copyTasksAddingCustomRequest('error404', 'recipient', $proto);
        $this->executeTarget('recipient');
    }

    public function testFileNamingOptions()
    {
        $this->executeTarget('mkdir');

        $proto = new HTTP_Request2();
        $mock  = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(
            "HTTP/1.1 200 OK\r\n" .
            "Content-Type: text/plain; charset=iso-8859-1\r\n" .
            "\r\n" .
            "This file is named explicitly"
        );
        $mock->addResponse(
            "HTTP/1.1 200 OK\r\n" .
            "Content-Type: text/plain; charset=iso-8859-1\r\n" .
            "Content-Disposition: attachment; filename=\"disposition.txt\"\r\n" .
            "\r\n" .
            "This file is named according to Content-Disposition header"
        );
        $mock->addResponse(
            "HTTP/1.1 200 OK\r\n" .
            "Content-Type: text/plain; charset=iso-8859-1\r\n" .
            "\r\n" .
            "This file is named according to an URL part"
        );
        $proto->setAdapter($mock);

        $this->copyTasksAddingCustomRequest('filenames', 'recipient', $proto);
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
}