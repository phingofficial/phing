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

namespace Phing\Task\System\Condition;

use Phing\Exception\BuildException;
use Phing\Exception\ConfigurationException;
use Phing\Project;
use Phing\Task;

/**
 * A PHP version condition.
 *
 * @author Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class PhpVersion implements Condition
{
    private $atMost = '';
    private $atLeast = '';
    private $exactly = '';

    /**
     * Evaluate the condition.
     *
     * @return bool if the condition is true.
     * @throws BuildException if an error occurs.
     */
    public function evaluate()
    {
        $this->validate();
        $actual = PHP_VERSION;
        if ($this->atLeast !== '') {
            return version_compare($actual, $this->atLeast, '>=');
        }

        if ($this->exactly !== '') {
            return version_compare($actual, $this->exactly, '=');
        }

        if ($this->atMost !== '') {
            return version_compare($actual, $this->atMost, '<=');
        }

        return false;
    }

    private function validate(): void
    {
        if ($this->atLeast !== '' && $this->exactly !== '' && $this->atMost !== '') {
            throw new BuildException('Only one of atleast or atmost or exactly may be set.');
        }
        if ($this->atLeast === '' && $this->exactly === '' && $this->atMost === '') {
            throw new BuildException('One of atleast or atmost or exactly must be set.');
        }
    }

    /**
     * Get the atleast attribute.
     *
     * @return string the atleast attribute.
     */
    public function getAtLeast(): string
    {
        return $this->atLeast;
    }

    /**
     * Set the atleast attribute.
     * This is of the form major.minor.point
     * For example 1.7.0
     *
     * @param string $atLeast the version to check against.
     */
    public function setAtLeast(string $atLeast): void
    {
        $this->atLeast = $atLeast;
    }

    /**
     * Get the atmost attribute.
     *
     * @return string the atmost attribute.
     */
    public function getAtMost(): string
    {
        return $this->atMost;
    }

    /**
     * Set the atmost attribute.
     * This is of the form major.minor.point
     * For example 1.7.0
     *
     * @param string $atMost the version to check against.
     */
    public function setAtMost(string $atMost): void
    {
        $this->atMost = $atMost;
    }

    /**
     * Get the exactly attribute.
     *
     * @return string the exactly attribute.
     */
    public function getExactly(): string
    {
        return $this->exactly;
    }

    /**
     * Set the exactly attribute.
     * This is of the form major.minor.point.
     * For example 1.7.0.
     *
     * @param string $exactly the version to check against.
     */
    public function setExactly(string $exactly): void
    {
        $this->exactly = $exactly;
    }
}
