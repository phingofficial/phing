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
 * PropertyCopy
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.ext.property
 */
class PropertyCopy extends AbstractPropertySetterTask
{
    /**
     * @var string $from
     */
    private $from;

    /**
     * @var bool $silent
     */
    private $silent;

    /***
     * Default Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->from   = null;
        $this->silent = false;
    }

    /**
     * @param string $from
     *
     * @return void
     */
    public function setFrom(string $from): void
    {
        $this->from = $from;
    }

    /**
     * @param bool $silent
     *
     * @return void
     */
    public function setSilent(bool $silent): void
    {
        $this->silent = $silent;
    }

    /**
     * @return void
     */
    protected function validate(): void
    {
        parent::validate();
        if ($this->from === null) {
            throw new BuildException("Missing the 'from' attribute.");
        }
    }

    /**
     * @return void
     */
    public function main(): void
    {
        $this->validate();

        $value = $this->getProject()->getProperty($this->from);

        if ($value === null && !$this->silent) {
            throw new BuildException("Property '" . $this->from . "' is not defined.");
        }

        if ($value !== null) {
            $this->setPropertyValue($value);
        }
    }
}
