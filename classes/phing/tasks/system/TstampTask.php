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
 * Sets properties to the current time, or offsets from the current time.
 * The default properties are TSTAMP, DSTAMP and TODAY;
 *
 * Based on Ant's Tstamp task.
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.tasks.system
 * @since   2.2.0
 */
class TstampTask extends Task
{
    /**
     * @var TstampCustomFormat[]
     */
    private $customFormats = [];

    private $prefix = '';

    /**
     * Set a prefix for the properties. If the prefix does not end with a "."
     * one is automatically added.
     *
     * @param string $prefix the prefix to use.
     *
     * @return void
     */
    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;

        if (!empty($this->prefix)) {
            $this->prefix .= '.';
        }
    }

    /**
     * Adds a custom format
     *
     * @param TstampCustomFormat $cf custom format
     *
     * @return void
     */
    public function addFormat(TstampCustomFormat $cf): void
    {
        $this->customFormats[] = $cf;
    }

    /**
     * Create the timestamps. Custom ones are done before
     * the standard ones.
     *
     * @return void
     *
     * @throws Exception
     * @throws BuildException
     */
    public function main(): void
    {
        $d = $this->getNow();

        foreach ($this->customFormats as $cf) {
            $cf->execute($this, $d, $this->getLocation());
        }

        $dstamp = strftime('%Y%m%d', $d);
        $this->prefixProperty('DSTAMP', $dstamp);

        $tstamp = strftime('%H%M', $d);
        $this->prefixProperty('TSTAMP', $tstamp);

        $today = strftime('%B %d %Y', $d);
        $this->prefixProperty('TODAY', $today);
    }

    /**
     * helper that encapsulates prefix logic and property setting
     * policy (i.e. we use setNewProperty instead of setProperty).
     *
     * @param string $name
     * @param string $value
     *
     * @return void
     */
    public function prefixProperty(string $name, string $value): void
    {
        $this->getProject()->setNewProperty($this->prefix . $name, $value);
    }

    /**
     * @return int
     *
     * @throws Exception
     */
    protected function getNow(): int
    {
        $property = $this->getProject()->getProperty('phing.tstamp.now.iso');

        if ($property !== null && $property !== '') {
            try {
                $dateTime = new DateTime($property);
            } catch (Throwable $e) {
                $this->log('magic property phing.tstamp.now.iso ignored as ' . $property . ' is not a valid number');
                $dateTime = new DateTime();
            }

            return $dateTime->getTimestamp();
        }

        $property = $this->getProject()->getProperty('phing.tstamp.now');

        $dateTime = (new DateTime())->getTimestamp();

        if ($property !== null && $property !== '') {
            $dateTime = DateTime::createFromFormat('U', $property);
            if ($dateTime === false) {
                $this->log('magic property phing.tstamp.now ignored as ' . $property . ' is not a valid number');
            } else {
                $dateTime = $dateTime->getTimestamp();
            }
        }

        return $dateTime;
    }
}
