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
 * Encapsulates an input request.
 *
 * @author  Hans Lellelid <hans@xmpl.org> (Phing)
 * @author  Stefan Bodewig <stefan.bodewig@epost.de> (Ant)
 * @package phing.input
 */
class InputRequest
{
    /**
     * @var string
     */
    protected $prompt;

    /**
     * @var string|null
     */
    protected $input;

    /**
     * @var string
     */
    protected $defaultValue;

    /**
     * @var string
     */
    protected $promptChar;

    /**
     * @var bool
     */
    protected $hidden = false;

    /**
     * @param string $prompt The prompt to show to the user.  Must not be null.
     *
     * @throws BuildException
     */
    public function __construct(string $prompt)
    {
        if ($prompt === null) {
            throw new BuildException('prompt must not be null');
        }
        $this->prompt = $prompt;
    }

    /**
     * Retrieves the prompt text.
     *
     * @return string
     */
    public function getPrompt(): string
    {
        return $this->prompt;
    }

    /**
     * Sets the user provided input.
     *
     * @param string|null $input
     *
     * @return void
     */
    public function setInput(?string $input): void
    {
        $this->input = $input;
    }

    /**
     * Is the user input valid?
     *
     * @return bool
     */
    public function isInputValid(): bool
    {
        return true;
    }

    /**
     * Retrieves the user input.
     *
     * @return string|null
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Set the default value to use.
     *
     * @param string $v
     *
     * @return void
     */
    public function setDefaultValue(string $v): void
    {
        $this->defaultValue = $v;
    }

    /**
     * Return the default value to use.
     *
     * @return string|null
     */
    public function getDefaultValue(): ?string
    {
        return $this->defaultValue;
    }

    /**
     * Set the default value to use.
     *
     * @param string $c
     *
     * @return void
     */
    public function setPromptChar(string $c): void
    {
        $this->promptChar = $c;
    }

    /**
     * Return the default value to use.
     *
     * @return string|null
     */
    public function getPromptChar(): ?string
    {
        return $this->promptChar;
    }

    /**
     * @return bool
     */
    public function isHidden(): bool
    {
        return $this->hidden;
    }

    /**
     * @param bool $hidden
     *
     * @return void
     */
    public function setHidden(bool $hidden): void
    {
        $this->hidden = $hidden;
    }
}
