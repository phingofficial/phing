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
 * @author    Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package   phing.listener.statistics
 */
class StringFormatter
{
    /**
     * @param string $value
     * @param int    $fixedLength
     *
     * @return string
     */
    public function center(string $value, int $fixedLength): string
    {
        $spacesBeforeValue = $this->calculateSpaceBeforeValue($value, $fixedLength);
        return $this->toSpaces((int) $spacesBeforeValue) . $value;
    }

    /**
     * @param string $value
     * @param int    $fixedLength
     *
     * @return string
     */
    public function left(string $value, int $fixedLength): string
    {
        return $value . $this->toSpaces($fixedLength - strlen($value) + 4);
    }

    /**
     * @param string $value
     * @param int    $fixedLength
     *
     * @return float
     */
    private function calculateSpaceBeforeValue(string $value, int $fixedLength): float
    {
        return $fixedLength / 2 - strlen($value) / 2;
    }

    /**
     * @param int $size
     *
     * @return string
     */
    public function toSpaces(int $size): string
    {
        return $this->toChars(' ', $size);
    }

    /**
     * @param string $ch
     * @param int    $size
     *
     * @return string
     */
    public function toChars(string $ch, int $size): string
    {
        return str_repeat($ch, $size);
    }
}
