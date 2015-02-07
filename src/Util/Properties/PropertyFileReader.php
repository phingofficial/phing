<?php
namespace Phing\Util\Properties;

use Phing\Io\IOException;
use Phing\Io\File;

/**
 * Reads property files into a PropertySet.
 * @author Matthias Pigulla <mp@webfactory.de>
 */
class PropertyFileReader
{
    /** @var PropertySet */
    protected $properties;

    public function __construct(PropertySet $s = null)
    {
        if (null === $s) {
            $s = new PropertySetImpl();
        }

        $this->properties = $s;
    }

    /**
     * Returns the PropertySet used.
     *
     * @return PropertySet
     */
    public function getPropertySet()
    {
        return $this->properties;
    }

    /**
     * Read properties from a file, possibly from a particular section.
     *
     * @param File   $file    The property file to load
     * @param string $section The property file section to parse. May be omitted or <code>null</code>, in which case only the global section will be read.
     *
     * @throws IOException When the file cannot be read
     */
    public function load(File $file, $section = null)
    {
        if (!$file->canRead()) {
            throw new IOException("Can not read file " . $file->getPath());
        }

        $section = (string) $section;

        // load() already made sure that file is readable
        // but we'll double check that when reading the file into
        // an array

        $lines = file($file->getPath());

        // concatenate lines ending with backslash
        $linesCount = count($lines);
        for ($i = 0; $i < $linesCount; $i++) {
            if (substr($lines[$i], -2, 1) === '\\') {
                $lines[$i + 1] = substr($lines[$i], 0, -2) . ltrim($lines[$i + 1]);
                $lines[$i] = '';
            }
        }

        $currentSection = '';
        $sect = array($currentSection => array(), $section => array());
        $depends = array();

        foreach ($lines as $l) {

            $l = trim(preg_replace("/(?:^|\s+)[;#].*$/", "", $l));

            if (!$l) {
                continue;
            }

            if (preg_match('/^\[(\w+)(?:\s*:\s*(\w+))?\]$/', $l, $matches)) {
                $currentSection = $matches[1];
                $sect[$currentSection] = array();
                if (isset($matches[2])) {
                    $depends[$currentSection] = $matches[2];
                }
                continue;
            }

            $pos = strpos($l, '=');
            $name = trim(substr($l, 0, $pos));
            $value = $this->inVal(trim(substr($l, $pos + 1)));

            /*
             * Take care: Property file may contain identical keys like
             * a[] = first
             * a[] = second
             */
            $sect[$currentSection][] = array($name, $value);
        }

        $dependencyOrder = array();
        while ($section) {
            array_unshift($dependencyOrder, $section);
            $section = isset($depends[$section]) ? $depends[$section] : '';
        }
        array_unshift($dependencyOrder, '');

        foreach ($dependencyOrder as $section) {
            foreach ($sect[$section] as $def) {
                list ($name, $value) = $def;
                $this->properties[$name] = $value;
            }
        }
    }

    /**
     * Process values when being read in from properties file.
     * does things like convert "true" => true
     * @param  string $val Trimmed value.
     * @return mixed  The new property value (may be boolean, etc.)
     */
    protected function inVal($val)
    {
        if ($val === "true") {
            $val = true;
        } elseif ($val === "false") {
            $val = false;
        }

        return $val;
    }
}
