<?php

/**
 * Helper class, holds the nested &lt;map&gt; values. Elements will look like
 * this: &lt;map from=&quot;d:&quot; to=&quot;/foo&quot;/&gt;
 *
 * When running on windows, the prefix comparison will be case
 * insensitive.
 */
class MapEntry
{
    /**
     * @var PathConvert $outer
     */
    private $outer;

    public function __construct(PathConvert $outer)
    {
        $this->outer = $outer;
    }

    /**
     * the prefix string to search for; required.
     * Note that this value is case-insensitive when the build is
     * running on a Windows platform and case-sensitive when running on
     * a Unix platform.
     */
    public function setFrom($from)
    {
        $this->outer->from = $from;
    }

    public function setTo($to)
    {
        $this->outer->to = $to;
    }

    /**
     * Apply this map entry to a given path element
     *
     * @param  string $elem Path element to process
     * @return string Updated path element after mapping
     *
     * @throws BuildException
     */
    public function apply($elem)
    {
        if ($this->outer->from === null || $this->outer->to === null) {
            throw new BuildException(
                "Both 'from' and 'to' must be set "
                . "in a map entry"
            );
        }

        // If we're on windows, then do the comparison ignoring case
        $cmpElem = $this->outer->onWindows ? strtolower($elem) : $elem;
        $cmpFrom = $this->outer->onWindows ? strtolower(
            str_replace('/', '\\', $this->outer->from)
        ) : $this->outer->from;

        // If the element starts with the configured prefix, then
        // convert the prefix to the configured 'to' value.

        if (StringHelper::startsWith($cmpFrom, $cmpElem)) {
            $len = strlen($this->outer->from);

            if ($len >= strlen($elem)) {
                $elem = $this->outer->to;
            } else {
                $elem = $this->outer->to . StringHelper::substring($elem, $len);
            }
        }

        return $elem;
    }
}
