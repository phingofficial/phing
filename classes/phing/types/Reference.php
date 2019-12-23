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
 * Class to hold a reference to another object in the project.
 *
 * @package phing.types
 */
class Reference
{
    /**
     * @var string $refid
     */
    protected $refid;

    /**
     * @var Project $project
     */
    private $project;

    /**
     * @param Project     $project
     * @param string|null $id
     */
    public function __construct(Project $project, ?string $id = null)
    {
        $this->setRefId($id);
        $this->setProject($project);
    }

    /**
     * @param string|null $id
     *
     * @return void
     */
    public function setRefId(?string $id): void
    {
        $this->refid = (string) $id;
    }

    /**
     * @return string|null
     */
    public function getRefId(): ?string
    {
        return $this->refid;
    }

    /**
     * @param Project $project
     *
     * @return void
     */
    public function setProject(Project $project): void
    {
        $this->project = $project;
    }

    /**
     * Get the associated project, if any; may be null.
     *
     * @return Project the associated project
     */
    public function getProject(): Project
    {
        return $this->project;
    }

    /**
     * returns reference to object in references container of project
     *
     * @param Project|null $fallback
     *
     * @return string|object
     */
    public function getReferencedObject(?Project $fallback = null)
    {
        $project = $fallback ?? $this->project;

        // setRefId casts its argument to a string, so compare strictly against ''
        if ($this->refid === '') {
            throw new BuildException('No reference specified');
        }
        $o = $project->getReference($this->refid);
        if ($o === null) {
            throw new BuildException(sprintf('Reference %s not found.', $this->refid));
        }

        return $o;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->refid;
    }
}
