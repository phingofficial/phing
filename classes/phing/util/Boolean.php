<?php
class Boolean {
	
	protected static $TRUE_VALUES = array("on", "true", "t", "yes", "y", "1");
    protected static $FALSE_VALUES = array("off", "false", "f", "no", "n", "0");
	
	public static function cast($value) {
		if (!self::isBoolean($value))
			throw new BuildException("Not a boolean value: '{$value}'");
		return self::booleanValue($value);
	}

	/** tests if a string is a representative of a boolean */
    public static function isBoolean($s) {
        if (is_bool($s)) 
            return true; // it already is boolean
        
        if ($s === "" || $s === null || !is_string($s)) 
            return false; // not a valid string for testing

        $test = trim(strtolower($s));
        return (boolean) in_array($test, array_merge(self::$FALSE_VALUES, self::$TRUE_VALUES));
    }
    
    /**
     * @return boolean
     */ 
    public static function booleanValue($s) {
        if (is_bool($s)) 
            return $s; // it's already boolean (not a string)
        
        // otherwise assume it's something like "true" or "t"
        $trimmed = strtolower(trim($s));
        return (boolean) in_array($trimmed, self::$TRUE_VALUES);
    }
}