<?php
/**
 * Require base Properties class to extend it 
 */
include_once 'phing/system/util/Properties.php';
/**
 * PropertiesFile class supports a recursive properties load
 * 
 * @package phing.tasks.ext.properties
 * @author Shaked (phing@shakedos.com)
 * @version $Id$
 */
class PropertiesFile extends Properties {  
	/**
	 * @var const - decides how to retreive the extended environment string 
	 */
	const EXTENDS_REGEX = '\{\sextends(.*)\}'; 
	/**
	 * (non-PHPdoc)
	 * @see Properties::parse()
	 */
    protected function parse($filePath) {

        // load() already made sure that file is readable                
        // but we'll double check that when reading the file into 
        // an array
        
        if (($lines = @file($filePath)) === false) {
            throw new IOException("Unable to parse contents of $filePath");
        }
         
        $sec_name = ""; 
        
        // check extends 
        $extendedFilename = $this->getExtendedFilename($lines[0]);   
        if ($extendedFilename){
        	//get path create file and load again 
        	$newFileName = substr($filePath,0,strrpos($filePath,'/')) . '/' .$extendedFilename . '.properties';
        	$file = new PhingFile($newFileName); 
        	//load before so we can override later 
        	$this->load($file);
        }   
        
        foreach($lines as $line) {
            // strip comments and leading/trailing spaces
            $line = trim(preg_replace("/\s+[;#]\s.+$/", "", $line));
            
            if (empty($line) || $line[0] == ';' || $line[0] == '#' || $line[0] == '{') { // { should be skipped
                continue;
            }
                
            $pos = strpos($line, '=');
            $property = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1));    
            $propertyValue = $this->inVal($value);        
            //set private properties variable   
            $this->setProperty($property,$propertyValue);
        } // for each line 
    }
    /** 
     * Get the the name of the extended file 
     * @param string $line - the first line
     * @return string|false 
     */
    protected function getExtendedFilename($line){
    	if (preg_match('#'.self::EXTENDS_REGEX.'#', $line,$matches)){
    		return trim($matches[1]); 
    	}
    	return false; 
    } 
}