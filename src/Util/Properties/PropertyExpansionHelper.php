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
 *
 * As this class already "contains" a property set, it also acts as a decorator
 * for it and takes care of expanding propery references in the property values
 * themselves.
 */
class PropertyExpansionHelper implements PropertySet
{
    protected $props;
    protected $refStack;

    public function __construct(PropertySet $props)
    {
        $this->props = $props;
    }

    public function offsetGet($key)
    {
        return $this->expand($this->props->offsetGet($key));
    }

    public function offsetSet($key, $value)
    {
        $this->props->offsetSet($key, $value);
    }

    public function offsetExists($key)
    {
        return $this->props->offsetExists($key);
    }

    public function offsetUnset($key)
    {
        $this->props->offsetUnset($key);
    }

    public function getIterator()
    {
        return new PropertyExpansionIterator($this, $this->props->getIterator());
    }

    public function isEmpty()
    {
        return $this->props->isEmpty();
    }

    public function keys()
    {
        return $this->props->keys();
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
     * @exception BuildException if the given value has an unclosed
     *                           property name, e.g. <code>${xxx</code>
     */
    public function expand($buffer)
    {
        if ($buffer === null) {
            return null;
        }

        if (is_array($buffer)) {
            return $this->expandArray($buffer);
        }

        return $this->match($buffer, array());
    }

    public function match($buffer, $refStack)
    {
        if (strpos($buffer, '${') !== false) {
            $properties = $this->props;
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
        }

        return $buffer;
    }

    protected function expandArray(array $a)
    {
        $r = array();
        foreach ($a as $key => $value) {
            $r[$key] = $this->expand($value);
        }
        return $r;
    }
}
