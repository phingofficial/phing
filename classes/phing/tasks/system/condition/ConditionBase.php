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
 * Abstract baseclass for the <condition> task as well as several
 * conditions - ensures that the types of conditions inside the task
 * and the "container" conditions are in sync.
 *
 * @author    Hans Lellelid <hans@xmpl.org>
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @copyright 2001,2002 THYRELL. All rights reserved
 * @package   phing.tasks.system.condition
 */
abstract class ConditionBase extends ProjectComponent implements IteratorAggregate, CustomChildCreator
{
    /**
     * @var Condition[]
     */
    public $conditions = []; // needs to be public for "inner" class access

    /**
     * @var string $taskName
     */
    private $taskName = 'condition';

    /**
     * @param string $taskName
     */
    public function __construct(string $taskName = 'component')
    {
        parent::__construct();
        $this->setTaskName($taskName);
    }

    /**
     * Sets the name to use in logging messages.
     *
     * @param string $name The name to use in logging messages.
     *                     Should not be <code>null</code>.
     *
     * @return void
     */
    public function setTaskName(string $name): void
    {
        $this->taskName = $name;
    }

    /**
     * Returns the name to use in logging messages.
     *
     * @return string the name to use in logging messages.
     */
    public function getTaskName(): string
    {
        return $this->taskName;
    }

    /**
     * @return int
     */
    public function countConditions(): int
    {
        return count($this->conditions);
    }

    /**
     * Required for IteratorAggregate
     *
     * @return ConditionEnumeration
     */
    public function getIterator(): ConditionEnumeration
    {
        return new ConditionEnumeration($this);
    }

    /**
     * @return Condition[]
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * @param AvailableTask $a
     *
     * @return void
     */
    public function addAvailable(AvailableTask $a): void
    {
        $this->conditions[] = $a;
    }

    /**
     * @return NotCondition
     */
    public function createNot(): NotCondition
    {
        $num = array_push($this->conditions, new NotCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return AndCondition
     */
    public function createAnd(): AndCondition
    {
        $num = array_push($this->conditions, new AndCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return OrCondition
     */
    public function createOr(): OrCondition
    {
        $num = array_push($this->conditions, new OrCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return XorCondition
     */
    public function createXor(): XorCondition
    {
        $num = array_push($this->conditions, new XorCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return EqualsCondition
     */
    public function createEquals(): EqualsCondition
    {
        $num = array_push($this->conditions, new EqualsCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return OsCondition
     */
    public function createOs(): OsCondition
    {
        $num = array_push($this->conditions, new OsCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return IsFalseCondition
     */
    public function createIsFalse(): IsFalseCondition
    {
        $num = array_push($this->conditions, new IsFalseCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return IsTrueCondition
     */
    public function createIsTrue(): IsTrueCondition
    {
        $num = array_push($this->conditions, new IsTrueCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return IsPropertyFalseCondition
     */
    public function createIsPropertyFalse(): IsPropertyFalseCondition
    {
        $num = array_push($this->conditions, new IsPropertyFalseCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return IsPropertyTrueCondition
     */
    public function createIsPropertyTrue(): IsPropertyTrueCondition
    {
        $num = array_push($this->conditions, new IsPropertyTrueCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return ContainsCondition
     */
    public function createContains(): ContainsCondition
    {
        $num = array_push($this->conditions, new ContainsCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return IsSetCondition
     */
    public function createIsSet(): IsSetCondition
    {
        $num = array_push($this->conditions, new IsSetCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return ReferenceExistsCondition
     */
    public function createReferenceExists(): ReferenceExistsCondition
    {
        $num = array_push($this->conditions, new ReferenceExistsCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return VersionCompareCondition
     */
    public function createVersionCompare(): VersionCompareCondition
    {
        $num = array_push($this->conditions, new VersionCompareCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return HttpCondition
     */
    public function createHttp(): HttpCondition
    {
        $num = array_push($this->conditions, new HttpCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return PhingVersion
     */
    public function createPhingVersion(): PhingVersion
    {
        $num = array_push($this->conditions, new PhingVersion());

        return $this->conditions[$num - 1];
    }

    /**
     * @return HasFreeSpaceCondition
     */
    public function createHasFreeSpace(): HasFreeSpaceCondition
    {
        $num = array_push($this->conditions, new HasFreeSpaceCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return FilesMatch
     */
    public function createFilesMatch(): FilesMatch
    {
        $num = array_push($this->conditions, new FilesMatch());

        return $this->conditions[$num - 1];
    }

    /**
     * @return SocketCondition
     */
    public function createSocket(): SocketCondition
    {
        $num = array_push($this->conditions, new SocketCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return IsFailure
     */
    public function createIsFailure(): IsFailure
    {
        $num = array_push($this->conditions, new IsFailure());

        return $this->conditions[$num - 1];
    }

    /**
     * @return IsFileSelected
     */
    public function createIsFileSelected(): IsFileSelected
    {
        $num = array_push($this->conditions, new IsFileSelected());

        return $this->conditions[$num - 1];
    }

    /**
     * @return Matches
     */
    public function createMatches(): Matches
    {
        $num = array_push($this->conditions, new Matches());

        return $this->conditions[$num - 1];
    }

    /**
     * @return PDOSQLExecTask
     */
    public function createPdoSqlExec(): PDOSQLExecTask
    {
        $num = array_push($this->conditions, new PDOSQLExecTask());

        return $this->conditions[$num - 1];
    }

    /**
     * @param string  $elementName
     * @param Project $project
     *
     * @return Condition
     *
     * @throws BuildException
     */
    public function customChildCreator(string $elementName, Project $project)
    {
        $condition = $project->createCondition($elementName);
        $num       = array_push($this->conditions, $condition);

        return $this->conditions[$num - 1];
    }
}
