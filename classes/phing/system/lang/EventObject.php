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
 * @package phing.system.lang
 */
class EventObject
{
    /**
     * The object on which the Event initially occurred.
     *
     * @var object
     */
    protected $source;

    /**
     * Constructs a prototypical Event.
     *
     * @param object|null $source
     *
     * @throws Exception
     */
    public function __construct($source)
    {
        if ($source === null) {
            throw new Exception('Null source');
        }
        $this->source = $source;
    }

    /**
     * The object on which the Event initially occurred.
     *
     * @return object
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Returns a String representation of this EventObject.
     *
     * @return string
     */
    public function __toString(): string
    {
        if (method_exists($this->getSource(), 'toString')) {
            return static::class . '[source=' . $this->getSource()->toString() . ']';
        }

        if (method_exists($this->getSource(), '__toString')) {
            return static::class . '[source=' . $this->getSource() . ']';
        }

        return static::class . '[source=' . get_class($this->getSource()) . ']';
    }
}
