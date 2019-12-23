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
 * Convert dot-notation packages to relative paths.
 *
 * @author  Hans Lellelid <hans@xmpl.org>
 * @package phing.tasks.ext
 */
class PackageAsPathTask extends Task
{
    /**
     * The package to convert.
     *
     * @var string
     */
    protected $pckg;

    /**
     * The property to store the conversion in.
     *
     * @var string
     */
    protected $name;

    /**
     * Executes the package to patch converstion and stores it
     * in the user property <code>name</code>.
     *
     * @return void
     */
    public function main(): void
    {
        $this->project->setUserProperty($this->name, strtr($this->pckg, '.', '/'));
    }

    /**
     * @param string $pckg the package to convert
     *
     * @return void
     */
    public function setPackage(string $pckg): void
    {
        $this->pckg = $pckg;
    }

    /**
     * @param string $name the property to store the path in
     *
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
