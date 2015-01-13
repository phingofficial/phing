<?php

namespace Phing\Io\FileSystem;

use Phing\Io\IOException;
use Phing\Phing;

class FileSystemFactory
{
    /**
     * Instance for getFileSystem() method.
     * @var AbstractFileSystem
     */
    public static $fs;

    /**
     * Static method to return the FileSystem singleton representing
     * this platform's local filesystem driver.
     *
     * @return AbstractFileSystem
     * @throws \Phing\Io\IOException
     */
    public static function getFileSystem()
    {
        if (self::$fs === null) {
            switch (Phing::getProperty('host.fstype')) {
                case 'UNIX':
                    self::$fs = new UnixFileSystem();
                    break;
                case 'WIN32':
                case 'WINNT':
                    self::$fs = new Win32FileSystem();
                    break;
                default:
                    throw new IOException("Host uses unsupported filesystem, unable to proceed");
            }
        }

        return self::$fs;
    }
}
