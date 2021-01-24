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

namespace Phing\Tasks\System;

use Phing\Parser\DynamicConfigurator;
use Phing\Project;
use Phing\Task;

/**
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system
 */
class DynamicTask extends Task implements DynamicConfigurator
{
    public function main()
    {
    }

    public function setDynamicAttribute(string $name, string $value): void
    {
        $this->getProject()->setNewProperty($name, $value);
    }

    public function customChildCreator($name, Project $project)
    {
        return new class ($project) implements DynamicConfigurator {
            /**
             * @var Project
             */
            private $project;

            public function __construct(Project $project)
            {
                $this->project = $project;
            }

            public function setDynamicAttribute(string $name, string $value): void
            {
                $this->project->setNewProperty($name, $value);
            }

            public function customChildCreator($name, Project $project)
            {
                return null;
            }
        };
    }
}
