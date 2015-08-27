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

/**
 * Helper class which can be used for Phing task attribute setter methods to
 * allow the build file to specify an integer in either decimal, octal, or
 * hexadecimal format.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.types
 */
class FlexInteger
{
    /**
     * @var int
     */
    private $value;

    /**
     * @param string $value The value to decode.
     */
    public function __construct($value)
    {
        $this->value = intval($value, 0);
    }

    /**
     * Returns the decimal integer value.
     * @return int The integer value.
     */
    public function intValue()
    {
        return $this->value;
    }

    /**
     * Return the decimal value for display.
     * @return string A string version of the integer.
     */
    public function __toString()
    {
        return (string) $this->value;
    }
}
