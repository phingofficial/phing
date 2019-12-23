<?php
/**
 * INI file modification task for Phing, the PHP build tool.
 *
 * Based on http://ant-contrib.sourceforge.net/tasks/tasks/inifile.html
 *
 * PHP version 5
 *
 * @link     http://www.phing.info/
 *
 * @category Tasks
 * @package  phing.tasks.ext
 * @author   Ken Guest <kguest@php.net>
 * @license  LGPL v3 or later http://www.gnu.org/licenses/lgpl.html
 */

declare(strict_types=1);

/**
 * Class for collecting details for removing keys or sections from an ini file
 *
 * @link     http://www.phing.info/
 *
 * @category Tasks
 * @package  phing.tasks.ext
 * @author   Ken Guest <kguest@php.net>
 * @license  LGPL v3 or later http://www.gnu.org/licenses/lgpl.html
 */
class IniFileRemove
{
    /**
     * Property
     *
     * @var string|null
     */
    protected $property = null;

    /**
     * Section
     *
     * @var string|null
     */
    protected $section = null;

    /**
     * Set Section name
     *
     * @param string $section Name of section in ini file
     *
     * @return void
     */
    public function setSection(string $section): void
    {
        $this->section = $section;
    }

    /**
     * Set Property/Key name
     *
     * @param string $property ini key name
     *
     * @return void
     */
    public function setProperty(string $property): void
    {
        $this->property = $property;
    }

    /**
     * Get Property
     *
     * @return string|null
     */
    public function getProperty(): ?string
    {
        return $this->property;
    }

    /**
     * Get Section
     *
     * @return string|null
     */
    public function getSection(): ?string
    {
        return $this->section;
    }
}
