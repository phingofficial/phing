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

use PHPUnit\Framework\MockObject\MockObject;

/**
 * WikiPublish task test
 *
 * @author  Piotr Lewandowski <piotr@cassis.pl>
 * @package phing.tasks.ext
 */
class WikiPublishTaskTest extends BuildFileTest
{
    /**
     * Test Wiki api success request and response sequence
     *
     * @return void
     *
     * @requires PHP >= 7.2
     */
    public function testApiEdit(): void
    {
        $task = $this->getWikiPublishMock();

        $task->expects($this->at(0))
            ->method('callApi')
            ->with(
                'action=login',
                ['lgname' => 'testUser', 'lgpassword' => 'testPassword']
            )
            ->willReturn(['login' => ['result' => 'NeedToken', 'token' => 'testLgToken']]);

        $task->expects($this->at(1))
            ->method('callApi')
            ->with(
                'action=login',
                ['lgname' => 'testUser', 'lgpassword' => 'testPassword', 'lgtoken' => 'testLgToken']
            )
            ->willReturn(['login' => ['result' => 'Success']]);

        $task->expects($this->at(2))
            ->method('callApi')
            ->with('action=tokens&type=edit')
            ->willReturn(['tokens' => ['edittoken' => 'testEditToken+/']]);

        $task->expects($this->at(3))
            ->method('callApi')
            ->with(
                'action=edit&token=testEditToken%2B%2F',
                ['minor' => '', 'title' => 'some page', 'prependtext' => 'some content']
            )
            ->willReturn(['edit' => ['result' => 'Success']]);

        /** @var WikiPublishTask $task */
        $task->setApiUrl('http://localhost/testApi.php');
        $task->setApiUser('testUser');
        $task->setApiPassword('testPassword');

        $task->setTitle('some page');
        $task->setContent('some content');
        $task->setMode('prepend');

        $task->main();
    }

    /**
     * Test invalid input attributes
     *
     * @return void
     *
     * @requires PHP >= 7.2
     */
    public function testInvalidAttributes(): void
    {
        /** @var WikiPublishTask $task */
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
     * Creates WikiPublishTask mock
     *
     * @return MockObject|WikiPublishTask
     */
    private function getWikiPublishMock(): MockObject
    {
        $result = $this->getMockBuilder(WikiPublishTask::class);

        return $result->setMethods(['callApi'])->getMock();
    }
}
