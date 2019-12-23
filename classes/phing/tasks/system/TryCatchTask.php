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
 * A wrapper task that lets you run tasks(s) when another set
 * of tasks fails.
 *
 * Inspired by {@link http://ant-contrib.sourceforge.net/tasks/tasks/trycatch.html}
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.tasks.system
 */
class TryCatchTask extends Task
{
    /**
     * @var string
     */
    protected $propertyName = '';

    /**
     * @var string
     */
    protected $referenceName = '';

    /**
     * @var SequentialTask
     */
    protected $tryContainer = null;

    /**
     * @var SequentialTask
     */
    protected $catchContainer = null;

    /**
     * @var SequentialTask
     */
    protected $finallyContainer = null;

    /**
     * Main method
     *
     * @return void
     *
     * @throws Exception
     * @throws BuildException
     */
    public function main(): void
    {
        $exc = null;

        if (empty($this->tryContainer)) {
            throw new BuildException('A nested <try> element is required');
        }

        try {
            $this->tryContainer->perform();
        } catch (BuildException $e) {
            if (!empty($this->propertyName)) {
                $this->project->setProperty($this->propertyName, $e->getMessage());
            }

            if (!empty($this->referenceName)) {
                $this->project->addReference($this->referenceName, $e);
            }

            if (!empty($this->catchContainer)) {
                $this->catchContainer->perform();
            } else {
                $exc = $e;
            }
        }

        if (!empty($this->finallyContainer)) {
            $this->finallyContainer->perform();
        }

        if (!empty($exc)) {
            throw $exc;
        }
    }

    /**
     * Sets the name of the property that will
     * contain the exception message.
     *
     * @param string $property
     *
     * @return void
     */
    public function setProperty(string $property): void
    {
        $this->propertyName = (string) $property;
    }

    /**
     * Sets the name of the reference that will
     * contain the exception.
     *
     * @param string $reference
     *
     * @return void
     */
    public function setReference(string $reference): void
    {
        $this->referenceName = $reference;
    }

    /**
     * Add nested <try> element
     *
     * @param SequentialTask $container
     *
     * @return void
     */
    public function addTry(SequentialTask $container): void
    {
        $this->tryContainer = $container;
    }

    /**
     * Add nested <catch> element
     *
     * @param SequentialTask $container
     *
     * @return void
     */
    public function addCatch(SequentialTask $container): void
    {
        $this->catchContainer = $container;
    }

    /**
     * Add nested <finally> element
     *
     * @param SequentialTask $container
     *
     * @return void
     */
    public function addFinally(SequentialTask $container): void
    {
        $this->finallyContainer = $container;
    }
}
