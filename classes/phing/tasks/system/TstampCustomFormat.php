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
 * @package  phing.tasks.system
 */
class TstampCustomFormat
{
    private $propertyName = "";
    private $pattern      = "";
    private $locale       = "";
    private $timezone     = "";

    /**
     * The property to receive the date/time string in the given pattern
     *
     * @param string $propertyName the name of the property.
     */
    public function setProperty($propertyName)
    {
        $this->propertyName = $propertyName;
    }

    /**
     * The date/time pattern to be used. The values are as
     * defined by the PHP strftime() function.
     *
     * @param string $pattern
     */
    public function setPattern($pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * The locale used to create date/time string.
     *
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @param string $timezone
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * validate parameter and execute the format.
     *
     * @param TstampTask $tstamp reference to task
     *
     * @throws BuildException
     */
    public function execute(TstampTask $tstamp, $d, $location)
    {
        if (empty($this->propertyName)) {
            throw new BuildException("property attribute must be provided", $location);
        }

        if (empty($this->pattern)) {
            throw new BuildException("pattern attribute must be provided", $location);
        }

        $oldlocale = "";
        if (!empty($this->locale)) {
            $oldlocale = setlocale(LC_ALL, 0);
            setlocale(LC_ALL, $this->locale);
        }

        $savedTimezone = date_default_timezone_get();
        if (!empty($this->timezone)) {
            date_default_timezone_set($this->timezone);
        }

        $value = strftime($this->pattern, $d);
        $tstamp->prefixProperty($this->propertyName, $value);

        if (!empty($this->locale)) {
            // reset locale
            setlocale(LC_ALL, $oldlocale);
        }

        if (!empty($this->timezone)) {
            date_default_timezone_set($savedTimezone);
        }
    }
}
