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
 * "Inner" class for IfTask.
 * This class has same basic structure as the IfTask, although of course it doesn't support <else> tags.
 *
 * @package phing.tasks.system
 */
class ElseIfTask extends ConditionBase
{
    /**
     * @var SequentialTask
     */
    private $thenTasks = null;

    /**
     * @param SequentialTask $t
     *
     * @return void
     *
     * @throws BuildException
     */
    public function addThen(SequentialTask $t): void
    {
        if ($this->thenTasks != null) {
            throw new BuildException('You must not nest more than one <then> into <elseif>');
        }
        $this->thenTasks = $t;
    }

    /**
     * @return bool
     *
     * @throws BuildException
     */
    public function evaluate(): bool
    {
        if ($this->countConditions() > 1) {
            throw new BuildException('You must not nest more than one condition into <elseif>');
        }
        if ($this->countConditions() < 1) {
            throw new BuildException('You must nest a condition into <elseif>');
        }

        $conditions = $this->getConditions();
        $c          = $conditions[0];

        return $c->evaluate();
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function main(): void
    {
        if ($this->thenTasks !== null) {
            $this->thenTasks->main();
        }
    }
}
