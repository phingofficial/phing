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
class HttpRequestTaskTest extends BaseHttpTaskTest
{
    public function setUp()
    {
        $this->configureProject(PHING_TEST_BASE . "/etc/tasks/ext/http/httprequest.xml");
    }

    protected function createRequestWithMockAdapter()
    {
        $mock = new HTTP_Request2_Adapter_Mock();
        $mock->addResponse(
            "HTTP/1.1 200 OK\r\n" .
            "Content-Type: text/plain; charset=iso-8859-1\r\n" .
            "\r\n" .
            "The response containing a 'foo' string"
        );
        $request = new HTTP_Request2();
        $request->setAdapter($mock);

        return $request;
    }

    /**
     * @expectedException BuildException
     * @expectedExceptionMessage Required attribute 'url' is missing
     */
    public function testMissingUrl()
    {
        $this->executeTarget('missingURL');
    }

    public function testMatchesRegexp()
    {
        $this->copyTasksAddingCustomRequest('matchesRegexp', 'recipient', $this->createRequestWithMockAdapter());

        $this->expectLog('recipient', 'The response body matched the provided regex.');
    }

    /**
     * @expectedException BuildException
     * @expectedExceptionMessage The received response body did not match the given regular expression
     */
    public function testDoesntMatchRegexp()
    {
        $this->copyTasksAddingCustomRequest('doesNotMatchRegexp', 'recipient', $this->createRequestWithMockAdapter());

        $this->executeTarget('recipient');
    }
}