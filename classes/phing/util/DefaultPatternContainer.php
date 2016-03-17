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

include_once 'phing/system/io/PhingFile.php';
include_once 'phing/util/StringHelper.php';

/**
 * Describes a set of default patterns.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.util
 */
abstract class DefaultPatternContainer extends ArrayObject
{
    /**
     * @var string[]
     */
    protected $defaultPatternList = array();

    /**
     * Standard constructor.
     */
    public function __construct()
    {
        $this->defaultPatternList = $this->normalizePatternList($this->defaultPatternList);

        parent::__construct($this->defaultPatternList);
    }

    /**
     * Append a pattern to the container.
     * @param mixed $value
     */
    public function append($value)
    {
        parent::append($this->normalizePattern($value));
    }

    /**
     * Removes a pattern from the container.
     * @param $value
     */
    public function remove($value)
    {
        $key = array_search($value, $this->getArrayCopy());
        if (parent::offsetExists($key)) {
            parent::offsetUnset($key);
        }
    }

    /**
     * Reset the container to the initial state.
     */
    public function reset()
    {
        $this->exchangeArray($this->defaultPatternList);
    }

    /**
     * Clears all patterns in the container.
     */
    public function clear()
    {
        $this->exchangeArray(array());
    }

    /**
     * Normalize a list of patterns.
     * @param array $list
     * @return array
     */
    public function normalizePatternList(array $list)
    {
        $normalizedPatterns = array();
        foreach ($list as $entry) {
            $normalizedPatterns[] = $this->normalizePattern($entry);
        }

        return $normalizedPatterns;
    }

    /**
     * Normalizes a pattern.
     * @param $value
     * @return mixed|string
     */
    protected function normalizePattern($value)
    {
        $pattern = str_replace(array('/', '\\'), PhingFile::$separator, $value);
        if (StringHelper::endsWith(DIRECTORY_SEPARATOR, $pattern)) {
            $pattern .= "**";
        }
        return $pattern;
    }
}
