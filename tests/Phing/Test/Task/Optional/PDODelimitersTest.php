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

use Phing\Io\File;
use Phing\Io\IOException;
use Phing\Task\System\Pdo\PDOSQLExecTask;
use Phing\Test\Support\BuildFileTest;

/**
 * @author Alexey Borzov <avb@php.net>
 *
 * @internal
 */
class PDODelimitersTest extends BuildFileTest
{
    protected $queries = [];

    protected $mockTask;

    /**
     * @throws IOException
     */
    public function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('The PHP pdo sqlite (pdo_sqlite) is needed for this test.');
        }

        $this->configureProject(PHING_TEST_BASE . '/etc/tasks/ext/pdo/empty.xml');
        $this->queries = [];

        $this->mockTask = $this->getMockBuilder(PDOSQLExecTask::class)
            ->onlyMethods(['getConnection', 'execSQL'])
            ->getMock()
        ;
        $this->mockTask->setProject($this->project);
        // prevents calling beginTransaction() on obviously missing PDO instance
        $this->mockTask->setAutocommit(true);
        $this->mockTask->expects($this->atLeastOnce())
            ->method('getConnection')
            ->willReturn($this->getMockBuilder(\PDO::class)->disableOriginalConstructor()->getMock());
        $this->mockTask->expects($this->atLeastOnce())
            ->method('execSQL')
            ->willReturnCallback([$this, 'storeQuery'])
        ;

        $targets = $this->project->getTargets();
        $targets['test']->addTask($this->mockTask);
        $this->mockTask->setOwningTarget($targets['test']);
    }

    public function storeQuery($query): void
    {
        $query = trim($query);
        if (strlen($query)) {
            $this->queries[] = str_replace(["\n\n", "\r"], ["\n", ''], $query);
        }
    }

    public function testDelimiterTypeNormal(): void
    {
        // for some reason default splitter mangles spaces on subsequent lines
        $expected = [
            <<<'SQL'
                insert into foo (bar, "strange;name""indeed") values ('bar''s value containing ;', 'a value for strange named column')
                SQL,
            <<<'SQL'
                delete
                 from
                 foo where bar = 'some value'
                SQL,
            <<<'SQL'
                update dump -- I should not be ignored
                 set message = 'I am a string with \\ backslash \' escapes and semicolons;'
                SQL,
            <<<'SQL'
                create procedure setfoo(newfoo int)
                 begin
                 set @foo = newfoo;
                 end
                SQL,
            <<<'SQL'
                insert into dump (message) values ('I am a statement not ending with a delimiter')
                SQL,
        ];
        // and insists on "\n" linebreaks
        foreach ($expected as &$query) {
            $query = str_replace(["\n\n", "\r"], ["\n", ''], $query);
        }

        $this->mockTask->setSrc(new File(PHING_TEST_BASE . '/etc/tasks/ext/pdo/delimiters-normal.sql'));
        $this->mockTask->setDelimiterType(PDOSQLExecTask::DELIM_NORMAL);
        $this->project->setProperty('bar.value', 'some value');
        $this->project->executeTarget('test');

        $this->assertEquals($expected, $this->queries);
    }

    public function testDelimiterTypeRow(): void
    {
        // for some reason default splitter mangles spaces on subsequent lines
        $expected = [
            <<<'SQL'
                insert into "duh" (foo) values ('duh')
                SQL,
            <<<'SQL'
                update "duh?" -- I should not be ignored
                 set foo = 'some value'
                SQL,
            <<<'SQL'
                insert into dump (message) values ('I am a statement not ending with a delimiter')
                SQL,
        ];
        // and insists on "\n" linebreaks
        foreach ($expected as &$query) {
            $query = str_replace(["\n\n", "\r"], ["\n", ''], $query);
        }

        $this->mockTask->setSrc(new File(PHING_TEST_BASE . '/etc/tasks/ext/pdo/delimiters-row.sql'));
        $this->mockTask->setDelimiterType(PDOSQLExecTask::DELIM_ROW);
        $this->mockTask->setDelimiter('duh');
        $this->project->setProperty('foo.value', 'some value');
        $this->project->executeTarget('test');

        $this->assertEquals($expected, $this->queries);
    }

    /**
     * Checks that PDOSQLExecTask properly understands PostgreSQL dialect
     * (especially "dollar quoting") when working with 'pgsql:' URLs.
     *
     * @see http://www.phing.info/trac/ticket/499
     * @see http://www.postgresql.org/docs/9.0/interactive/sql-syntax-lexical.html#SQL-SYNTAX-DOLLAR-QUOTING
     */
    public function testRequest499(): void
    {
        $expected = [
            <<<'SQL'
                select 1
                # 2
                SQL,
            <<<'SQL'
                select 'foo'
                // 'bar'
                SQL,
            <<<'SQL'
                insert into foo (bar, "strange;name""indeed") values ('bar''s value containing ;', 'a value for strange named column')
                SQL,
            <<<'SQL'
                create function foo(text)
                returns boolean as
                $function$
                BEGIN
                    RETURN ($1 ~ $q$[\t\r\n\v\\]$q$);
                END;
                $function$
                language plpgsql
                SQL,
            <<<'SQL'
                CREATE FUNCTION phingPDOtest() RETURNS "trigger"
                    AS $_X$
                if (1)
                {
                    # All is well - just continue

                    return;
                }
                else
                {
                    # Not good - this is probably a fatal error!
                    elog(ERROR,"True is not true");

                    return "SKIP";
                }
                $_X$
                    LANGUAGE plperl
                SQL,
            "insert into foo (bar) \nvalues ('some value')",
            <<<'SQL'
                insert into foo (bar) values ($$ a dollar-quoted string containing a few quotes ' ", a $placeholder$ and a semicolon;$$)
                SQL,
            <<<'SQL'
                create rule blah_insert
                as on insert to blah do instead (
                    insert into foo values (new.id, 'blah');
                    insert into bar values (new.id, 'blah-blah');
                )
                SQL,
            <<<'SQL'
                insert into dump (message) values ('I am a statement not ending with a delimiter')
                SQL,
        ];
        foreach ($expected as &$query) {
            $query = str_replace(["\n\n", "\r"], ["\n", ''], $query);
        }

        $this->mockTask->setSrc(new File(PHING_TEST_BASE . '/etc/tasks/ext/pdo/delimiters-pgsql.sql'));
        $this->mockTask->setUrl('pgsql:host=localhost;dbname=phing');
        $this->mockTask->setDelimiterType(PDOSQLExecTask::DELIM_NORMAL);
        $this->project->setProperty('bar.value', 'some value');
        $this->project->executeTarget('test');

        $this->assertEquals($expected, $this->queries);
    }
}
