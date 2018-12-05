<?php

/**
 * @package  phing.tasks.system
 */
class TstampCustomFormat
{
    private $propertyName = "";
    private $pattern = "";
    private $locale = "";
    private $timezone = "";

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
     * @param pattern
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
