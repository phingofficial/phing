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

namespace Phing\Task\System;

use Phing\Exception\BuildException;
use Phing\Project;
use Phing\ProjectComponent;

/**
 * A class for creating adhoc datatypes in build file.
 *
 * @author  Hans Lellelid <hans@xmpl.org>
 */
class AdhocTypedefTask extends AdhocTask
{
    /**
     * The tag that refers to this task.
     */
    private $name;

    /**
     * Set the tag that will represent this adhoc task/type.
     *
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Main entry point.
     */
    public function main()
    {
        if (null === $this->name) {
            throw new BuildException('The name attribute is required for adhoc task definition.', $this->getLocation());
        }

        $this->execute();

        $classes = $this->getNewClasses();
        if (1 !== count($classes)) {
            throw new BuildException('You must define one (and only one) class for AdhocTypedefTask.');
        }
        $classname = array_shift($classes);

        // instantiate it to make sure it is an instance of ProjectComponent
        $t = new $classname();
        if (!($t instanceof ProjectComponent)) {
            throw new BuildException(
                'The adhoc class you defined must be an instance of phing.ProjectComponent',
                $this->getLocation()
            );
        }

        $this->log('Datatype ' . $this->name . ' will be handled by class ' . $classname, Project::MSG_VERBOSE);
        $this->project->addDataTypeDefinition($this->name, $classname);
    }
}
