<?php
/**
 * Class for collecting details for setting values in ini file
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
 * InifileSet
 *
 * @link     InifileSet.php
 *
 * @category Tasks
 * @package  phing.tasks.ext
 * @author   Ken Guest <ken@linux.ie>
 * @license  LGPL (see http://www.gnu.org/licenses/lgpl.html)
 */
class IniFileSet
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
     * Value
     *
     * @var string|null
     */
    protected $value = null;

    /**
     * Operation
     *
     * @var string|null
     */
    protected $operation = null;

    /**
     * Set Operation
     *
     * @param string $operation +/-
     *
     * @return void
     */
    public function setOperation(string $operation): void
    {
        $this->operation = $operation;
    }

    /**
     * Get Operation
     *
     * @return string|null
     */
    public function getOperation(): ?string
    {
        return $this->operation;
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
     * Set Value
     *
     * @param string $value Value to set for key in ini file
     *
     * @return void
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
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
     * Get Value
     *
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
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
