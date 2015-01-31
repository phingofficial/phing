<?php
namespace Phing\Util\Properties;

/*
 *  $Id$
 *
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
use Phing\Exception\BuildException;

/**
 * A class that can expand ${}-style references in arbitrary strings with the
 * corresponding values from a PropertySet.
 */
class PropertyExpansionHelper
{
    protected $properties;

    /**
     * Constructor.
     *
     * @param PropertySet $propertySet The PropertySet that will be used for all expansions.
     */
    public function __construct(PropertySet $propertySet)
    {
        $this->properties = $propertySet;
    }

    /**
     * Replaces ${} style constructions in the given value with the
     * string value of the corresponding data types.
     *
     * @param value The string to be scanned for property references.
     *              May be <code>null</code>.
     *
     * @return the given string with embedded property names replaced
     *         by values, or <code>null</code> if the given string is
     *         <code>null</code>.
     *
     * @exception BuildException Then circularly defined properties are found.
     */
    public function expand($buffer)
    {
        if ($buffer === null) {
            return null;
        }

        if ($buffer instanceof \Traversable) {
            return $this->expandTraversable($buffer);
        }

        if (is_array($buffer)) {
            return $this->expandArray($buffer);
        }

        return $this->match($buffer, array());
    }

    /**
     * Traverses each element and expands ${} style property references.
     *
     * @param \Traversable $traversable The \Traversable to process
     *
     * @return array An array that maintains keys from $traversable and has ${}-style property references in all values expanded.
     *
     * @exception BuildException Then circularly defined properties are found.
     */
    public function expandTraversable(\Traversable $traversable)
    {
        return $this->expandArray(iterator_to_array($traversable));
    }

    /**
     * Expands ${} style property references in all array entries
     *
     * @param array $array The array to process
     *
     * @return array An array with identical keys that has ${}-style property references in all values expanded.
     *
     * @exception BuildException Then circularly defined properties are found.
     */
    public function expandArray(array $array)
    {
        return array_map(array($this, 'expand'), $array);
    }

    /**
     * This method is public only to be callable from a closure inside itself.
     * Do not use it directly.
     */
    public function match($buffer, $refStack)
    {
        do {
            $old = $buffer;

            $properties = $this->properties;
            $self = $this;
            $buffer = preg_replace_callback('/\$\{([^\$}]+)\}/', function ($matches) use ($properties, $refStack, $self) {

                $propertyName = $matches[1];

                if (in_array($propertyName, $refStack)) {
                    throw new BuildException("Property $propertyName was circularly defined: " . implode(" => ", $refStack));
                }

                if (!isset($properties[$propertyName])) {
                    return $matches[0];
                }

                $propertyValue = $properties[$propertyName];

                if (is_bool($propertyValue)) {
                    $propertyValue = $propertyValue ? "true" : "false";
                } else if (is_array($propertyValue)) {
                    $propertyValue = implode(',', $propertyValue);
                } else {
                    $refStack[] = $propertyName;
                    $propertyValue = $self->match($propertyValue, $refStack);
                }

                return $propertyValue;

            }, $buffer);

        } while ($old != $buffer);

        return $buffer;
    }
}
