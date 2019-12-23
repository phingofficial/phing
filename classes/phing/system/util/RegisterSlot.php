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
 * Represents a slot in the register.
 *
 * @package phing.system.util
 */
class RegisterSlot
{
    /**
     * The name of this slot.
     *
     * @var string
     */
    private $key;

    /**
     * The value for this slot.
     *
     * @var mixed
     */
    private $value;

    /**
     * Constructs a new RegisterSlot, setting the key to passed param.
     *
     * @param string $key
     */
    public function __construct(string $key)
    {
        $this->setKey($key);
    }

    /**
     * Sets the key / name for this slot.
     *
     * @param string $k
     *
     * @return void
     */
    public function setKey(string $k): void
    {
        $this->key = (string) $k;
    }

    /**
     * Gets the key / name for this slot.
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Sets the value for this slot.
     *
     * @param mixed $v
     *
     * @return void
     */
    public function setValue($v): void
    {
        $this->value = $v;
    }

    /**
     * Returns the value at this slot.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Recursively implodes an array to a comma-separated string
     *
     * @param array $arr
     *
     * @return string
     */
    private function implodeArray(array $arr): string
    {
        $values = [];

        foreach ($arr as $value) {
            if (is_array($value)) {
                $values[] = $this->implodeArray($value);
            } else {
                $values[] = $value;
            }
        }

        return '{' . implode(',', $values) . '}';
    }

    /**
     * Returns the value at this slot as a string value.
     *
     * @return string
     */
    public function __toString(): string
    {
        if (is_array($this->value)) {
            return $this->implodeArray($this->value);
        }

        return (string) $this->value;
    }
}
