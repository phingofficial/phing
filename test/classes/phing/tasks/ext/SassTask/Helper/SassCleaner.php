<?php

declare(strict_types=1);

trait SassCleaner
{

    public function sassCleanUp(string $compileDirectoryPath, string $testFileName): void
    {
        $fs = FileSystem::getFileSystem();
        if (file_exists($compileDirectoryPath . $testFileName)) {
            $fs->unlink($compileDirectoryPath . $testFileName);
        }
        if (file_exists($compileDirectoryPath . $testFileName . '.map')) {
            $fs->unlink($compileDirectoryPath . $testFileName . '.map');
        }
        if (is_dir($compileDirectoryPath . ".sass-cache")) {
            $fs->rmdir($compileDirectoryPath . ".sass-cache", true);
        }
    }
}
