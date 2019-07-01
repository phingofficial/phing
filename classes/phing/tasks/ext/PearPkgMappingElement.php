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
 * Sub-element of <mapping>.
 *
 * @package phing.tasks.ext
 */
class PearPkgMappingElement
{
    private $key;
    private $value;
    private $elements = [];

    /**
     * @param $v
     */
    public function setKey($v)
    {
        $this->key = $v;
    }

    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param $v
     */
    public function setValue($v)
    {
        $this->value = $v;
    }

    /**
     * Returns either the simple value or
     * the calculated value (array) of nested elements.
     *
     * @return mixed
     */
    public function getValue()
    {
        if (!empty($this->elements)) {
            $value = [];
            foreach ($this->elements as $el) {
                if ($el->getKey() !== null) {
                    $value[$el->getKey()] = $el->getValue();
                } else {
                    $value[] = $el->getValue();
                }
            }

            return $value;
        } else {
            return $this->value;
        }
    }

    /**
     * Handles nested <element> tags.
     */
    public function createElement()
    {
        $e = new PearPkgMappingElement();
        $this->elements[] = $e;

        return $e;
    }
}
