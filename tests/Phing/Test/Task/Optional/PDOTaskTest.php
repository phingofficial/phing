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

use Phing\Test\Support\BuildFileTest;

class PDOTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('The PHP pdo sqlite (pdo_sqlite) is needed for this test.');
        }

        $this->configureProject(PHING_TEST_BASE . '/etc/tasks/ext/pdo/test.xml');
    }

    public function tearDown(): void
    {
        @unlink('test.db');
    }

    public function testPDOTask(): void
    {
        $this->expectLogContaining(__FUNCTION__, '2 of 2 SQL statements executed successfully');
    }

    public function testWriteXMLResutFile(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileExists('result.xml');
        @unlink('result.xml');
    }

    public function testWritePlainResutFile(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileExists('result.txt');
        @unlink('result.txt');
    }

    public function testContinue(): void
    {
        $this->expectLogContaining(__FUNCTION__, 'Failed to execute:  THIS IS NO SQL');
    }

    public function testErrorProp(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('sql.error', 'true');
    }

    public function testFileList(): void
    {
        $this->expectLogContaining(__FUNCTION__, '2 of 2 SQL statements executed successfully');
    }

    public function testFileSet(): void
    {
        $this->expectLogContaining(__FUNCTION__, '2 of 2 SQL statements executed successfully');
    }

    public function testStatementCountProp(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('statement.count', 2);
    }

    public function testOptionalAttributes(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileExists('result.txt');
        $this->assertStringContainsString('# 3 statement(s) successful executed.', file_get_contents('result.txt'));
        @unlink('result.txt');
    }

    public function testDoNotFailOnConnectionError(): void
    {
        $this->expectLogContaining(__FUNCTION__, 'foo');
    }
}
