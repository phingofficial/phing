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
use Phing\Task\Ext\WikiPublishTask;
use Phing\Test\Support\BuildFileTest;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * WikiPublish task test.
 *
 * @author  Piotr Lewandowski <piotr@cassis.pl>
 *
 * @internal
 * @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
class WikiPublishTaskTest extends BuildFileTest
{
    /**
     * Returns the KEY of the first element for which the $callback
     *  returns TRUE. If no matching element is found the function
     *  returns NULL.
     *
     * @param array $array The array that should be searched.
     * @param callable $callback The callback function to call to check
     *  each element. The first parameter contains the value ($value),
     *  the second parameter contains the corresponding key ($key). If
     *  this function returns TRUE, the key ($key) is returned
     *  immediately and the callback will not be called for further
     *  elements.
     *
     * @return mixed The key of the first element for which the
     *  $callback returns TRUE. NULL, If no matching element is found.
     */
    private function array_find_key(array $array, callable $callback)
    {
        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return $key;
            }
        }

        return null;
    }

    public function testApiEdit(): void
    {
        $task = $this->getWikiPublishMock();

        $task->setApiUrl('http://localhost/testApi.php');
        $task->setApiUser('testUser');
        $task->setApiPassword('testPassword');

        $task->setTitle('some page');
        $task->setContent('some content');
        $task->setMode('prepend');

        $callParams = [
            ['action=login', ['lgname' => 'testUser', 'lgpassword' => 'testPassword']],
            ['action=login', ['lgname' => 'testUser', 'lgpassword' => 'testPassword', 'lgtoken' => 'testLgToken']],
            ['action=tokens&type=edit'],
            ['action=edit&token=testEditToken%2B%2F', ['minor' => '', 'title' => 'some page', 'prependtext' => 'some content']]
        ];
        $returnResults = [
            ['login' => ['result' => 'NeedToken', 'token' => 'testLgToken']],
            ['login' => ['result' => 'Success']],
            ['tokens' => ['edittoken' => 'testEditToken+/']],
            ['edit' => ['result' => 'Success']]
        ];

        $task->expects($this->exactly(count($callParams)))
            ->method('callApi')
            ->willReturnCallback(function (string $action, array|null $args) use ($callParams, $returnResults): array {
                $index = $this->array_find_key($callParams, function (array $value) use ($action, $args): bool {
                    return $value[0] === $action && ($value[1] ?? null) === $args;
                });
                if (isset($callParams[$index])) {
                    $this->assertSame($callParams[$index][1] ?? null, $args);
                    return $returnResults[$index];
                }
                return [];
            })
        ;

        $task->main();
    }

    public function testInvalidAttributes(): void
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
