<?php
class GitTestsHelper
{
    public static function rmdir($dir) {
        if (!file_exists($dir)) return true;
        if (!is_dir($dir)) return unlink($dir);
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') continue;
            if (!self::rmdir($dir.DIRECTORY_SEPARATOR.$item)) return false;
        }
        return rmdir($dir);
    }
}
