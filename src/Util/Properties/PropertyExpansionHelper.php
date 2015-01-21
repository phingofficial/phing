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

        // Because we're not doing anything special (like multiple passes),
        // regex is the simplest / fastest.  PropertyTask, though, uses
        // the old parsePropertyString() method, since it has more stringent
        // requirements.

        $sb = $buffer;
        $iteration = 0;

        $properties = $this->props;

        // loop to recursively replace tokens
        while (strpos($sb, '${') !== false) {
            $sb = preg_replace_callback('/\$\{([^\$}]+)\}/', function ($matches) use ($properties) {

                $propertyName = $matches[1];

                if (!isset($properties[$propertyName])) {
                    return $matches[0];
                }

                $propertyValue = $properties[$propertyName];

                if (is_bool($propertyValue)) {
                    $propertyValue = $propertyValue ? "true" : "false";
                } else if (is_array($propertyValue)) {
                    $propertyValue = implode(',', $propertyValue);
                }

                return $propertyValue;

            }, $sb);

            // keep track of iterations so we can break out of otherwise infinite loops.
            $iteration++;
            if ($iteration == 5) {
                return $sb;
            }
        }

        return $sb;
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
