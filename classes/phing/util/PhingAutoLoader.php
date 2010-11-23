<?php

/*
 * $Id$
 *
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
 * Autoloader for Phing classes/types/tasks
 * 
 * @author    Michiel Rook <mrook@php.net>
 * @version   $Revision$
 * @package   phing.util
 */
class PhingAutoLoader
{
    public static $locations = array("." => true);
    
    /**
     * Add a dot-notation or PEAR-notation class (and optionally
     * classpath) to the list of locations
     * 
     * @param string $classname
     * @param string $classpath
     * @return string
     */
    public static function addLocation($classname, $classpath = null)
    {
        /// check if this is a PEAR-style path (@see http://pear.php.net/manual/en/standards.naming.php)
        if (strpos($classname, '.') === false && strpos($classname, '_') !== false) {
            $cls = $classname;
            $classname = str_replace('_', '.', $classname);
        } else {
            $cls = StringHelper::unqualify($classname);
        }
        
        // 1- temporarily replace escaped '.' with another illegal char (#)
        $tmp = str_replace('\.', '##', $classname);
        // 2- swap out the remaining '.' with DIR_SEP
        $tmp = strtr($tmp, '.', DIRECTORY_SEPARATOR);
        // 3- swap back the escaped '.'
        $tmp = str_replace('##', '.', $tmp);

        $path = dirname($tmp);
        
        if (!empty($classpath)) {
            $path = $classpath . DIRECTORY_SEPARATOR . $path;
        }
        
        self::$locations[$path] = true;
        
        return $cls;
    }
    
    /**
     * Autoloader
     * 
     * @param string $class
     * @return boolean
     */
    public static function autoload($class)
    {
        /// check if this is a PEAR-style path (@see http://pear.php.net/manual/en/standards.naming.php)
        if (strpos($class, '.') === false && strpos($class, '_') !== false) {
            $classFile = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
        } else {
            $classFile = $class . '.php';
        }
        
        foreach (self::$locations as $location => $dummy) {
            @include_once $location . DIRECTORY_SEPARATOR . $classFile;

            if (class_exists($class)) {
                return true;
            }
        }
        
        return false;
    }
}

spl_autoload_register(array('PhingAutoLoader', 'autoload'));
