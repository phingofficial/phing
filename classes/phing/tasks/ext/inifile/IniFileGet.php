<?php
/**
 * Class for getting values from .ini files
 *
 * PHP Version 5
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
 * InifileGet
 *
 * @link     InifileGet.php
 *
 * @category Tasks
 * @package  phing.tasks.ext
 * @author   Ken Guest <ken@linux.ie>
 * @license  LGPL (see http://www.gnu.org/licenses/lgpl.html)
 */
class IniFileGet
{
    /**
     * Default
     *
     * @var string
     */
    protected $default = '';

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
     * Output property name
     *
     * @var string|null
     */
    protected $output = null;

    /**
     * Set the default value, for if key or section is not present in .ini file
     *
     * @param string $default Default value
     *
     * @return void
     */
    public function setDefault(string $default): void
    {
        $this->default = trim($default);
    }

    /**
     * Get the default value, for if key or section is not present in .ini file
     *
     * @return string
     */
    public function getDefault(): string
    {
        return $this->default;
    }

    /**
     * Set Section name
     *
     * @param string $section Name of section in ini file
     *
     * @return void
     */
    public function setSection(string $section): void
    {
        $this->section = trim($section);
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

    /**
     * Set Property
     *
     * @param string $property property/key name
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
     * Set name of property to set retrieved value to
     *
     * @param string $output Name of property to set with retrieved value
     *
     * @return void
     */
    public function setOutputProperty(string $output): void
    {
        $this->output = $output;
    }

    /**
     * Get name of property to set retrieved value to
     *
     * @return string|null
     */
    public function getOutputProperty(): ?string
    {
        return $this->output;
    }
}
