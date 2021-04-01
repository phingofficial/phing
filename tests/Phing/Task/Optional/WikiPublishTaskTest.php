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

use Phing\Exception\BuildException;
use Phing\Task\Optional\WikiPublishTask;
use Phing\Test\Support\BuildFileTest;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * WikiPublish task test.
 *
 * @author  Piotr Lewandowski <piotr@cassis.pl>
 */
class WikiPublishTaskTest extends BuildFileTest
{
    /**
     * @requires PHP >= 7.2
     */
    public function testApiEdit()
    {
        $task = $this->getWikiPublishMock();

        $task->setApiUrl('http://localhost/testApi.php');
        $task->setApiUser('testUser');
        $task->setApiPassword('testPassword');

        $task->setTitle('some page');
        $task->setContent('some content');
        $task->setMode('prepend');

        $task->expects($this->exactly(4))
            ->method('callApi')
            ->withConsecutive(
                ['action=login', ['lgname' => 'testUser', 'lgpassword' => 'testPassword']],
                ['action=login', ['lgname' => 'testUser', 'lgpassword' => 'testPassword', 'lgtoken' => 'testLgToken']],
                ['action=tokens&type=edit'],
                ['action=edit&token=testEditToken%2B%2F', ['minor' => '', 'title' => 'some page', 'prependtext' => 'some content']]
            )
            ->willReturnOnConsecutiveCalls(
                ['login' => ['result' => 'NeedToken', 'token' => 'testLgToken']],
                ['login' => ['result' => 'Success']],
                ['tokens' => ['edittoken' => 'testEditToken+/']],
                ['edit' => ['result' => 'Success']]
            )
        ;

        $task->main();
    }

    /**
     * @requires PHP >= 7.2
     */
    public function testInvalidAttributes()
    {
        $task = $this->getWikiPublishMock();

        try {
            $task->main();
        } catch (BuildException $e) {
            $this->assertEquals('Wiki apiUrl is required', $e->getMessage());
        }

        $task->setApiUrl('http://localhost/testApi.php');

        try {
            $task->main();
        } catch (BuildException $e) {
            $this->assertEquals('Wiki page id or title is required', $e->getMessage());
        }
    }

    /**
     * Creates WikiPublishTask mock.
     *
     * @return MockObject|WikiPublishTask
     */
    private function getWikiPublishMock()
    {
        $result = $this->getMockBuilder(WikiPublishTask::class);

        return $result->onlyMethods(['callApi'])->getMock();
    }
}
